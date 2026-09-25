# Bleet TypeScript/Aurelia API Reference

Documentation des services, composants, attributs et interfaces Aurelia pour Bleet.

## Architecture

```
bleet/
├── services/           → Services DI singleton
├── components/         → Custom Elements <bleet-*>
├── attributes/         → Custom Attributes [bleet-*]
├── interfaces/         → Types TypeScript
├── enums/              → Constantes
└── configure.ts        → Configuration
```

---

## Configuration

### BleetConfiguration

```typescript
import { BleetConfiguration } from 'bleet';

Aurelia.register(
    BleetConfiguration.customize((config) => {
        // Transport simple
        config.transports = Transport.Http;
        
        // Transport avec baseUrl
        config.transports = { type: Transport.Http, baseUrl: '/api' };
        
        // Transports multiples (fallback)
        config.transports = [
            { type: Transport.Socketio, baseUrl: 'ws://localhost:3000' },
            { type: Transport.Http, baseUrl: '/api' }
        ];
        
        // BaseUrl global (fallback)
        config.baseUrl = '/api';
    })
);
```

### IBleetConfiguration

```typescript
interface ConfigInterface {
    transports?: TransportConfig;
    baseUrl?: string;
}

// Injection
constructor(private config: IBleetConfiguration = resolve(IBleetConfiguration)) {}

// API
config.getConfig(): ConfigInterface
config.get<T>(key: string): T
config.set<T>(key: string, val: T): T
config.getTransports(): Transport[]
config.getBaseUrl(transport: Transport): string
config.getTransport(type: Transport): ITransport | null
config.getAvailableTransports(): ITransport[]
```

---

## Services

### IApiService

Service principal pour les requêtes API avec pipeline codec, cache et fallback transport.

```typescript
import { IApiService } from 'bleet';

constructor(private api: IApiService = resolve(IApiService)) {}
```

#### API

```typescript
// Builder fluent
api.url(path: string, params?: Record<string, any>): ApiRequestBuilder

// Raccourci HTML (dialogs AJAX)
api.fetchHtml(url: string): Promise<IHttpResponse<string>>
```

#### ApiRequestBuilder

```typescript
api.url('/users/:id', { id: 42 })
    // Query string
    .queryString({ search: 'foo', page: 1 })
    
    // Format entrée
    .fromJson(data?)           // Content-Type: application/json
    .fromForm(data?)           // Content-Type: application/x-www-form-urlencoded
    .fromMultipart(formData?)  // Content-Type: multipart/form-data
    
    // Format sortie
    .toJson()                  // Accept: application/json (défaut)
    .toText()                  // Accept: text/plain
    .toBlob()                  // Accept: application/octet-stream
    .toXls()                   // Accept: application/vnd.openxmlformats...
    .toArrayBuffer()           // Accept: application/octet-stream
    
    // Codecs
    .withInputCodec(codec)     // Transforme request avant envoi
    .withOutputCodec(codec)    // Transforme response après réception
    
    // Options
    .withPagination({ pageSize: 20, page: 1 })
    .withCache({ ttl: 300, storage: 'session' | 'memory' })
    
    // Exécution
    .get<T>(): Promise<IHttpResponse<T>>
    .post<T>(): Promise<IHttpResponse<T>>
    .put<T>(): Promise<IHttpResponse<T>>
    .patch<T>(): Promise<IHttpResponse<T>>
    .delete<T>(): Promise<IHttpResponse<T>>
    .request<T>(verb: string): Promise<IHttpResponse<T>>
```

#### Exemple

```typescript
// GET simple
const users = await this.api.url('/users').get<User[]>();

// POST avec données
const user = await this.api
    .url('/users')
    .fromJson({ name: 'John', email: 'john@example.com' })
    .post<User>();

// GET avec path params
const user = await this.api
    .url('/users/:id', { id: 42 })
    .get<User>();

// Upload fichier
const result = await this.api
    .url('/upload')
    .fromMultipart(formData)
    .post<UploadResult>();

// Avec cache
const data = await this.api
    .url('/products')
    .withCache({ ttl: 60, storage: 'session' })
    .get<Product[]>();
```

---

### IHttpService

Transport HTTP via Fetch API. Implémente `ITransport`.

```typescript
import { IHttpService } from 'bleet';

// Automatiquement configuré avec :
// - X-Requested-With: XMLHttpRequest
// - credentials: include
```

---

### ISocketioService

Transport WebSocket via Socket.io. Implémente `ITransport`. Chargé à la demande.

```typescript
import { ISocketioService } from 'bleet';

constructor(private socket: ISocketioService = resolve(ISocketioService)) {}

// API
socket.connect(namespace?: string, options?: Record<string, any>): Promise<void>
socket.disconnect(): void
socket.isConnected(): boolean
socket.isAvailable(): boolean
```

**Note** : Requiert `npm install socket.io-client`.

---

### IStorageService

Wrapper localStorage avec JSON auto.

```typescript
import { IStorageService } from 'bleet';

constructor(private storage: IStorageService = resolve(IStorageService)) {}

storage.load(key: string, def?: any): any
storage.save(key: string, value: any): void
storage.remove(key: string): void
```

---

### ISvgService

Service d'icônes SVG intégrées.

```typescript
import { ISvgService } from 'bleet';

constructor(private svg: ISvgService = resolve(ISvgService)) {}

svg.get(icon: string): string | null  // Retourne SVG ou icon tel quel
svg.has(key: string): boolean
```

**Icônes intégrées** : `information-circle`, `check-circle`, `exclamation-triangle`, `x-circle`, `x-mark`

---

### ITransitionService

Gestion des transitions CSS avec callback.

```typescript
import { ITransitionService } from 'bleet';

constructor(private transition: ITransitionService = resolve(ITransitionService)) {}

transition.securityTimeout: number  // défaut: 2000ms

transition.run(
    element: HTMLElement,
    before: (e: HTMLElement) => void,
    after?: (e: HTMLElement) => void
): void
```

---

### ITrapFocusService

Focus trap pour modals/drawers. **Transient** (nouvelle instance par injection).

```typescript
import { ITrapFocusService } from 'bleet';

constructor(private trap: ITrapFocusService = resolve(ITrapFocusService)) {}

trap.focusableElementsQuerySelector: string  // Sélecteur éléments focusables

trap.start(
    opener: HTMLElement,           // Élément qui a ouvert (recevra le focus au stop)
    target: HTMLElement,           // Container du trap
    globalElement: HTMLElement,    // Pour détection clic extérieur
    startCallback?: Function,
    stopCallback?: Function,
    initialFocusElement?: HTMLElement
): Promise<void>

trap.stop(): Promise<void>
```

**Raccourcis clavier** : Tab (loop), Shift+Tab (reverse), ArrowUp/Down (no loop), Escape (stop)

---

## Custom Elements

### &lt;bleet-modal&gt;

Modal AJAX avec chargement dynamique.

```html
<bleet-modal id="editUser"></bleet-modal>
```

```typescript
@bindable id: string = '';
```

**Cycle de vie** :
1. Écoute `Channels.Modal` pour `ModalAction.Open/Close/Toggle`
2. Charge URL via `IApiService`
3. Applique `IDialogResponse` (header/content/footer/actions)
4. Gère soumission formulaire automatiquement

**Fermeture** : `data-modal="close"` sur n'importe quel élément

---

### &lt;bleet-drawer&gt;

Drawer latéral AJAX (slide-in depuis la droite).

```html
<bleet-drawer id="settings"></bleet-drawer>
```

```typescript
@bindable id: string = '';
```

**Identique à modal** mais avec transition `translate-x`.

**Fermeture** : `data-drawer="close"` sur n'importe quel élément

---

### &lt;bleet-overlay&gt;

Fond semi-transparent pour modals/drawers.

```html
<bleet-overlay class="fixed inset-0 z-40 bg-black/50 hidden opacity-0 transition-opacity"></bleet-overlay>
```

Écoute `Channels.Overlay` pour `OverlayAction.Open/Close/Toggle`.

---

### &lt;bleet-toaster&gt;

Container pour toasts. Une seule instance dans le layout.

```html
<bleet-toaster></bleet-toaster>
```

Écoute `Channels.Toaster` pour `ToasterAction.Add/Remove`.

---

### &lt;bleet-toast&gt;

Toast individuel (géré par toaster).

```typescript
@bindable id: string = '';
@bindable color: UiColor = UiColor.Info;
@bindable icon: UiToastIcon = UiToastIcon.Info;
@bindable title: string = '';
@bindable content: string = '';
@bindable duration: number = 0;  // 0 = pas d'auto-close
```

---

### &lt;bleet-toaster-trigger&gt;

Déclenche un toast au chargement de la page.

```html
<bleet-toaster-trigger
    id="welcome"
    color="success"
    icon="check-circle"
    title="Bienvenue"
    content="Connexion réussie"
    duration="5000"
></bleet-toaster-trigger>
```

---

### &lt;bleet-ajaxify&gt;

Zone rechargeable via AJAX.

```html
<bleet-ajaxify id="userList" url="/api/users/list"></bleet-ajaxify>
```

```typescript
@bindable id: string = '';
@bindable url?: string;
```

Écoute `Channels.Ajaxify` pour `AjaxifyAction.Refresh`.

---

## Custom Attributes

### [bleet-modal-trigger]

Déclenche l'ouverture d'un modal.

```html
<button bleet-modal-trigger="editUser" url="/modal/user/42">Modifier</button>
```

```typescript
@bindable({ primary: true }) id: string = '';
@bindable url: string = '';
@bindable color: string = 'primary';
```

---

### [bleet-drawer-trigger]

Déclenche l'ouverture d'un drawer.

```html
<button bleet-drawer-trigger="settings" url="/drawer/settings">Paramètres</button>
```

```typescript
@bindable({ primary: true }) id: string = '';
@bindable url: string = '';
@bindable color: string = 'primary';
```

---

### [bleet-toaster-trigger]

Déclenche un toast au clic.

```html
<button bleet-toaster-trigger="notif" color="success" content="Action réussie">Clic</button>
```

```typescript
@bindable({ primary: true }) id: string = '';
@bindable color: UiColor = UiColor.Info;
@bindable icon: UiToastIcon = UiToastIcon.Info;
@bindable title: string = '';
@bindable content: string = '';
@bindable duration: number = 0;
```

---

### [bleet-alert]

Gestion fermeture alert avec animation.

```html
<div bleet-alert class="transition-all overflow-hidden">
    <button data-alert="close">×</button>
    Contenu alert
</div>
```

**Fermeture** : `data-alert="close"` déclenche animation height→0 puis remove.

---

### [bleet-badge]

Badge avec bouton de suppression.

```html
<span bleet-badge="tag-1">
    Tag
    <button data-badge="remove">×</button>
</span>
```

```typescript
@bindable({ primary: true }) id: string = '';
```

Publie `Channels.Badge` avec `BadgeAction.Remove` puis `element.remove()`.

---

### [bleet-burger]

Bouton burger pour ouvrir le menu sidebar.

```html
<button bleet-burger>☰</button>
```

Publie `Channels.Menu` avec `MenuAction.Open`.

---

### [bleet-menu]

Sidebar avec sous-menus collapsibles.

```html
<nav bleet-menu class="-translate-x-full transition-transform">
    <button data-menu="close">×</button>
    <button data-menu="toggle-button-sub1">Section</button>
    <ul data-menu="toggle-list-sub1" class="hidden">...</ul>
</nav>
```

**Data attributes** :
- `data-menu="close"` : Ferme le menu
- `data-menu="toggle-button-{id}"` : Toggle sous-menu
- `data-menu="toggle-list-{id}"` : Liste à toggle
- `data-menu="icon"` : Icône rotation (sur svg dans le button)

État des sous-menus persisté en localStorage.

---

### [bleet-profile]

Dropdown profil utilisateur avec focus trap.

```html
<div bleet-profile="user-menu">
    <button data-profile="toggle">Avatar</button>
    <div data-profile="panel" class="hidden">
        <a href="/profile">Mon profil</a>
        <a href="/logout">Déconnexion</a>
    </div>
</div>
```

```typescript
@bindable({ primary: true }) id: string = '';
```

---

### [bleet-select]

Select custom avec panel dropdown.

```html
<div bleet-select>
    <select class="sr-only">
        <option value="1">Option 1</option>
        <option value="2">Option 2</option>
    </select>
    <button>
        <span data-select="value">Sélectionner</span>
    </button>
    <div data-select="items" class="hidden">
        <template data-select="item-template">
            <button data-value="">
                <span data-select="item-text"></span>
                <span data-select="item-check"></span>
            </button>
        </template>
    </div>
</div>
```

**Data attributes** :
- `data-select="value"` : Texte affiché dans le bouton
- `data-select="items"` : Container des options
- `data-select="item-template"` : Template option
- `data-select="item-text"` : Texte option
- `data-select="item-check"` : Checkmark
- `data-value` : Valeur de l'option
- `data-class-active` / `data-class-inactive` : Classes à swap

---

### [bleet-dropdown]

Dropdown avancé avec recherche, multi-sélection, tags.

```html
<div bleet-dropdown>
    <select multiple class="sr-only">...</select>
    <button>
        <span data-dropdown="placeholder">Sélectionner</span>
        <span data-dropdown="value"></span>
        <span data-dropdown="tags"></span>
    </button>
    <div data-dropdown="items" class="hidden">
        <input data-dropdown="search" placeholder="Rechercher...">
        <div data-dropdown="items-container"></div>
        <div data-dropdown="empty" class="hidden">Aucun résultat</div>
        <template data-dropdown="item-template">...</template>
        <template data-dropdown="tag-template">
            <span data-tag-value="">
                <span data-dropdown="tag-text"></span>
                <button data-dropdown="tag-remove">×</button>
            </span>
        </template>
    </div>
</div>
```

Détecte automatiquement `multiple` et mode tags (présence de `tag-template`).

---

### [bleet-tabs]

Onglets avec synchronisation select mobile.

```html
<div bleet-tabs>
    <select class="sm:hidden">
        <option value="0">Tab 1</option>
        <option value="1">Tab 2</option>
    </select>
    <div class="hidden sm:block">
        <button data-tabs="tab-0" aria-selected="true">Tab 1</button>
        <button data-tabs="tab-1" aria-selected="false">Tab 2</button>
    </div>
    <div data-tabs="panel-0">Contenu 1</div>
    <div data-tabs="panel-1" class="hidden">Contenu 2</div>
</div>
```

---

### [bleet-pager]

Pagination avec select mobile.

```html
<div bleet-pager>
    <select data-pager="select">
        <option value="1">Page 1</option>
        <option value="2">Page 2</option>
    </select>
    <a data-pager="page-1" href="?page=1">1</a>
    <a data-pager="page-2" href="?page=2">2</a>
</div>
```

---

### [bleet-password]

Toggle show/hide password.

```html
<div bleet-password>
    <input type="password">
    <button data-password="toggle">
        <svg data-password="icon-hidden">👁</svg>
        <svg data-password="icon-visible" class="hidden">👁‍🗨</svg>
    </button>
</div>
```

---

### [bleet-upload]

Upload fichier avec Resumable.js (chunked).

```html
<div bleet-upload
     endpoint="/api/upload"
     preview-endpoint="/api/files/{name}"
     delete-endpoint="/api/files/{name}"
     accept="jpg,png,pdf"
     max-files="5"
     chunk-size="1048576">
    <input type="hidden" name="files" value="">
</div>
```

```typescript
@bindable endpoint: string = '';
@bindable previewEndpoint: string = '';
@bindable deleteEndpoint: string = '';
@bindable accept: string = '';         // Extensions séparées par virgule
@bindable maxFiles: number = 1;
@bindable chunkSize: number = 1048576; // 1MB
```

---

### [bleet-popover]

Popover tooltip positionné automatiquement au-dessus (ou en-dessous) de l'élément déclencheur.

```html
<div bleet-popover="my-popover"
     class="fixed z-50 hidden [&.is-open]:block bg-secondary-50 text-secondary-700 text-sm border rounded shadow-sm px-3 py-1.5 pointer-events-none whitespace-nowrap">
    Texte du tooltip
</div>
```

```typescript
@bindable({ primary: true }) id: string = '';
```

**Fonctionnement** :
- Écoute `Channels.Popover` pour `PopoverAction.Open/Close/Toggle`
- Positionne l'élément centré au-dessus du trigger via `DOMRect`
- Fallback en dessous si pas assez d'espace au-dessus
- Clamp horizontal pour rester dans le viewport
- Toggle la classe CSS `is-open`
- Publie `Channels.PopoverStatus` avec `PopoverStatus.Opened/Closed`

---

### [bleet-popover-trigger]

Déclenche l'ouverture/fermeture d'un popover au survol (mouseenter/mouseleave).

```html
<button bleet-popover-trigger="my-popover">Hover me</button>
```

```typescript
@bindable({ primary: true }) id: string = '';
@bindable() absolute: boolean = true;  // Envoie DOMRect pour positionnement absolu
```

**Fonctionnement** :
- `mouseenter` → publie `PopoverAction.Open` avec `DOMRect` du trigger (si `absolute=true`)
- `mouseleave` → publie `PopoverAction.Close`
- Plusieurs triggers peuvent partager le même `id` de popover (ex : boutons dans une liste)

---

### [bleet-ajaxify-trigger]

Trigger AJAX générique pour formulaires partiels.

```html
<form action="/api/items" method="POST">
    <input type="hidden" name="_csrf" value="...">
    
    <div bleet-ajaxify-trigger url="/api/item/toggle" verb="POST" id="toggle-1">
        <input type="checkbox" name="active" value="1">
    </div>
</form>
```

```typescript
@bindable({ primary: true }) url: string = '';
@bindable verb: string = '';           // Défaut: form.method ou POST
@bindable event: string = 'click';     // Événement déclencheur
@bindable id: string = '';             // Pour collecter inputs data-ajaxify="{id}"
```

**Réponse** : `IAjaxifyResponse` avec `element` (HTML pour sync), `toast`, `ajaxify`.

---

## Interfaces

### IHttpRequest

```typescript
interface IHttpRequest<T = any> {
    url: string;
    method: string;
    headers: Record<string, string>;
    data?: T;
    pathParams?: Record<string, any>;
}
```

### IHttpResponse

```typescript
interface IHttpResponse<T = any> {
    statusCode: number;
    headers: Record<string, string>;
    body: T;
    pagination?: IPagination;
}
```

### IPagination

```typescript
interface IPagination {
    page: number;
    pageCount: number;
    pageSize: number;
    totalCount: number;
}
```

### ICodec

```typescript
interface ICodec<TIn = any, TOut = any> {
    encode?: (ctx: IHttpRequest<TIn>) => Promise<IHttpRequest<TIn>>;
    decode?: (ctx: IHttpResponse<TOut>) => Promise<IHttpResponse<TOut>>;
}
```

### ITransport

```typescript
interface ITransport {
    readonly type: Transport;
    isAvailable(): boolean;
    prepareRequest(ctx: IHttpRequest): IHttpRequest;
    execute<T>(ctx: IHttpRequest, responseType?: string): Promise<IHttpResponse<T>>;
}
```

### IDialogResponse

Réponse serveur pour modal/drawer.

```typescript
interface IDialogResponse {
    // Style
    color?: UiColor;
    icon?: string | null;
    
    // Contenu HTML
    header?: string;
    content?: string;
    footer?: string;
    
    // Action primaire (obligatoire)
    action: DialogAction;        // 'keep' | 'close'
    
    // Actions secondaires (combinables)
    toast?: IToast;
    ajaxify?: IAjaxify;
    redirect?: string;
    refresh?: boolean;
}
```

### IToast

```typescript
interface IToast {
    id?: string;
    icon?: UiToastIcon;
    color: UiColor;
    title?: string;
    content: string;
    duration?: number;           // ms, 0 = pas d'auto-close
}
```

### IAjaxify

```typescript
interface IAjaxify {
    action: AjaxifyAction;       // 'refresh'
    id?: string;
    url?: string;
}
```

### IAjaxifyResponse

```typescript
interface IAjaxifyResponse {
    element?: string;            // HTML pour remplacer l'élément
    checked?: boolean;
    toast?: IToast;
    ajaxify?: IAjaxify;
}
```

---

## Enums

### Transport

```typescript
enum Transport {
    Socketio = 'socketio',
    Http = 'http',
}
```

### Channels (Event Aggregator)

```typescript
enum Channels {
    Overlay = 'bleet:overlay',
    OverlayStatus = 'bleet:overlay:status',
    Modal = 'bleet:modal',
    ModalStatus = 'bleet:modal:status',
    Drawer = 'bleet:drawer',
    DrawerStatus = 'bleet:drawer:status',
    Toaster = 'bleet:toaster',
    ToasterStatus = 'bleet:toaster:status',
    Menu = 'bleet:menu',
    MenuStatus = 'bleet:menu:status',
    Badge = 'bleet:badge',
    Profile = 'bleet:profile',
    ProfileStatus = 'bleet:profile:status',
    Ajaxify = 'bleet:ajaxify',
    Popover = 'bleet:popover',
    PopoverStatus = 'bleet:popover:status',
}
```

### Actions

```typescript
enum OverlayAction { Open, Close, Toggle }
enum ModalAction { Open, Close, Toggle }
enum DrawerAction { Open, Close, Toggle }
enum MenuAction { Open, Close, Toggle }
enum ProfileAction { Open, Close, Toggle }
enum ToasterAction { Add, Remove }
enum BadgeAction { Remove }
enum AjaxifyAction { Refresh }
enum PopoverAction { Open, Close, Toggle }
enum DialogAction { Keep, Close }
```

### Status

```typescript
enum OverlayStatus { Opening, Closing, Opened, Closed }
enum ModalStatus { Opening, Closing, Opened, Closed }
enum DrawerStatus { Opening, Closing, Opened, Closed }
enum MenuStatus { Opening, Closing, Opened, Closed }
enum ProfileStatus { Opening, Closing, Opened, Closed }
enum ToasterStatus { Added, Removed }
enum PopoverStatus { Opening, Closing, Opened, Closed }
```

### UiColor

```typescript
enum UiColor {
    Primary = 'primary',
    Secondary = 'secondary',
    Success = 'success',
    Danger = 'danger',
    Warning = 'warning',
    Info = 'info',
    Accent = 'accent',
}
```

### UiIcon / UiToastIcon

```typescript
enum UiToastIcon {
    Info = 'information-circle',
    Success = 'check-circle',
    Warning = 'exclamation-triangle',
    Danger = 'x-circle',
}

enum UiIcon {
    // Alias courts
    Info = 'information-circle',
    Success = 'check-circle',
    Warning = 'exclamation-triangle',
    Danger = 'x-circle',
    // Alias longs (match heroicon)
    InformationCircle = 'information-circle',
    CheckCircle = 'check-circle',
    ExclamationTriangle = 'exclamation-triangle',
    XCircle = 'x-circle',
}
```

---

## Codecs

### RequestCodec

Ajoute `X-Requested-With: XMLHttpRequest`.

```typescript
import { RequestCodec } from 'bleet/codecs/request-codec';

api.url('/endpoint')
    .withInputCodec(RequestCodec.codec)
    .get();
```

---

## Usage PHP avec Aurelia

### Attributs générés par PHP

Les widgets PHP génèrent des attributs pour Aurelia via :

```php
use Blackcube\Bleet\Aurelia;

// Custom attributes (string JSON)
Aurelia::attributesCustomAttribute(array $options): string

// Custom elements (array pour Html::tag)
Aurelia::attributesCustomElement(array $options): array
```

### Exemples

```php
// Modal trigger
Bleet::button('Modifier')
    ->attributes(Bleet::modal('editUser')->trigger('/api/user/42'))
    ->render();
// Génère: <button bleet-modal-trigger="editUser" url="/api/user/42">

// Toast trigger
Bleet::toast()
    ->id('welcome')
    ->color(UiColor::Success)
    ->title('Bienvenue')
    ->content('Connexion réussie')
    ->duration(5000)
    ->render();
// Génère: <bleet-toaster-trigger id="welcome" color="success" ...>

// Drawer trigger
Bleet::button('Paramètres')
    ->attributes(Bleet::drawer('settings')->trigger('/api/settings'))
    ->render();
// Génère: <button bleet-drawer-trigger="settings" url="/api/settings">
```

---

## Exports

```typescript
// Attributes
BleetAlertCustomAttribute
BleetBadgeCustomAttribute
BleetBurgerCustomAttribute
BleetDrawerTriggerCustomAttribute
BleetDropdownCustomAttribute
BleetMenuCustomAttribute
BleetModalTriggerCustomAttribute
BleetPagerCustomAttribute
BleetPasswordCustomAttribute
BleetProfileCustomAttribute
BleetSelectCustomAttribute
BleetTabsCustomAttribute
BleetToasterTriggerCustomAttribute
BleetUploadCustomAttribute
BleetAjaxifyTriggerCustomAttribute
BleetPopoverCustomAttribute
BleetPopoverTriggerCustomAttribute

// Components
BleetOverlay
BleetToast
BleetToaster
BleetToasterTrigger
BleetModal
BleetDrawer
BleetAjaxify

// Services
IHttpService
IApiService
ISocketioService
IStorageService
ITransitionService
ITrapFocusService
ISvgService

// Enums
Channels, Transport
OverlayAction, OverlayStatus
ModalAction, ModalStatus
DrawerAction, DrawerStatus
ToasterAction, ToasterStatus
MenuAction, MenuStatus
ProfileAction, ProfileStatus
BadgeAction, AjaxifyAction
PopoverAction, PopoverStatus
DialogAction
UiColor, UiToastIcon, UiIcon

// Interfaces
ITransport
IOverlay, IOverlayStatus
IModal, IModalStatus
IDrawer, IDrawerStatus
IToaster, IToasterStatus, IToast
IMenu, IMenuStatus
IBadge
IProfile, IProfileStatus
IDialogResponse
IPopover, IPopoverStatus
IAjaxify, IAjaxifyResponse
```
