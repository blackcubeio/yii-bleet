import {DI, ILogger, IPlatform, resolve} from 'aurelia';
import type {Socket} from 'socket.io-client';
import {IBleetConfiguration} from '../configure';
import {Transport} from '../enums/api';

let io: typeof import('socket.io-client').io | null = null;

function getSocketIo(): typeof import('socket.io-client').io {
    if (!io) {
        try {
            io = require('socket.io-client').io;
        } catch {
            throw new Error(
                'socket.io-client is not installed. ' +
                'Install it with: npm install socket.io-client'
            );
        }
    }
    return io;
}

interface IListener {
    event: string;
    callback: (...args: unknown[]) => void;
}

export interface ISocketioManagerService extends SocketioManagerService {}
export const ISocketioManagerService = DI.createInterface<ISocketioManagerService>(
    'ISocketioManagerService',
    (x) => x.singleton(SocketioManagerService)
);

/**
 * Couche LIVE (push) du socle, reprise du SocketioManagerService de Blips (lui-même repris d'infinity-board). Gère un
 * socket par namespace, la reconnexion (backoff exponentiel SANS abandon : un namespace voulu se reconnecte tant que
 * `disconnect()` — logout — n'est pas demandé ; relance immédiate au réveil d'onglet/réseau), et le REGISTRE des
 * abonnements push (ré-attachés sur chaque nouveau socket : une reconnexion recrée le socket, le serveur re-push son
 * snapshot → l'état se resynchronise seul). WS uniquement : pas de fallback REST pour le live.
 *
 * Le jeton n'est pas l'affaire du gestionnaire : les options socket.io données à `connect()` sont gardées par
 * namespace et rejouées à chaque reconnexion. L'application y met `auth` sous forme de FONCTION — le contrat de
 * socket.io-client, `auth: (cb) => cb({ token })` — appelée à chaque handshake : le jeton est frais sans que le socle
 * connaisse la session.
 */
export class SocketioManagerService {
    private readonly sockets = new Map<string, Socket>();
    private readonly options = new Map<string, Record<string, any>>(); // options socket.io par namespace, rejouées à chaque reconnexion
    private readonly wanted = new Set<string>(); // namespaces à maintenir connectés (retirés au disconnect/logout)
    private readonly listeners = new Map<string, IListener[]>(); // abonnements push par namespace (source de vérité)
    private readonly reconnectAttempts = new Map<string, number>();
    private readonly reconnectTimers = new Map<string, number>();
    private readonly connectionPromises = new Map<string, Promise<boolean>>();
    private readonly reconnectDelay = 1000;
    private readonly reconnectDelayMax = 30000;
    private readonly requestTimeout = 5000;

    public constructor(
        private readonly logger: ILogger = resolve(ILogger).scopeTo('SocketioManagerService'),
        private readonly config: IBleetConfiguration = resolve(IBleetConfiguration),
        private readonly p: IPlatform = resolve(IPlatform),
    ) {
        this.logger.trace('constructor');
        // Réveil : onglet redevenu visible ou réseau revenu → reconnexion IMMÉDIATE des namespaces voulus
        // (les timers de backoff ont pu être gelés/perdus pendant que l'onglet était en arrière-plan).
        this.p.document.addEventListener('visibilitychange', () => {
            if (this.p.document.visibilityState === 'visible') {
                this.wakeUp();
            }
        });
        this.p.window.addEventListener('online', () => this.wakeUp());
    }

    public connect(namespace = '/', options?: Record<string, any>): Promise<boolean> {
        if (options !== undefined) {
            this.options.set(namespace, options);
        }
        if (this.connectionPromises.has(namespace)) {
            return this.connectionPromises.get(namespace) as Promise<boolean>;
        }
        if (this.sockets.get(namespace)?.connected === true) {
            return Promise.resolve(true);
        }
        this.clearReconnectTimer(namespace);
        this.wanted.add(namespace);
        if (this.sockets.has(namespace)) {
            this.cleanupSocket(namespace);
        }

        const promise = new Promise<boolean>((resolve) => {
            const url = this.config.getBaseUrl(Transport.Socketio) + namespace;
            this.logger.trace('connect', url);
            const socket = getSocketIo()(url, {
                transports: ['websocket'],
                timeout: 20000,
                ...(this.options.get(namespace) ?? {}),
                reconnection: false, // reconnexion gérée manuellement (backoff)
            });
            this.setupSocketListeners(socket, namespace);
            // Ré-attache les abonnements push AVANT l'event `connect` (le serveur push son snapshot à la
            // connexion) — c'est ce qui rend la reconnexion transparente pour les pages abonnées.
            for (const sub of this.listeners.get(namespace) ?? []) {
                socket.on(sub.event, sub.callback);
            }
            this.sockets.set(namespace, socket);
            socket.on('connect', () => resolve(true));
            socket.on('connect_error', (error) => {
                this.logger.warn('connect_error', namespace, error.message);
                resolve(false);
            });
            // Socket.io mutualise la connexion par URL : si ce namespace est déjà ouvert ailleurs, io() renvoie un
            // socket DÉJÀ connecté → l'event 'connect' ne se redéclenche pas. On résout alors tout de suite
            // (sinon connect() resterait pendant → le snapshot ne serait jamais réclamé).
            if (socket.connected === true) {
                resolve(true);
            }
        }).finally(() => this.connectionPromises.delete(namespace));

        this.connectionPromises.set(namespace, promise);
        return promise;
    }

    public disconnect(namespace: string | null = null): void {
        if (namespace === null) {
            this.wanted.clear();
            this.sockets.forEach((_, ns) => this.cleanupSocket(ns));
            this.sockets.clear();
            this.reconnectTimers.forEach((timer) => this.p.window.clearTimeout(timer));
            this.reconnectTimers.clear();
            this.reconnectAttempts.clear();
            this.connectionPromises.clear();
            return;
        }
        this.wanted.delete(namespace);
        this.cleanupSocket(namespace);
        this.sockets.delete(namespace);
        this.reconnectAttempts.delete(namespace);
        this.connectionPromises.delete(namespace);
        this.clearReconnectTimer(namespace);
    }

    public isConnected(namespace: string): boolean {
        return this.sockets.get(namespace)?.connected === true;
    }

    // Une connexion est EN VOL (handshake en cours) → sert au transport à attendre le WS plutôt que partir en REST.
    public isConnecting(namespace: string): boolean {
        return this.connectionPromises.has(namespace);
    }

    public on<T>(namespace: string, event: string, callback: (data: T) => void): Promise<boolean> {
        // REGISTRE d'abord (source de vérité, ré-attaché par connect() sur chaque nouveau socket — y compris à la
        // reconnexion), puis attache directe si un socket existe déjà (connect() n'attache que ceux qu'il crée).
        const cb = callback as (...args: unknown[]) => void;
        const list = this.listeners.get(namespace) ?? [];
        list.push({ event, callback: cb });
        this.listeners.set(namespace, list);
        const socket = this.sockets.get(namespace);
        const promise = this.ensureConnection(namespace);
        socket?.on(event, cb);
        return promise;
    }

    public off(namespace: string, event: string, callback: (...args: unknown[]) => void): void {
        const list = this.listeners.get(namespace) ?? [];
        this.listeners.set(
            namespace,
            list.filter((sub) => sub.event !== event || sub.callback !== callback),
        );
        this.sockets.get(namespace)?.off(event, callback);
    }

    // Fire-and-forget : connecte si besoin et émet sans attendre d'ack (le retour, s'il y en a, arrive par un push).
    public emit(namespace: string, event: string, payload: unknown = {}): Promise<void> {
        return this.ensureConnection(namespace).then(() => {
            this.sockets.get(namespace)?.emit(event, payload);
        });
    }

    // Requête/réponse via ack socket.io : connecte si besoin, émet `event` et résout avec la réponse du serveur.
    public request<T>(namespace: string, event: string, payload: unknown = {}): Promise<T> {
        return this.ensureConnection(namespace).then((ok) => new Promise<T>((resolve, reject) => {
            const socket = this.sockets.get(namespace);
            if (ok === false || socket === undefined) {
                reject(new Error(`WS indisponible (${namespace})`));
                return;
            }
            const timer = this.p.window.setTimeout(() => reject(new Error(`WS request timeout (${event})`)), this.requestTimeout);
            socket.emit(event, payload, (response: T) => {
                this.p.window.clearTimeout(timer);
                resolve(response);
            });
        }));
    }

    private ensureConnection(namespace: string): Promise<boolean> {
        if (this.sockets.get(namespace)?.connected === true) {
            return Promise.resolve(true);
        }
        if (this.connectionPromises.has(namespace)) {
            return this.connectionPromises.get(namespace) as Promise<boolean>;
        }
        return this.connect(namespace);
    }

    private setupSocketListeners(socket: Socket, namespace: string): void {
        socket.on('connect', () => {
            this.logger.trace('connect:success', namespace);
            this.reconnectAttempts.set(namespace, 0);
        });
        socket.on('connect_error', () => this.scheduleReconnect(namespace));
        socket.on('disconnect', (reason: string) => {
            this.logger.warn('disconnect', namespace, reason);
            // Toute coupure SUBIE se reconnecte ('io client disconnect' = notre propre cleanup/logout).
            if (reason !== 'io client disconnect') {
                this.scheduleReconnect(namespace);
            }
        });
    }

    // Réveil (onglet visible / réseau revenu) : reconnexion immédiate des namespaces voulus, backoff remis à zéro.
    private wakeUp(): void {
        for (const namespace of this.wanted) {
            if (this.sockets.get(namespace)?.connected !== true) {
                this.clearReconnectTimer(namespace);
                this.reconnectAttempts.set(namespace, 0);
                void this.connect(namespace);
            }
        }
    }

    private cleanupSocket(namespace: string): void {
        const socket = this.sockets.get(namespace);
        if (socket !== undefined) {
            socket.removeAllListeners();
            socket.disconnect(); // aussi sur un socket EN COURS de connexion (sinon il aboutit en zombie)
        }
    }

    private clearReconnectTimer(namespace: string): void {
        const timer = this.reconnectTimers.get(namespace);
        if (timer !== undefined) {
            this.p.window.clearTimeout(timer);
            this.reconnectTimers.delete(namespace);
        }
    }

    // SANS abandon : un namespace voulu retente indéfiniment (délai croissant plafonné à 30 s) jusqu'au
    // disconnect explicite (logout). Le compteur ne sert qu'au délai ; remis à zéro au connect/réveil.
    private scheduleReconnect(namespace: string): void {
        if (this.wanted.has(namespace) === false) {
            return;
        }
        this.clearReconnectTimer(namespace);
        const attempts = this.reconnectAttempts.get(namespace) ?? 0;
        const delay = Math.min(this.reconnectDelay * Math.pow(1.5, attempts), this.reconnectDelayMax);
        this.logger.trace('scheduleReconnect', namespace, `${Math.round(delay)} ms`);
        const timer = this.p.window.setTimeout(() => {
            this.reconnectAttempts.set(namespace, attempts + 1);
            if (this.connectionPromises.has(namespace) === false) {
                void this.connect(namespace).then((ok) => {
                    if (ok === false) {
                        this.scheduleReconnect(namespace);
                    }
                });
            }
        }, delay);
        this.reconnectTimers.set(namespace, timer);
    }
}
