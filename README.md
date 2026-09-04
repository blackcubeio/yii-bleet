# Blackcube Bleet

UIKit for Yii — PHP widgets + Aurelia 2 components.

[![License](https://img.shields.io/badge/license-BSD--3--Clause-blue.svg)](LICENSE.md)
[![Packagist Version](https://img.shields.io/packagist/v/blackcube/yii-bleet.svg)](https://packagist.org/packages/blackcube/yii-bleet)

## Installation

```bash
composer require blackcube/yii-bleet
```

The Aurelia 2 companion package:

```bash
npm install @blackcube/aurelia2-bleet
```

## Requirements

- Yii (view, html, widget, router, form-model)
- [blackcube/yii-form](https://github.com/blackcubeio/yii-form) — the form fields, without styling
- [blackcube/icons](https://github.com/blackcubeio/icons) — Heroicons and the SVG renderer
- Aurelia 2 (for interactive components: modal, drawer, ajaxify, toaster, upload, quill)

## Where the fields come from

```
blackcube/yii-bridge-model   the model
blackcube/yii-form           the fields, native HTML, no CSS
blackcube/yii-bleet          extends them and dresses them for its UI
```

Bleet inherits every field from `blackcube/yii-form` and only adds the
styling: classes, colors, sizes — plus what is not native, such as the icon
inside a field, the password toggle, the drawn checkmark, the switch, the
searchable dropdown and the resumable upload dropzone.

A project that wants the fields without Bleet's look uses `yii-form`
directly and overrides the same extension points.

## What it is

A fluent PHP API that generates HTML. You never write raw HTML in views.

Aurelia 2 handles interactivity client-side: AJAX loading, drawers, modals, toasts, rich text, file uploads.

7 colors (`primary`, `secondary`, `success`, `danger`, `warning`, `info`, `accent`), 5 sizes (`xs`, `sm`, `md`, `lg`, `xl`).

## Quick Start

### Buttons

```php
use Blackcube\Bleet\Bleet;

Bleet::button('Save')->primary()->render();
Bleet::button('Delete')->danger()->sm()->render();

Bleet::buttonGroup()
    ->addButton(Bleet::button()->icon('pencil')->info())
    ->addButton(Bleet::button()->icon('trash')->danger())
    ->render();
```

### Form fields

```php
Bleet::label('Name')->render();
Bleet::input($model, 'name')->render();
Bleet::textarea($model, 'description')->render();
Bleet::select($model, 'status')->optionsData($items)->render();
Bleet::checkbox($model, 'active')->render();
Bleet::toggle($model, 'published')->render();
```

### Upload

```php
Bleet::upload($model, 'document')
    ->accept(['jpg', 'jpeg', 'png', 'pdf'])
    ->maxFiles(5)
    ->multiple()
    ->render();
```

### Elastic fields (auto-render from JSON Schema)

```php
Bleet::elastic($model, $attribute, $elasticOptions)->render();
```

### Cards

```php
Bleet::cardHeader()->title('Contents')->icon('document-text')->primary()->render();
Bleet::card('Body content here')->render();
Bleet::statCard('Visitors', '12,458')->icon('users')->render();
```

### Layout

```php
Bleet::header('Admin')->render();
Bleet::sidebar()->addItem(Bleet::sidebarItem('Dashboard'))->render();
Bleet::breadcrumb()->render();
Bleet::footer()->render();
```

### Feedback

```php
Bleet::alert()->title('Warning')->content('Check your input')->warning()->render();
Bleet::toast()->success()->title('Saved')->content('Changes applied')->render();
```

### Modal and Drawer (AJAX)

```php
// Trigger — loads content via AJAX
Bleet::button('Edit')->attributes(Bleet::modal('editUser')->trigger('/user/1'))->render();
Bleet::button('Details')->attributes(Bleet::drawer()->trigger('/user/1'))->render();

// Shell — once in layout
Bleet::modal('editUser')->render();
Bleet::drawer()->render();
```

### Ajaxify

```php
// Trigger on element
Bleet::toggle()->attributes(Bleet::ajaxify()->event('change')->trigger())->render();

// Ajaxify zone
Bleet::ajaxify('myZone')->url('/api/content')->open();
// ... content ...
Bleet::ajaxify()->close();
```

## Widgets

### Layout

| Widget | Factory | Description |
|--------|---------|-------------|
| Header | `Bleet::header()` | Sticky header |
| Footer | `Bleet::footer()` | Page footer |
| Sidebar | `Bleet::sidebar()` | Navigation sidebar |
| SidebarItem | `Bleet::sidebarItem()` | Sidebar entry |
| Breadcrumb | `Bleet::breadcrumb()` | Breadcrumb trail |

### Forms

| Widget | Factory | Description |
|--------|---------|-------------|
| Input | `Bleet::input()` | Text input |
| Textarea | `Bleet::textarea()` | Multiline input |
| Select | `Bleet::select()` | Select with custom display (`searchable()`, `multiple()`, `withTags()`) |
| Checkbox | `Bleet::checkbox()` | Single checkbox |
| CheckboxList | `Bleet::checkboxList()` | Checkbox group |
| Radio | `Bleet::radio()` | Single radio |
| RadioList | `Bleet::radioList()` | Radio group |
| Toggle | `Bleet::toggle()` | Toggle switch |
| Upload | `Bleet::upload()` | File upload (Resumable.js) |
| Elastic | `Bleet::elastic()` | Auto-render from JSON Schema |
| Label | `Bleet::label()` | Form label |

### Feedback

| Widget | Factory | Description |
|--------|---------|-------------|
| Alert | `Bleet::alert()` | Alert message (dismissible) |
| Toast | `Bleet::toast()` | Toast notification |
| Toaster | `Bleet::toaster()` | Toast container |
| Modal | `Bleet::modal()` | AJAX modal dialog |
| Drawer | `Bleet::drawer()` | AJAX side panel |
| Ajaxify | `Bleet::ajaxify()` | AJAX zone and triggers |

### Content

| Widget | Factory | Description |
|--------|---------|-------------|
| Card | `Bleet::card()` | Container card |
| CardHeader | `Bleet::cardHeader()` | Colored card header with icon |
| Badge | `Bleet::badge()` | Inline badge |
| Button | `Bleet::button()` | Action button |
| ButtonGroup | `Bleet::buttonGroup()` | Grouped action buttons |
| StatCard | `Bleet::statCard()` | Dashboard stat card with trend |
| ShortcutCard | `Bleet::shortcutCard()` | Clickable shortcut card |
| ActivityFeed | `Bleet::activityFeed()` | Activity timeline |
| EmptyState | `Bleet::emptyState()` | Placeholder with CTA |
| Progress | `Bleet::progress()` | Progress bar |
| Step | `Bleet::step()` | Stepper / progress steps |
| Pager | `Bleet::pagination()` | Pagination |
| Profile | `Bleet::profile()` | User profile widget |

### Typography

| Widget | Factory | Description |
|--------|---------|-------------|
| H1–H6 | `Bleet::h1()` … `Bleet::h6()` | Headings |
| Hr | `Bleet::hr('Section')` | Separator with label |
| Paragraph | `Bleet::p()` | Paragraph |
| Anchor | `Bleet::a()` | Link |
| Strong | `Bleet::strong()` | Bold |
| Em | `Bleet::em()` | Italic |
| Code | `Bleet::code()` | Inline code |
| Pre | `Bleet::pre()` | Code block |
| Blockquote | `Bleet::blockquote()` | Blockquote |
| Mark | `Bleet::mark()` | Highlight |
| Small | `Bleet::small()` | Small text |
| Del / Ins | `Bleet::del()` / `Bleet::ins()` | Deleted / inserted text |
| Abbr | `Bleet::abbr()` | Abbreviation |
| Lists | `Bleet::ul()` / `Bleet::ol()` / `Bleet::dl()` | Unordered, ordered, description lists |
| Img | `Bleet::img()` | Image |
| Figure | `Bleet::figure()` | Image with caption |
| Svg | `Bleet::svg()` / `Bleet::icon()` | Heroicons / custom SVG |

## Aurelia 2 Components

Interactive components live in `@blackcube/aurelia2-bleet`. They are driven by the PHP widgets — you rarely instantiate them directly.

### Custom elements

| Component | Element | Description |
|-----------|---------|-------------|
| Ajaxify | `<bleet-ajaxify>` | AJAX content zone |
| Drawer | `<bleet-drawer>` | Side panel |
| Modal | `<bleet-modal>` | Centered dialog |
| Toaster | `<bleet-toaster>` | Toast container |
| Toast | `<bleet-toast>` | Toast notification |
| Quill | `<bleet-quilljs>` | Rich text editor |

### Custom attributes

| Attribute | Description |
|-----------|-------------|
| `bleet-ajaxify-trigger` | Triggers AJAX load |
| `bleet-drawer-trigger` | Opens a drawer |
| `bleet-modal-trigger` | Opens a modal |
| `bleet-toaster-trigger` | Triggers a toast |
| `bleet-alert` | Alert behavior |
| `bleet-badge` | Badge behavior |
| `bleet-burger` | Burger menu toggle |
| `bleet-dropdown` | Advanced dropdown |
| `bleet-select` | Custom select |
| `bleet-menu` | Menu behavior |
| `bleet-pager` | Pagination behavior |
| `bleet-password` | Password visibility toggle |
| `bleet-profile` | Profile widget behavior |
| `bleet-tabs` | Tab switching |
| `bleet-upload` | File upload (Resumable.js) |

## Let's be honest

**This is an admin UIKit, not a design system.**

Bleet is built for Blackcube's back-office. It generates consistent, functional HTML. It is not a general-purpose component library.

**Aurelia 2 is required for interactivity.**

Modals, drawers, toasts, uploads, rich text — all need the Aurelia 2 companion package. Static widgets (buttons, cards, typography) work without it.

**Tight coupling with Yii.**

Widgets use `FormModelInterface`, `UrlGeneratorInterface`, `Html` from Yii. This is not framework-agnostic.

## License

BSD-3-Clause. See [LICENSE.md](LICENSE.md).

Copyright (c) 2026 Blackcube — Philippe Gaultier.

## Author

Philippe Gaultier <philippe@blackcube.io>
