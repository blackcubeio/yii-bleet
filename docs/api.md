# API

## Bleet

Static factory for all widgets. Every widget is created through `Bleet::method()`.

### Colors and sizes

7 colors: `primary`, `secondary`, `success`, `danger`, `warning`, `info`, `accent`.

5 sizes: `xs`, `sm`, `md`, `lg`, `xl`.

Every widget extending `AbstractWidget` exposes both color and size shortcuts:

```php
Bleet::button('Save')->primary()->sm()->render();
```

### Widget catalog

#### Layout

| Factory | Widget | Description |
|---------|--------|-------------|
| `Bleet::header()` | Header | Sticky header |
| `Bleet::footer()` | Footer | Page footer |
| `Bleet::sidebar()` | Sidebar | Navigation sidebar |
| `Bleet::sidebarItem()` | SidebarItem | Sidebar entry |
| `Bleet::breadcrumb()` | Breadcrumb | Breadcrumb trail |

#### Forms

| Factory | Widget | Description |
|---------|--------|-------------|
| `Bleet::input()` | Input | Text input |
| `Bleet::textarea()` | Textarea | Multiline input |
| `Bleet::select()` | Select | Native select with custom display |
| `Bleet::dropdown()` | Dropdown | Advanced dropdown (search, multiple, tags) |
| `Bleet::checkbox()` | Checkbox | Single checkbox |
| `Bleet::checkboxList()` | CheckboxList | Checkbox group |
| `Bleet::radio()` | Radio | Single radio |
| `Bleet::radioList()` | RadioList | Radio group |
| `Bleet::toggle()` | Toggle | Toggle switch |
| `Bleet::upload()` | Upload | File upload (Resumable.js) |
| `Bleet::elastic()` | Elastic | Auto-render from JSON Schema |
| `Bleet::label()` | Label | Form label |

#### Feedback / Interactive (Aurelia 2)

| Factory | Widget | Description |
|---------|--------|-------------|
| `Bleet::alert()` | Alert | Alert message (dismissible) |
| `Bleet::toast()` | Toast | Toast notification |
| `Bleet::toaster()` | Toaster | Toast container |
| `Bleet::modal()` | Modal | AJAX modal dialog |
| `Bleet::drawer()` | Drawer | AJAX side panel |
| `Bleet::ajaxify()` | Ajaxify | AJAX zone and triggers |

#### Content

| Factory | Widget | Description |
|---------|--------|-------------|
| `Bleet::card()` | Card | Container card |
| `Bleet::cardHeader()` | CardHeader | Colored card header with icon |
| `Bleet::button()` | Button | Action button |
| `Bleet::buttonsBar()` | ButtonsBar | Grouped action buttons |
| `Bleet::badge()` | Badge | Inline badge |
| `Bleet::statCard()` | StatCard | Dashboard stat card with trend |
| `Bleet::shortcutCard()` | ShortcutCard | Clickable shortcut card |
| `Bleet::activityFeed()` | ActivityFeed | Activity timeline |
| `Bleet::emptyState()` | EmptyState | Placeholder with CTA |
| `Bleet::progress()` | Progress | Progress bar |
| `Bleet::step()` | Step | Stepper / progress steps |
| `Bleet::pagination()` | Pager | Pagination |
| `Bleet::profile()` | Profile | User profile widget |

#### Typography

| Factory | Widget | Description |
|---------|--------|-------------|
| `Bleet::h1()` … `Bleet::h6()` | H1–H6 | Headings |
| `Bleet::hr()` | Hr | Separator with label |
| `Bleet::p()` | Paragraph | Paragraph |
| `Bleet::a()` | Anchor | Link |
| `Bleet::strong()` | Strong | Bold |
| `Bleet::em()` | Em | Italic |
| `Bleet::code()` | Code | Inline code |
| `Bleet::pre()` | Pre | Code block |
| `Bleet::blockquote()` | Blockquote | Blockquote |
| `Bleet::mark()` | Mark | Highlight |
| `Bleet::small()` | Small | Small text |
| `Bleet::del()` / `Bleet::ins()` | Del / Ins | Deleted / inserted text |
| `Bleet::abbr()` | Abbr | Abbreviation |
| `Bleet::ul()` / `Bleet::ol()` / `Bleet::dl()` | Lists | Unordered, ordered, description lists |
| `Bleet::img()` | Img | Image |
| `Bleet::figure()` | Figure | Image with caption |
| `Bleet::svg()` / `Bleet::icon()` | Svg | Heroicons / custom SVG |

### Aurelia attribute helpers

Static methods on `Bleet` that return attribute arrays for Aurelia 2 custom attributes:

| Method | Description |
|--------|-------------|
| `Bleet::overlay()` | Backdrop for modals/drawers (once per page) |
| `Bleet::tabs()` | Tab switching |
| `Bleet::pager()` | Pagination behavior |
| `Bleet::burger()` | Burger menu toggle |
| `Bleet::menu()` | Menu behavior |

## Aurelia

Helper for generating Aurelia 2 attribute strings.

| Method | Returns | Description |
|--------|---------|-------------|
| `attributesCustomAttribute(array $options)` | `string` | Generates Aurelia custom attribute binding string |
| `attributesCustomElement(array $options)` | `array` | Generates attribute array for custom elements |

Keys in `$options` are converted from camelCase to kebab-case. Binding commands (`bind`, `one-way`, `two-way`, `one-time`, `trigger`, `capture`, `ref`, `attr`) are detected from key suffixes.

```php
Aurelia::attributesCustomAttribute(['id' => 'myModal', 'url' => '/api/user/1']);
// → "id: myModal; url: /api/user/1;"
```

## AbstractWidget

Base class for all Bleet widgets. Extends `Yiisoft\Widget\Widget`.

| Method | Description |
|--------|-------------|
| `color(string $color)` | Set color (`primary`, `secondary`, …) |
| `size(string $size)` | Set size (`xs`, `sm`, `md`, `lg`, `xl`) |
| `primary()` … `accent()` | Color shortcuts |
| `xs()` … `xl()` | Size shortcuts |

## HTML attributes

All widgets extending `AbstractWidget` use `BleetAttributesTrait` for homogeneous HTML attribute management. The API mirrors `Yiisoft\Html\Tag\Base\Tag`.

### Element attributes (`BleetAttributesTrait`)

Attributes target the **main HTML element** of the widget (`<button>`, `<input>`, `<abbr>`, etc.).

| Method | Description |
|--------|-------------|
| `attribute(string $name, mixed $value)` | Set a single attribute |
| `attributes(array $attributes)` | Replace all user attributes |
| `addAttributes(array $attributes)` | Merge into existing (new values win) |
| `unionAttributes(array $attributes)` | Merge into existing (existing values win) |
| `id(?string $id)` | Set the ID |
| `addClass(BackedEnum\|string\|null ...$class)` | Add CSS classes |
| `class(BackedEnum\|string\|null ...$class)` | Replace CSS classes |
| `addStyle(array\|string $style, bool $overwrite = true)` | Add inline CSS |
| `removeStyle(string\|array $properties)` | Remove inline CSS |

```php
Bleet::abbr('HTML', 'HyperText Markup Language')
    ->attribute('data-tooltip', 'true')
    ->addClass('my-custom-class')
    ->render();

Bleet::button('Save')
    ->submit()
    ->addAttributes(['data-confirm' => 'Are you sure?', 'data-loading' => 'true'])
    ->render();
```

User-defined attributes override widget defaults. For example, `->attribute('title', 'Custom')` on an `Abbr` widget overrides the constructor title. CSS classes always accumulate (user classes + component classes).

### Wrapper attributes (`BleetWrapperAttributesTrait`)

Form widgets with wrapper elements (Input in icon/floating mode, Textarea in floating mode, Select, Toggle) also expose wrapper-level attributes with the same API prefixed `wrapper`:

| Method | Target |
|--------|--------|
| `wrapperAttribute(string $name, mixed $value)` | Wrapper element |
| `wrapperAttributes(array $attributes)` | Wrapper element (replace) |
| `wrapperAddAttributes(array $attributes)` | Wrapper element (merge) |
| `wrapperUnionAttributes(array $attributes)` | Wrapper element (union) |
| `wrapperId(?string $id)` | Wrapper element |
| `wrapperAddClass(BackedEnum\|string\|null ...$class)` | Wrapper element |
| `wrapperClass(BackedEnum\|string\|null ...$class)` | Wrapper element (replace) |
| `wrapperAddStyle(array\|string $style, bool $overwrite = true)` | Wrapper element |
| `wrapperRemoveStyle(string\|array $properties)` | Wrapper element |

```php
Bleet::select()
    ->active($model, 'status')
    ->options($statusOptions)
    ->wrapperAddClass('flex-1')       // on the wrapper <div>
    ->attribute('data-field', 'status') // on the <select>
    ->render();

Bleet::input()
    ->active($model, 'email')
    ->icon('envelope')
    ->wrapperAddClass('mt-4')         // on the grid wrapper
    ->render();
```

## Contracts

### WidgetInterface

```php
interface WidgetInterface
{
    public function render(): string;
}
```

## Enums

| Enum | Values | Description |
|------|--------|-------------|
| `UiColor` | `Primary`, `Secondary`, `Success`, `Danger`, `Warning`, `Info`, `Accent` | UI color palette |
| `UiIcon` | `Info`, `Success`, `Warning`, `Danger` | Mapped Heroicon names |
| `AjaxifyAction` | `Refresh` | Secondary ajaxify action |
| `DialogAction` | `Keep`, `Close`, `RefreshAndClose` | Primary dialog action |

## Traits

### BleetAttributesTrait

HTML attribute management for the main element. See [HTML attributes](#html-attributes) for the full method list.

### BleetWrapperAttributesTrait

HTML attribute management for wrapper elements on form widgets. See [Wrapper attributes](#wrapper-attributes-bleetwrapperattributestrait) for the full method list.

### BleetModelAwareTrait

Model binding for form widgets. Provides `active($model, $property)` to bind a widget to a `FormModelInterface` property.

| Method | Description |
|--------|-------------|
| `active($model, $property)` | Bind to a form model property |

Resolves from the model: value, label, hint, placeholder, errors, required state, and HTML input attributes (from validation rules).

### BleetColorTrait

Normalized color CSS class generation. Provides `protected` methods for text, border, focus ring, background, and icon container color classes.

Auto-switches to `danger` color when the model has validation errors.

### BleetFieldDataTrait

Adds `data-*` attributes on field elements.

| Method | Description |
|--------|-------------|
| `fieldData(array $data)` | Set data attributes (without `data-` prefix) |

### BleetExportableTrait

Widgets that can be exported to arrays (for Aurelia communication).

| Method | Description |
|--------|-------------|
| `asArray()` | Returns widget data as array |

### SlotCaptureTrait

Content capture for widgets with slots (header, footer, content, caption). Supports 4 modes:

| Mode | Example |
|------|---------|
| String | `->content('<p>Hello</p>')` |
| Widget | `->content($alert)` |
| Closure | `->content(fn() => '<p>Dynamic</p>')` |
| Capture | `->beginContent()` / `->endContent()` |

## Helpers

### ActiveHelper

Input name and ID generation following Yii2 tabular input patterns.

| Method | Description |
|--------|-------------|
| `getInputName($model, $attribute)` | Generate input name (e.g. `User[name]`) |
| `getInputId($model, $attribute)` | Generate input ID (e.g. `user-name`) |
| `nameToId(string $name)` | Convert input name to HTML ID |
| `getAttributeName(string $attribute)` | Extract attribute name from tabular expression |

### AureliaCommunication

Builds response data arrays for Aurelia frontend communication (toasts, ajaxify, dialog actions).

| Method | Description |
|--------|-------------|
| `toast($title, $content, $color, $duration)` | Toast notification data |
| `ajaxify($id, $url, $action)` | Ajaxify refresh data |
| `dialog($action)` | Dialog action data (keep/close/refreshAndClose) |
| `dialogContent($header, $content, $color)` | Dialog content data |

