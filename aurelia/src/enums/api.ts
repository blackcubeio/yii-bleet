export enum Transport {
    Socketio = 'socketio',
    Http = 'http',
}

export interface TransportWithConfig {
    type: Transport;
    baseUrl?: string;
}

export type TransportEntry = Transport | TransportWithConfig;
export type TransportConfig = TransportEntry | TransportEntry[];