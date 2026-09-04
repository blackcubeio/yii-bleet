<?php

declare(strict_types=1);

/**
 * Bleet.php
 *
 * PHP Version 8.4
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Bleet;

use Blackcube\Bleet\Widgets\Abbr;
use Blackcube\Bleet\Widgets\ActivityFeed;
use Blackcube\Bleet\Widgets\ActivityItem;
use Blackcube\Bleet\Widgets\Ajaxify;
use Blackcube\Bleet\Widgets\Alert;
use Blackcube\Bleet\Widgets\Anchor;
use Blackcube\Bleet\Widgets\Badge;
use Blackcube\Bleet\Widgets\BleetResetButton;
use Blackcube\Bleet\Widgets\BleetSubmitButton;
use Blackcube\Bleet\Widgets\BleetButton;
use Blackcube\Bleet\Widgets\Blockquote;
use Blackcube\Bleet\Widgets\Breadcrumb;
use Blackcube\Bleet\Widgets\Button;
use Blackcube\Bleet\Widgets\ButtonGroup;
use Blackcube\Bleet\Widgets\Card;
use Blackcube\Bleet\Widgets\CardHeader;
use Blackcube\Bleet\Widgets\Checkbox;
use Blackcube\Bleet\Widgets\CheckboxList;
use Blackcube\Bleet\Widgets\Code;
use Blackcube\Bleet\Widgets\Del;
use Blackcube\Bleet\Widgets\DescriptionList;
use Blackcube\Bleet\Widgets\DetailItem;
use Blackcube\Bleet\Widgets\Drawer;
use Blackcube\Bleet\Widgets\Em;
use Blackcube\Bleet\Widgets\EmptyState;
use Blackcube\Bleet\Widgets\Figure;
use Blackcube\Bleet\Widgets\Footer;
use Blackcube\Bleet\Widgets\H1;
use Blackcube\Bleet\Widgets\H2;
use Blackcube\Bleet\Widgets\H3;
use Blackcube\Bleet\Widgets\H4;
use Blackcube\Bleet\Widgets\H5;
use Blackcube\Bleet\Widgets\H6;
use Blackcube\Bleet\Widgets\Header;
use Blackcube\Bleet\Widgets\Hr;
use Blackcube\Bleet\Widgets\Img;
use Blackcube\Bleet\Widgets\Input;
use Blackcube\Bleet\Widgets\Ins;
use Blackcube\Bleet\Widgets\Label;
use Blackcube\Bleet\Widgets\ListItem;
use Blackcube\Bleet\Widgets\Mark;
use Blackcube\Bleet\Widgets\Modal;
use Blackcube\Bleet\Widgets\OrderedList;
use Blackcube\Bleet\Widgets\Pager;
use Blackcube\Bleet\Widgets\Paragraph;
use Blackcube\Bleet\Widgets\Pre;
use Blackcube\Bleet\Widgets\Profile;
use Blackcube\Bleet\Widgets\Progress;
use Blackcube\Bleet\Widgets\Radio;
use Blackcube\Bleet\Widgets\RadioList;
use Blackcube\Bleet\Widgets\Select;
use Blackcube\Bleet\Widgets\ShortcutCard;
use Blackcube\Bleet\Widgets\Sidebar;
use Blackcube\Bleet\Widgets\SidebarItem;
use Blackcube\Bleet\Widgets\Small;
use Blackcube\Bleet\Widgets\StatCard;
use Blackcube\Bleet\Widgets\Step;
use Blackcube\Bleet\Widgets\Strong;
use Blackcube\Icons\Svg;
use Blackcube\Bleet\Widgets\TermItem;
use Blackcube\Bleet\Widgets\Textarea;
use Blackcube\Bleet\Widgets\Toast;
use Blackcube\Bleet\Widgets\Toaster;
use Blackcube\Bleet\Widgets\Toggle;
use Blackcube\Bleet\Widgets\UnorderedList;
use Blackcube\Bleet\Widgets\Elastic;
use Blackcube\Bleet\Widgets\Upload;
use Blackcube\Bleet\Traits\BleetAureliaTrait;
use Yiisoft\Data\Paginator\PaginatorInterface;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\FormModel\FormModelInputData;
use Yiisoft\FormModel\FormModelInterface;

/**
 * Bleet
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class Bleet
{
    use BleetAureliaTrait;
    /**
     * Default configuration for Upload widget
     */
    private static ?UploadConfig $uploadConfig = null;

    /**
     * Sets default configuration for Upload widget
     */
    public static function setUploadConfig(UploadConfig $config): void
    {
        self::$uploadConfig = $config;
    }

    /**
     * Returns Upload config (creates default instance if not defined)
     */
    public static function getUploadConfig(): UploadConfig
    {
        return self::$uploadConfig ?? new UploadConfig();
    }

    public const COLOR_PRIMARY = 'primary';
    public const COLOR_SECONDARY = 'secondary';
    public const COLOR_SUCCESS = 'success';
    public const COLOR_DANGER = 'danger';
    public const COLOR_WARNING = 'warning';
    public const COLOR_INFO = 'info';
    public const COLOR_ACCENT = 'accent';

    public const COLORS = [
        self::COLOR_PRIMARY,
        self::COLOR_SECONDARY,
        self::COLOR_SUCCESS,
        self::COLOR_DANGER,
        self::COLOR_WARNING,
        self::COLOR_INFO,
        self::COLOR_ACCENT,
    ];

    public const SIZE_XS = 'xs';
    public const SIZE_SM = 'sm';
    public const SIZE_MD = 'md';
    public const SIZE_LG = 'lg';
    public const SIZE_XL = 'xl';

    public const SIZES = [
        self::SIZE_XS,
        self::SIZE_SM,
        self::SIZE_MD,
        self::SIZE_LG,
        self::SIZE_XL,
    ];

    /**
     * Creates a Button
     */
    public static function button(string $content = '', ?FormModelInterface $model = null, ?string $property = null): BleetButton
    {
        $button = BleetButton::widget()->text($content);

        if ($model !== null && $property !== null) {
            $button = $button->name((new FormModelInputData($model, $property))->getName());
        }

        return $button;
    }

    /**
     * Form submit button.
     */
    public static function submitButton(string $content = '', ?FormModelInterface $model = null, ?string $property = null): BleetSubmitButton
    {
        $button = BleetSubmitButton::widget()->text($content);

        if ($model !== null && $property !== null) {
            $button = $button->name((new FormModelInputData($model, $property))->getName());
        }

        return $button;
    }

    /**
     * Form reset button.
     */
    public static function resetButton(string $content = '', ?FormModelInterface $model = null, ?string $property = null): BleetResetButton
    {
        $button = BleetResetButton::widget()->text($content);

        if ($model !== null && $property !== null) {
            $button = $button->name((new FormModelInputData($model, $property))->getName());
        }

        return $button;
    }

    /**
     * Creates a ButtonGroup (boutons joints en un seul bloc)
     *
     * Usage:
     *   Bleet::buttonGroup()
     *       ->addButton(Bleet::button()->icon('pencil')->info())
     *       ->addButton(Bleet::button()->icon('trash')->danger())
     *       ->render()
     */
    public static function buttonGroup(): ButtonGroup
    {
        return new ButtonGroup();
    }

    /**
     * Creates a badge
     */
    public static function badge(string $content = ''): Badge
    {
        return new Badge($content);
    }

    /**
     * Creates a Label
     */
    public static function label(string $content = '', ?FormModelInterface $model = null, ?string $property = null): Label
    {
        return new Label($content, $model, $property);
    }

    /**
     * Creates a Input
     */
    public static function input(?FormModelInterface $model = null, ?string $property = null): Input
    {
        $input = new Input();

        if ($model !== null && $property !== null) {
            $input = $input->inputData(new FormModelInputData($model, $property));
        }

        return $input->bleetParts();
    }

    /**
     * Creates a Textarea
     */
    public static function textarea(?FormModelInterface $model = null, ?string $property = null): Textarea
    {
        $textarea = Textarea::widget();

        if ($model !== null && $property !== null) {
            $textarea = $textarea->inputData(new FormModelInputData($model, $property));
        }

        return $textarea->bleetParts();
    }

    /**
     * Creates a Checkbox
     */
    public static function checkbox(?FormModelInterface $model = null, ?string $property = null): Checkbox
    {
        $checkbox = Checkbox::widget();

        if ($model !== null && $property !== null) {
            $checkbox = $checkbox->inputData(new FormModelInputData($model, $property));
        }

        return $checkbox->bleetParts();
    }

    /**
     * Creates a CheckboxList
     */
    public static function checkboxList(?FormModelInterface $model = null, ?string $property = null): CheckboxList
    {
        $field = CheckboxList::widget();

        if ($model !== null && $property !== null) {
            $field = $field->inputData(new FormModelInputData($model, $property));
        }

        return $field->bleetParts();
    }

    /**
     * Creates a Radio
     */
    public static function radio(): Radio
    {
        return new Radio();
    }

    /**
     * Creates a RadioList
     */
    public static function radioList(?FormModelInterface $model = null, ?string $property = null): RadioList
    {
        $field = RadioList::widget();

        if ($model !== null && $property !== null) {
            $field = $field->inputData(new FormModelInputData($model, $property));
        }

        return $field->bleetParts();
    }

    /**
     * Creates a Toggle
     */
    public static function toggle(?FormModelInterface $model = null, ?string $property = null): Toggle
    {
        $toggle = Toggle::widget();

        if ($model !== null && $property !== null) {
            $toggle = $toggle->inputData(new FormModelInputData($model, $property));
        }

        return $toggle->bleetParts();
    }

    /**
     * Creates a Upload (zone d'upload avec Resumable.js)
     *
     * Default endpoints are defined in UploadConfig:
     *   - /bleet/upload (upload chunks)
     *   - /bleet/preview (preview fichiers)
     *   - /bleet/delete (suppression temporaires)
     *
     * Usage (default config):
     *   Bleet::upload()
     *       ->accept(['image/*', 'application/pdf'])
     *       ->maxSize('10M')
     *       ->maxFiles(5)
     *       ->multiple()
     *       ->name('files')
     *       ->render()
     *
     * Usage (override endpoints):
     *   Bleet::upload()
     *       ->endpoint('/custom/upload')
     *       ->name('files')
     *       ->render()
     *
     * @param UploadConfig|null $config Custom config (uses global config if null)
     */
    public static function upload(?FormModelInterface $model = null, ?string $property = null, ?UploadConfig $config = null): Upload
    {
        $uploadConfig = $config;
        if ($uploadConfig === null) {
            $uploadConfig = self::$uploadConfig;
        }

        $upload = new Upload($uploadConfig);

        if ($model !== null && $property !== null) {
            $upload = $upload->inputData(new FormModelInputData($model, $property));
        }

        return $upload->bleetParts();
    }

    /**
     * Creates an Elastic field (auto-renders based on JSON Schema meta)
     *
     * Usage:
     *   Bleet::elastic($model, $attribute, $elasticOptions)->render()
     *
     * @param array $elasticOptions Options (file endpoints, etc.)
     */
    public static function elastic(?FormModelInterface $model = null, ?string $property = null, array $elasticOptions = []): Elastic
    {
        return new Elastic($model, $property, $elasticOptions);
    }

    /**
     * Creates a Sidebar
     */
    public static function sidebar(): Sidebar
    {
        return new Sidebar();
    }

    /**
     * Creates a Sidebar item
     */
    public static function sidebarItem(string $label = ''): SidebarItem
    {
        return new SidebarItem($label);
    }

    /**
     * Creates a Profile widget
     */
    public static function profile(): Profile
    {
        return new Profile();
    }

    /**
     * Creates a Paragraph
     */
    public static function p(string $content = ''): Paragraph
    {
        return Paragraph::widget()->content($content);
    }

    /**
     * Creates an unordered list
     * @param array<string|array|ListItem> $items
     */
    public static function ul(array $items = []): UnorderedList
    {
        return new UnorderedList($items);
    }

    /**
     * Creates an ordered list
     * @param array<string|array|ListItem> $items
     */
    public static function ol(array $items = []): OrderedList
    {
        return new OrderedList($items);
    }

    /**
     * Creates a list item with optional icon
     */
    public static function listItem(string $content = ''): ListItem
    {
        return new ListItem($content);
    }

    /**
     * Creates a definition list
     * @param array<TermItem|array> $items
     */
    public static function dl(array $items = []): DescriptionList
    {
        return new DescriptionList($items);
    }

    /**
     * Creates a term for definition list (dt + dd)
     */
    public static function termItem(string $term = ''): TermItem
    {
        return new TermItem($term);
    }

    /**
     * Creates a detail for definition list (dd)
     */
    public static function detailItem(string $content = ''): DetailItem
    {
        return new DetailItem($content);
    }

    /**
     * Creates a link
     */
    public static function a(string $content = '', ?string $url = null): Anchor
    {
        return new Anchor($content, $url);
    }

    /**
     * Creates highlighted text
     */
    public static function mark(string $content = ''): Mark
    {
        return new Mark($content);
    }

    /**
     * Creates a small text
     */
    public static function small(string $content = ''): Small
    {
        return new Small($content);
    }

    /**
     * Creates deleted text
     */
    public static function del(string $content = ''): Del
    {
        return new Del($content);
    }

    /**
     * Creates inserted text
     */
    public static function ins(string $content = ''): Ins
    {
        return new Ins($content);
    }

    /**
     * Creates bold text
     */
    public static function strong(string $content = ''): Strong
    {
        return new Strong($content);
    }

    /**
     * Creates italic text
     */
    public static function em(string $content = ''): Em
    {
        return new Em($content);
    }

    /**
     * Creates a blockquote
     */
    public static function blockquote(string $content = ''): Blockquote
    {
        return new Blockquote($content);
    }

    /**
     * Creates an abbreviation
     */
    public static function abbr(string $content, string $title): Abbr
    {
        return new Abbr($content, $title);
    }

    /**
     * Creates inline code
     */
    public static function code(string $content = ''): Code
    {
        return new Code($content);
    }

    /**
     * Creates a code block
     */
    public static function pre(string $content = ''): Pre
    {
        return new Pre($content);
    }

    /**
     * Creates a horizontal separator
     */
    public static function hr(?string $text = null): Hr
    {
        return new Hr($text);
    }

    /**
     * Creates an image
     */
    public static function img(string $src = '', string $alt = ''): Img
    {
        return new Img($src, $alt);
    }

    /**
     * Creates a figure (image with optional caption)
     */
    public static function figure(string $src = '', string $alt = ''): Figure
    {
        return new Figure($src, $alt);
    }

    /**
     * Creates a card (container with optional header/footer)
     */
    public static function card(string $content = ''): Card
    {
        return new Card($content);
    }

    /**
     * Creates a card header (colored header with icon, title, badges and optional button)
     *
     * Usage:
     *   Bleet::cardHeader()->title('Rubriques')->icon('document-text')->primary()->render()
     *   Bleet::cardHeader()->icon('folder')->title('Contenus')->badges([...])->primary()->render()
     */
    public static function cardHeader(): CardHeader
    {
        return new CardHeader();
    }

    /**
     * Creates an empty state (placeholder for empty content with CTA)
     */
    public static function emptyState(): EmptyState
    {
        return new EmptyState();
    }

    /**
     * Creates a progress bar
     */
    public static function progress(int $percent = 0): Progress
    {
        return new Progress($percent);
    }

    /**
     * Creates a stat card (statistic card for a dashboard)
     *
     * Usage:
     *   Bleet::statCard('Visiteurs', '12,458')->icon('users')->render()
     *   Bleet::statCard('Commandes', '284')->icon('shopping-bag')->trend('+12.5%', 'vs mois dernier')->trendUp()->render()
     */
    public static function statCard(string $label = '', string $value = ''): StatCard
    {
        return new StatCard($label, $value);
    }

    /**
     * Creates an activity feed (activity timeline)
     *
     * Usage:
     *   Bleet::activityFeed()
     *       ->title('Recent activity')
     *       ->addItem(Bleet::activityItem('Order validated')->icon('check')->success())
     *       ->render()
     */
    public static function activityFeed(): ActivityFeed
    {
        return new ActivityFeed();
    }

    /**
     * Creates an activity item (for ActivityFeed)
     *
     * Usage:
     *   Bleet::activityItem('Order #1234 validated')->icon('check')->success()->timestamp('2 minutes ago')
     */
    public static function activityItem(string $content = ''): ActivityItem
    {
        return new ActivityItem($content);
    }

    /**
     * Creates a shortcut card (clickable shortcut card)
     *
     * Usage:
     *   Bleet::shortcutCard('Nouveau contenu', '/content/new')->icon('plus')->primary()->render()
     */
    public static function shortcutCard(string $label = '', string $url = '#'): ShortcutCard
    {
        return new ShortcutCard($label, $url);
    }

    /**
     * Creates a Heroicon SVG
     */
    public static function svg(): Svg
    {
        return Svg::heroicon();
    }

    /**
     * Creates a custom SVG (ui, logo)
     */
    public static function icon(): Svg
    {
        return Svg::icon();
    }

    /**
     * Creates an H1 title (page header with gradient)
     */
    public static function h1(string $title = ''): H1
    {
        return new H1($title);
    }

    /**
     * Creates an H2 title (section header with colored background)
     */
    public static function h2(string $title = ''): H2
    {
        return new H2($title);
    }

    /**
     * Creates an H3 title (section header with border)
     */
    public static function h3(string $title = ''): H3
    {
        return new H3($title);
    }

    /**
     * Creates an H4 title (sub-section header with border)
     */
    public static function h4(string $title = ''): H4
    {
        return new H4($title);
    }

    /**
     * Creates an H5 title (minor section header)
     */
    public static function h5(string $title = ''): H5
    {
        return new H5($title);
    }

    /**
     * Creates an H6 title (smallest section header)
     */
    public static function h6(string $title = ''): H6
    {
        return new H6($title);
    }

    /**
     * Creates a breadcrumb
     */
    public static function breadcrumb(): Breadcrumb
    {
        return new Breadcrumb();
    }

    /**
     * Creates a stepper (progress steps)
     */
    public static function step(): Step
    {
        return new Step();
    }

    /**
     * Creates a pagination widget
     *
     * Usage:
     *   Bleet::pagination($paginator, $urlGenerator)
     *       ->maxButtonCount(10)
     *       ->showInfo()
     *       ->primary()
     *       ->render()
     */
    public static function pagination(PaginatorInterface $paginator, UrlGeneratorInterface $urlGenerator): Pager
    {
        return new Pager($paginator, $urlGenerator);
    }

    /**
     * Creates a sticky header
     */
    public static function header(string $title = 'Bleet'): Header
    {
        return (new Header())->title($title);
    }

    /**
     * Creates a footer
     */
    public static function footer(): Footer
    {
        return new Footer();
    }

    /**
     * Creates a Toaster widget (toast container)
     *
     * Usage:
     *   Bleet::toaster()->render()
     *
     * Pour usage direct Aurelia (rare):
     *   Aurelia::toaster($options)
     */
    public static function toaster(): Toaster
    {
        return new Toaster();
    }

    /**
     * Creates a Toast widget (triggers a toast)
     *
     * Usage - toast on page load:
     *   Bleet::toast()->success()->title('Success')->content('Action successful')->render()
     *
     * Usage - attribute on a button:
     *   Bleet::button('Save')->attributes(Bleet::toast()->success()->content('Saved!')->toAttribute())
     *
     * Pour usage direct Aurelia (rare):
     *   Aurelia::toast($options)
     */
    public static function toast(): Toast
    {
        return new Toast();
    }

    /**
     * Creates an Alert widget (alert message)
     *
     * Usage:
     *   Bleet::alert()->content('Simpthe message')->render()
     *   Bleet::alert()->title('Attention')->content('Detailed message')->warning()->render()
     *   Bleet::alert()->content('Fermable')->dismissible()->render()
     */
    public static function alert(): Alert
    {
        return new Alert();
    }

    /**
     * Creates a Modal widget (centered modal dialog, AJAX version)
     *
     * Usage:
     *   // Trigger with URL
     *   Bleet::button('Modifier')->attributes(Bleet::modal('editUser')->trigger('/api/user/1'))->render()
     *
     *   // Shell (once in layout)
     *   Bleet::modal('editUser')->render()
     */
    public static function modal(string $id = 'modal'): Modal
    {
        return new Modal($id);
    }

    /**
     * Creates a Drawer widget (side panel, AJAX version)
     *
     * Usage:
     *   // Trigger with URL (uses default id 'drawer')
     *   Bleet::button('Details')->attributes(Bleet::drawer()->trigger('/api/user/1'))->render()
     *
     *   // Shell (once in layout)
     *   Bleet::drawer()->render()
     */
    public static function drawer(string $id = 'drawer'): Drawer
    {
        return new Drawer($id);
    }

    /**
     * Creates an Ajaxify helper
     *
     * Usage:
     *   // Trigger attribute
     *   Bleet::toggle()->attributes(Bleet::ajaxify()->event('change')->trigger())->render()
     *
     *   // Component
     *   Bleet::ajaxify('myZone')->url('/api/content')->open()
     *   Bleet::ajaxify()->close()
     */
    public static function ajaxify(string $id = ''): Ajaxify
    {
        return new Ajaxify($id);
    }

    /**
     * Creates a simple select with custom display
     */
    public static function select(?FormModelInterface $model = null, ?string $property = null): Select
    {
        $select = Select::widget();

        if ($model !== null && $property !== null) {
            $select = $select->inputData(new FormModelInputData($model, $property));
        }

        return $select->bleetParts();
    }

}