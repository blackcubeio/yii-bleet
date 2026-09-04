# Bleet API Reference

Documentation complète de la librairie de widgets Bleet pour Yii.

## Architecture

### Classe principale

```php
use Blackcube\Bleet\Bleet;
```

Factory statique pour tous les widgets. Chaque méthode retourne une instance immutable (fluent API avec `clone`).

### Constantes

```php
// Couleurs
Bleet::COLOR_PRIMARY   = 'primary';
Bleet::COLOR_SECONDARY = 'secondary';
Bleet::COLOR_SUCCESS   = 'success';
Bleet::COLOR_DANGER    = 'danger';
Bleet::COLOR_WARNING   = 'warning';
Bleet::COLOR_INFO      = 'info';
Bleet::COLOR_ACCENT    = 'accent';

Bleet::COLORS = [...]; // Toutes les couleurs

// Tailles
Bleet::SIZE_XS = 'xs';
Bleet::SIZE_SM = 'sm';
Bleet::SIZE_MD = 'md';
Bleet::SIZE_LG = 'lg';
Bleet::SIZE_XL = 'xl';

Bleet::SIZES = [...]; // Toutes les tailles
```

### AbstractWidget (base)

Tous les widgets héritant de `AbstractWidget` disposent de :

```php
// Couleurs (fluent)
->color(string)
->primary() ->secondary() ->success() ->danger() ->warning() ->info() ->accent()

// Tailles (fluent)
->size(string)
->xs() ->sm() ->md() ->lg() ->xl()

// Rendu
->render(): string
->__toString(): string

// Begin/End (certains widgets)
->begin(): ?string
static::end(): string
```

### Règle Aurelia

**IMPORTANT** : Les composants Aurelia (custom elements et custom attributes) doivent TOUJOURS utiliser les helpers :

```php
use Blackcube\Bleet\Aurelia;

// Pour les custom elements (<bleet-modal>, <bleet-toast>, etc.)
Aurelia::attributesCustomElement(array $options): array

// Pour les custom attributes (bleet-upload, bleet-select, etc.)
Aurelia::attributesCustomAttribute(array $options): string
```

---

## Widgets Typographie

### Code

```php
Bleet::code(string $content = '')
    ->content(string)
    ->encode(bool)                     // défaut: true
    // + AbstractWidget
    // Couleur par défaut : primary
```

### Del

```php
Bleet::del(string $content = '')
    ->content(string)
    ->datetime(string)                 // ISO 8601
    ->cite(string)                     // URL source
    ->encode(bool)
    // + AbstractWidget
    // Couleur par défaut : danger
```

### Em

```php
Bleet::em(string $content = '')
    ->content(string)
    ->encode(bool)
    // + AbstractWidget
    // Couleur par défaut : secondary
```

### Ins

```php
Bleet::ins(string $content = '')
    ->content(string)
    ->datetime(string)                 // ISO 8601
    ->cite(string)                     // URL source
    ->encode(bool)
    // + AbstractWidget
    // Couleur par défaut : success
```

### Mark

```php
Bleet::mark(string $content = '')
    ->content(string)
    ->encode(bool)
    // + AbstractWidget
    // Couleur par défaut : warning
```

### Paragraph (p)

```php
Bleet::p()
    ->content(string|Stringable...)
    ->encode(bool)
    ->id(?string)
    ->class(string...)
    ->addClass(string...)
    ->attribute(string, mixed)
    ->attributes(array)
    // + AbstractWidget
    // Couleur par défaut : secondary
```

### Pre

```php
Bleet::pre(string $content = '')
    ->content(string|Widget|Closure)
    ->beginContent() / ->endContent()
    ->title(string)                    // nom de fichier, etc.
    ->encode(bool)
    ->id(string)
    ->addClass(string...)
    ->attribute(string, mixed)
    // + AbstractWidget
```

### Small

```php
Bleet::small(string $content = '')
    ->content(string)
    ->encode(bool)
    ->id(string)
    ->addClass(string...)
    ->attribute(string, mixed)
    // + AbstractWidget
    // Taille par défaut : sm
```

### Strong

```php
Bleet::strong(string $content = '')
    ->content(string)
    ->encode(bool)
    ->id(string)
    ->addClass(string...)
    ->attribute(string, mixed)
    // + AbstractWidget
    // Couleur par défaut : secondary
```

---

## Widgets Titres

Tous les widgets H1-H6 partagent une API similaire.

### H1

Page header avec gradient, CTAs optionnels.

```php
Bleet::h1(string $title = '')
    ->title(string)
    ->subtitle(string)
    ->primaryCta(string $label, ?string $url)    // bouton blanc
    ->secondaryCta(string $label, ?string $url)  // bouton coloré
    ->id(string)
    ->addClass(string...)
    ->attribute(string, mixed)
    // + AbstractWidget
```

### H2

Section header avec fond coloré.

```php
Bleet::h2(string $title = '')
    ->title(string)
    ->subtitle(string)
    ->id(string)
    ->addClass(string...)
    ->attribute(string, mixed)
    // + AbstractWidget
```

### H3

Section header avec bordure bottom.

```php
Bleet::h3(string $title = '')
    ->title(string)
    ->subtitle(string)
    ->id(string)
    ->addClass(string...)
    ->attribute(string, mixed)
    // + AbstractWidget
```

### H4

Subsection header avec bordure bottom (plus petit).

```php
Bleet::h4(string $title = '')
    ->title(string)
    ->subtitle(string)
    ->id(string)
    ->addClass(string...)
    ->attribute(string, mixed)
    // + AbstractWidget
```

### H5

Minor section header (sans bordure).

```php
Bleet::h5(string $title = '')
    ->title(string)
    ->subtitle(string)
    ->id(string)
    ->addClass(string...)
    ->attribute(string, mixed)
    // + AbstractWidget
```

### H6

Smallest header (titre uniquement, pas de subtitle).

```php
Bleet::h6(string $title = '')
    ->title(string)
    ->id(string)
    ->addClass(string...)
    ->attribute(string, mixed)
    // + AbstractWidget
```

---

## Widgets Listes

### OrderedList (ol)

```php
Bleet::ol(array $items = [])
    ->items(array)                     // [string|array|ListItem]
    ->addItem(string|array|ListItem)
    ->encode(bool)
    ->id(string)
    ->addClass(string...)
    ->attribute(string, mixed)
    // + AbstractWidget
    // Couleur par défaut : secondary
```

### UnorderedList (ul)

```php
Bleet::ul(array $items = [])
    ->items(array)                     // [string|array|ListItem]
    ->addItem(string|array|ListItem)
    ->encode(bool)
    ->id(string)
    ->addClass(string...)
    ->attribute(string, mixed)
    // + AbstractWidget
    // Couleur par défaut : secondary
```

### ListItem

Helper pour items avec icônes. **N'hérite PAS de AbstractWidget.**

```php
Bleet::listItem(string $content = '')
    ->content(string)
    ->solid(string)                    // icône solid 24x24
    ->outline(string)                  // icône outline 24x24
    ->mini(string)                     // icône 20x20
    ->micro(string)                    // icône 16x16
    ->color(string)                    // override couleur parent
    ->primary() ->secondary() ->success() ->danger() ->warning() ->info()
    ->encode(bool)
```

### DescriptionList (dl)

```php
Bleet::dl(array $items = [])
    ->items(array)                     // [TermItem|array]
    ->addItem(TermItem|array)
    ->cols(int)                        // 0=stacked, 1=flex, 2+=grid
    ->tableMode(bool)                  // auto si cols>=2
    // + AbstractWidget
    // Couleur par défaut : primary
```

### TermItem

Helper pour dl. **N'hérite PAS de AbstractWidget.**

```php
Bleet::termItem(string $term = '')
    ->term(string)
    ->encodeTerm(bool)
    ->detail(string)                   // raccourci addDetail
    ->addDetail(DetailItem)
    ->details(array)                   // [DetailItem|string]
    ->level(int)                       // niveau arborescent
```

### DetailItem

Helper pour dd. **N'hérite PAS de AbstractWidget.**

```php
Bleet::detailItem(string $content = '')
    ->content(string)
    ->encode(bool)
```

---

## Widgets Liens & Boutons

### Anchor (a)

```php
Bleet::a(?string $url = null)
    ->url(string)
    ->content(string|Widget|Closure)
    ->beginContent() / ->endContent()
    ->target(string)                   // _blank, _self, etc.
    ->external()                       // target=_blank + rel
    ->encode(bool)
    ->id(string)
    ->addClass(string...)
    ->attribute(string, mixed)
    ->attributes(array)
    // + AbstractWidget
    // Couleur par défaut : primary
```

### Button

```php
Bleet::button(string $content = '')
    ->content(string)
    ->icon(string $name, string $type = 'outline')
    ->badge(int)                       // compteur position absolue
    ->outline(bool) ->ghost(bool) ->inverse(bool)
    ->submit() ->reset() ->button()
    ->disabled(bool)
    ->attributes(array)                // MERGE (pas replace)
    ->renderAsMenuItem(string ...$classes): string
    // + AbstractWidget
```

### ButtonsBar

```php
Bleet::buttonsBar()
    ->addButton(Button|Anchor)
    ->buttons(array)
```

---

## Widgets Formulaire

### Label

```php
Bleet::label(string $content = '')
    ->active(FormModelInterface, string)
    ->content(string)
    ->for(string)
    ->required(bool)                   // ajoute *
    ->id(string)
    ->addClass(string...)
    // + AbstractWidget
    // Couleur par défaut : secondary
```

### Input

```php
Bleet::input()
    ->active(FormModelInterface, string)
    // Types
    ->type(string)
    ->text() ->password() ->email() ->number() ->date() ->hidden() ->tel()
    // Attributs
    ->name(string)
    ->id(string)
    ->value(string)
    ->placeholder(string)
    ->disabled(bool)
    ->readonly(bool)
    ->required(bool)
    ->labelledBy(string)
    ->describedBy(string)
    ->autocomplete(string)
    // Décoration
    ->floatingLabel(string|Label)
    ->icon(string|Svg)                 // trailing (droite)
    ->iconLeft(string|Svg)             // leading (gauche)
    ->showable(bool)                   // toggle show/hide password
    ->addClass(string...)
    ->fieldData(array)
    // + AbstractWidget
    // Couleur par défaut : secondary
```

### Textarea

```php
Bleet::textarea()
    ->active(FormModelInterface, string)
    ->name(string)
    ->id(string)
    ->value(string)
    ->placeholder(string)
    ->rows(int)                        // défaut: 4
    ->cols(int)
    ->disabled(bool)
    ->readonly(bool)
    ->required(bool)
    ->labelledBy(string)
    ->describedBy(string)
    ->floatingLabel(string|Label)
    ->addClass(string...)
    ->fieldData(array)
    // + AbstractWidget
    // Couleur par défaut : secondary
```

### Select

```php
Bleet::select()
    ->active(FormModelInterface, string)
    ->name(string)
    ->label(string|bool)               // string=custom, false=hide
    ->placeholder(string)
    ->options(array)                   // ['value' => 'label'] ou groupes
    ->selected(?string)
    ->labelledBy(string)
    ->describedBy(string)
    ->id(string)
    ->addClass(string...)
    ->attribute(string, mixed)
    ->fieldData(array)
    // + AbstractWidget
    // Couleur par défaut : secondary
```

### Dropdown

Dropdown avancé avec recherche, sélection multiple, mode tags.

```php
Bleet::dropdown()
    ->active(FormModelInterface, string)
    ->name(string)
    ->label(string)
    ->placeholder(string)
    ->searchPlaceholder(string)
    ->emptyText(string)
    ->options(array)                   // ['value' => 'label'] ou groupes
    ->selected(string|array|null)
    ->searchable(bool)
    ->multiple(bool)
    ->withTags(bool)                   // mode Gmail
    ->labelledBy(string)
    ->describedBy(string)
    ->fieldData(array)
    // + AbstractWidget
    // Couleur par défaut : secondary
```

### Checkbox

```php
Bleet::checkbox()
    ->active(FormModelInterface, string)
    ->name(string)
    ->id(string)
    ->value(string)
    ->uncheckValue(string)             // hidden input pour unchecked
    ->checked(bool)
    ->disabled(bool)
    ->required(bool)
    ->label(string|Label)
    ->description(string)              // active mode avec description
    ->fieldData(array)
    // + AbstractWidget
    // Couleur par défaut : secondary
```

### CheckboxList

```php
Bleet::checkboxList()
    ->active(FormModelInterface, string)
    ->name(string)                     // auto-append [] si manquant
    ->id(string)
    ->items(array)                     // ['value' => 'label']
    ->values(array)                    // valeurs sélectionnées
    ->disabled(bool)
    ->required(bool)
    ->label(string)
    ->hint(string)
    ->fieldData(array)
    // + AbstractWidget
    // Couleur par défaut : secondary
```

### Radio

```php
Bleet::radio()
    ->active(FormModelInterface, string)
    ->name(string)
    ->id(string)
    ->value(string)
    ->checked(bool)
    ->disabled(bool)
    ->required(bool)
    ->label(string|Label)
    ->description(string)
    ->fieldData(array)
    // + AbstractWidget
    // Couleur par défaut : secondary
```

### RadioList

```php
Bleet::radioList()
    ->active(FormModelInterface, string)
    ->name(string)
    ->id(string)
    ->items(array)                     // ['value' => 'label']
    ->value(?string)
    ->disabled(bool)
    ->required(bool)
    ->label(string)
    ->hint(string)
    ->fieldData(array)
    // + AbstractWidget
    // Couleur par défaut : secondary
```

### Toggle

```php
Bleet::toggle()
    ->active(FormModelInterface, string)
    ->name(string)
    ->id(string)
    ->value(string)                    // défaut: '1'
    ->checked(bool)
    ->disabled(bool)
    ->label(string|Label)
    ->ariaLabel(string)                // si pas de label visible
    ->attributes(array)                // sur wrapper
    ->fieldData(array)
    // + AbstractWidget
    // Couleur par défaut : secondary
```

Génère automatiquement un hidden input avec value='0'.

### Upload

```php
Bleet::upload(?UploadConfig $config = null)
    ->active(FormModelInterface, string)
    ->endpoint(string)
    ->previewEndpoint(string)          // {name} placeholder
    ->deleteEndpoint(string)
    ->accept(array)                    // ['jpg', 'pdf', ...]
    ->maxFiles(int)                    // défaut: 1
    ->multiple(bool)
    ->chunkSize(int)
    ->name(string)
    ->id(string)
    ->value(?string)                   // fichiers existants (CSV)
    ->label(string)
    ->hint(string)
    ->disabled(bool)
    ->required(bool)
    // + AbstractWidget
    // Couleur par défaut : secondary
```

Utilise Resumable.js pour l'upload chunked. Custom attribute Aurelia : `bleet-upload`.

---

## Widgets Layout

### Card

```php
Bleet::card(string|Widget|Closure $content = '')
    ->content()
    ->beginContent() / ->endContent()
    ->header() / ->beginHeader() / ->endHeader()
    ->title(string)
    ->description(string)
    ->headerButton(label, url)
    ->footer() / ->beginFooter() / ->endFooter()
    ->footerButton(label, url)
    ->encode(bool)
    // + AbstractWidget
    // Couleur par défaut : secondary
```

### CardHeader

```php
Bleet::cardHeader()
    ->title(string)
    ->icon(string)
    ->left(Stringable|string)
    ->badges(Stringable[])
    ->button(Stringable|string)
    // + AbstractWidget
    // Couleur par défaut : primary
```

### EmptyState

Extends Card.

```php
Bleet::emptyState()
    ->icon(string)                     // heroicon outline
    ->title(string)
    ->description(string)
    ->button(string $label, string $url)  // auto-inclut icône +
    // + AbstractWidget
    // Couleur par défaut : secondary
```

### Figure

```php
Bleet::figure(string $src = '', string $alt = '')
    ->src(string)
    ->alt(string)
    ->caption(string|Widget|Closure)
    ->beginCaption() / ->endCaption()
    ->description(string)              // active mode détaillé
    ->prefix(string)                   // ex: 'Figure 1.'
    ->center()
    ->rounded()
    // + AbstractWidget
    // Couleur par défaut : primary
```

### Hr

```php
Bleet::hr(?string $text = null)
    ->text(string)                     // texte au centre
    ->id(string)
    ->addClass(string...)
    ->attribute(string, mixed)
    // + AbstractWidget
```

---

## Widgets Navigation

### Header

```php
Bleet::header()
    ->title(string)                    // défaut: 'Bleet'
    ->withBurgerMenu() / ->withoutBurgerMenu()
    ->search(?string $action, string $placeholder = 'Rechercher')
    ->actions(array)                   // [Anchor|Button]
    ->addAction(Anchor|Button)
    ->profile(Profile)
    ->id(string)
    ->addClass(string...)
    ->attribute(string, mixed)
    // + AbstractWidget
```

### Footer

```php
Bleet::footer()
    ->version(string)
    ->copyright(string)                // défaut: 'All rights reserved'
    // + AbstractWidget
    // Couleur par défaut : primary
```

### Sidebar

```php
Bleet::sidebar()
    ->logo(string|Img)
    ->encode(bool)                     // défaut: true (false pour HTML brut)
    ->separator(bool)                  // ligne entre logo et nav
    ->items(array)                     // [SidebarItem|array]
    ->addItem(SidebarItem|array)
    ->id(string)
    ->addClass(string...)
    // + AbstractWidget
```

### SidebarItem

**N'hérite PAS de AbstractWidget.**

```php
Bleet::sidebarItem(string $label = '')
    ->label(string)
    ->url(string)
    ->outline(string)                  // icône outline (défaut)
    ->solid(string)                    // icône solid
    ->active(bool)
    ->toggleId(string)                 // pour sous-menu
    ->children(array)                  // [SidebarItem]
    ->addChild(SidebarItem)
```

### Breadcrumb

```php
Bleet::breadcrumb()
    ->homeUrl(string)
    ->homeLabel(string)
    ->homeIcon(string)
    ->items(array)                     // [BreadcrumbItem|array]
    ->addItem(BreadcrumbItem|array)
    ->id(string)
    ->addClass(string...)
    // + AbstractWidget
```

### BreadcrumbItem

**N'hérite PAS de AbstractWidget.**

```php
Bleet::breadcrumbItem(string $label = '')
    ->label(string)
    ->url(?string)
    ->icon(string)
    ->encode(bool)
```

### Pager

```php
Bleet::pager(OffsetPaginator $paginator, UrlGeneratorInterface $urlGenerator)
    ->pageParam(string)                // défaut: 'page'
    ->maxButtonCount(int)              // défaut: 10
    ->hideOnSinglePage(bool)           // défaut: true
    ->showInfo(bool) / ->hideInfo()
    ->showMobileSelect(bool) / ->hideMobileSelect()
    ->id(string)
    ->addClass(string...)
    ->attribute(string, mixed)
    // + AbstractWidget
```

### Step

```php
Bleet::step()
    ->current(int)                     // index 0-based
    ->steps(array)                     // [['label' => '', 'url' => '']]
    ->addStep(string $label, ?string $url)
    ->id(string)
    ->addClass(string...)
    ->attribute(string, mixed)
    // + AbstractWidget
```

États automatiques : completed (< current), current (= current), upcoming (> current).

### Tab / TabPanel

```php
Tab::widget()
    ->id(string)
    ->addClass(string...)
    ->attribute(string, mixed)
    // + AbstractWidget
```

Usage (begin/end) :

```php
<?php Tab::widget()->primary()->begin() ?>
    <?php TabPanel::begin('Profil', active: true) ?>
        Contenu
    <?php TabPanel::end() ?>
<?php echo Tab::end() ?>
```

**TabPanel** (N'hérite PAS de AbstractWidget) :

```php
TabPanel::begin(string $label, bool $active = false, ?string $badge = null)
TabPanel::end()
```

---

## Widgets Dashboard

### StatCard

```php
Bleet::statCard(string $label = '', string $value = '')
    ->label(string)
    ->value(string)
    ->icon(string)                     // heroicon outline
    ->trend(float, ?string $label)     // auto-format +/- %, direction auto
    ->id(string)
    ->addClass(string...)
    ->attribute(string, mixed)
    // + AbstractWidget
```

### ShortcutCard

```php
Bleet::shortcutCard(string $label = '', string $url = '#')
    ->label(string)
    ->url(string)
    ->icon(string)                     // heroicon outline
    ->id(string)
    ->addClass(string...)
    ->attribute(string, mixed)
    // + AbstractWidget
```

### ActivityFeed

```php
Bleet::activityFeed()
    ->items(array)
    ->addItem(ActivityItem|array)
    // + AbstractWidget
    // Couleur par défaut : secondary
```

### ActivityItem

**N'hérite PAS de AbstractWidget.**

```php
Bleet::activityItem()
    ->content(string|Widget|Closure)
    ->datetime(string)
    ->avatar(string|Img)
    ->icon(string)
    ->iconColor(string)
    ->encode(bool)
```

### Progress

**Couleurs restreintes** : primary, success, warning, danger.

```php
Bleet::progress(int $percent = 0)
    ->percent(int)
    ->title(string)
    ->labels(array)                    // étapes sous la barre
    ->auto()                           // couleur auto selon %
    ->id(string)
    ->addClass(string...)
    ->attribute(string, mixed)
    // + AbstractWidget (couleurs restreintes)
```

Couleurs auto : < 33% → danger, < 66% → warning, ≥ 66% → success.

---

## Widgets Interactifs

### Alert

```php
Bleet::alert(string $content = '')
    ->content(string|Widget|Closure)
    ->beginContent() / ->endContent()
    ->title(?string)
    ->closable(bool)
    ->encode(bool)
    // + AbstractWidget
    // Couleur par défaut : info
```

### Badge

```php
Bleet::badge(string $content = '')
    ->content(string)
    ->dot(bool)                        // mode point coloré
    ->rounded(bool)                    // défaut: true (arrondi)
    ->flat(bool)                       // fond transparent
    ->encode(bool)
    // + AbstractWidget
    // Couleur par défaut : primary
```

### Avatar

```php
Bleet::avatar()
    ->initials(string)
    ->img(string|Img)
    ->sm() ->md() ->lg() ->xl()
    // + AbstractWidget
```

### Modal

Custom element Aurelia : `<bleet-modal>`.

```php
Bleet::modal(string $id)
    // + AbstractWidget
```

**Méthodes terminales :**

```php
->trigger(?string $url = null): array  // attributs pour bouton
->render(): string                     // coquille <bleet-modal>
->asArray(): array                     // export EA/AJAX
```

**Usage :**

```php
// Trigger (utilise Aurelia::attributesCustomAttribute)
<?php echo Bleet::button('Modifier')
    ->attributes(Bleet::modal('editUser')->trigger('/api/user/1'))
    ->render();

// Shell dans layout
<?php echo Bleet::modal('editUser')->render();
```

### Drawer

Custom element Aurelia : `<bleet-drawer>`.

```php
Bleet::drawer(string $id = 'drawer')
    // + AbstractWidget
    // Couleur par défaut : primary
```

**Méthodes terminales :**

```php
->trigger(?string $url = null): array
->render(): string
->asArray(): array
```

### Toast

Custom element Aurelia : `<bleet-toast>`.

```php
Bleet::toast()
    ->id(string)
    ->title(string)
    ->content(string)
    ->duration(int)                    // ms, 0 = pas d'auto-close
    ->icon(UiIcon|string)              // override auto-selection
    // + AbstractWidget
    // Couleur par défaut : info
```

**Icônes auto selon couleur :**
- success → check-circle
- danger → x-circle
- warning → exclamation-triangle
- autres → information-circle

**Méthodes terminales :**

```php
->trigger(): array                     // attributs pour bouton
->render(): string                     // composant invisible (page load)
->asArray(): array
```

### Toaster

Container pour toasts. Une seule fois dans le layout.

```php
Bleet::toaster()
    ->id(string)
    ->addClass(string...)
    // + AbstractWidget
```

---

## Widgets Média

### Img

```php
Bleet::img(string $src = '', string $alt = '')
    ->src(string)
    ->alt(string)
    ->rounded()
    ->resize(?int $width, ?int $height = null)
    ->width(int)
    ->height(int)
    ->quality(int)                     // 0-100
    ->rotate(float)                    // degrés, sens anti-horaire
    ->greyscale()
    ->watermark(string)
    ->cache(bool)                      // défaut: true (sinon base64)
    ->id(string)
    ->addClass(string...)
    ->attribute(string, mixed)
    // + AbstractWidget
```

**Méthode spéciale :**

```php
->url(): string                        // retourne l'URL ou data URI
```

**Configuration statique :**

```php
Img::setFileProvider(?FileProviderInterface $provider);
Img::setCachePath(string $path, string $url);
```

### File

```php
Bleet::file(string $src = '')
    ->src(string)
    ->label(string)
    ->cache(bool)                      // défaut: true
    ->url(): string
```

**Configuration statique :**

```php
File::setFileProvider(?FileProviderInterface $provider);
File::setCachePath(string $path, string $url);
```

### Svg

**N'hérite PAS de AbstractWidget** — Classe autonome.

```php
// Heroicons
Svg::heroicon()
    ->outline(string)                  // 24x24 outline
    ->solid(string)                    // 24x24 solid
    ->mini(string)                     // 20x20 solid
    ->micro(string)                    // 16x16 solid

// Custom icons
Svg::icon()
    ->ui(string)                       // 24x24 custom
    ->logo(string)                     // 200x200 custom

// Commun
    ->id(string)
    ->addClass(string...)
    ->attribute(string, mixed)
    ->attributes(array)
```

**Alias via Bleet :**

```php
Bleet::svg()->outline('chevron-right')->addClass('w-6', 'h-6')->render();
```

---

## Widgets Profil

### Profile

```php
Bleet::profile()
    ->avatar(string|Img)               // initiales ou image
    ->name(string)                     // affiché en desktop
    ->items(array)                     // [Anchor|Button]
    ->addItem(Anchor|Button)
    ->id(string)
    ->addClass(string...)
    // + AbstractWidget
```

---

## Helpers

### ActiveHelper

Helper pour générer noms/IDs d'input depuis les modèles.

```php
namespace Blackcube\Bleet\Helper;

ActiveHelper::getInputName(FormModelInterface $model, string $attribute): string
ActiveHelper::getInputId(FormModelInterface $model, string $attribute): string
ActiveHelper::nameToId(string $name): string
ActiveHelper::getAttributeName(string $attribute): string
```

### Aurelia

Helper pour les attributs Aurelia.

```php
namespace Blackcube\Bleet;

// Pour les custom elements
Aurelia::attributesCustomElement(array $options): array

// Pour les custom attributes
Aurelia::attributesCustomAttribute(array $options): string
```

---

## Enums

### UiColor

```php
namespace Blackcube\Bleet\Enum;

enum UiColor: string
{
    case Primary = 'primary';
    case Secondary = 'secondary';
    case Success = 'success';
    case Danger = 'danger';
    case Warning = 'warning';
    case Info = 'info';
    case Accent = 'accent';
}
```

### UiIcon

```php
namespace Blackcube\Bleet\Enum;

enum UiIcon: string
{
    case Info = 'information-circle';
    case Success = 'check-circle';
    case Warning = 'exclamation-triangle';
    case Danger = 'x-circle';
}
```

### DialogAction

```php
namespace Blackcube\Bleet\Enum;

enum DialogAction: string
{
    case Keep = 'keep';
    case Close = 'close';
}
```

### AjaxifyAction

```php
namespace Blackcube\Bleet\Enum;

enum AjaxifyAction: string
{
    case Refresh = 'refresh';
}
```

---

## Interfaces

### WidgetInterface

```php
namespace Blackcube\Bleet\Contracts;

interface WidgetInterface
{
    public function render(): string;
}
```

---

## Traits

### BleetModelAwareTrait

Binding au FormModelInterface via `->active($model, 'property')`.

### BleetFieldDataTrait

Gestion des attributs `data-*` via `->fieldData(array)`.

### BleetColorTrait

Helpers couleur : `textColorClass()`, `textMutedColorClass()`, `inputColorClasses()`, `focusVisibleRingClasses()`.

### BleetExportableTrait

Export pour EA/AJAX via `->asArray()`.

### SlotCaptureTrait

Capture de contenu via `->beginSlot()` / `->endSlot()`.

### RenderViewTrait

Rendu de vues PHP via `->renderView(string $viewName, array $params)`.

---

## Conventions Bleet

### Règle prepareClasses()

Tous les widgets implémentent :

```php
protected function prepareClasses(): array
{
    $baseClasses = [...];
    $colorClasses = [...];
    $sizeClasses = [...];
    return [...$baseClasses, ...$colorClasses, ...$sizeClasses];
}
```

Classes séparées en base/color/size, spread variadic, pas de helper hack.

### Couleurs par défaut

| Widget | Couleur |
|--------|---------|
| Code, CardHeader, Footer, Drawer, DescriptionList, Figure, H1-H6 | primary |
| Card, Checkbox, CheckboxList, Dropdown, Em, EmptyState, Input, Label, Paragraph, Radio, RadioList, Select, Strong, Textarea, Toggle, Upload, UnorderedList, OrderedList, ActivityFeed | secondary |
| Del | danger |
| Ins | success |
| Mark, Badge | warning |
| Alert, Toast | info |

### Widgets sans AbstractWidget

- ListItem
- BreadcrumbItem
- SidebarItem
- TermItem
- DetailItem
- ActivityItem
- TabPanel
- Svg
