<?php

declare(strict_types=1);

namespace Blackcube\Bleet\Tests\Model;

use Blackcube\Bleet\Bleet;
use Blackcube\Bleet\Tests\Support\ModelTester;
use Blackcube\Bleet\Widgets\Abbr;
use Blackcube\Bleet\Widgets\Anchor;
use Blackcube\Bleet\Widgets\Badge;
use Blackcube\Bleet\Widgets\Button;
use Blackcube\Bleet\Widgets\Paragraph;

class BleetAttributesCest
{
    // ========== TRAIT API ==========

    public function testAttributeSingle(ModelTester $I): void
    {
        $I->wantTo('verify attribute() sets a single HTML attribute');

        $html = (new Badge('Test'))->attribute('data-x', '1')->render();

        $I->assertStringContainsString('data-x="1"', $html);
    }

    public function testAttributesReplace(ModelTester $I): void
    {
        $I->wantTo('verify attributes() replaces all user attributes');

        $badge = (new Badge('Test'))
            ->attribute('data-a', '1')
            ->attributes(['data-b' => '2']);

        $html = $badge->render();

        $I->assertStringNotContainsString('data-a', $html);
        $I->assertStringContainsString('data-b="2"', $html);
    }

    public function testAddAttributesMerge(ModelTester $I): void
    {
        $I->wantTo('verify addAttributes() merges with existing');

        $badge = (new Badge('Test'))
            ->attribute('data-a', '1')
            ->addAttributes(['data-b' => '2']);

        $html = $badge->render();

        $I->assertStringContainsString('data-a="1"', $html);
        $I->assertStringContainsString('data-b="2"', $html);
    }

    public function testUnionAttributesKeepExisting(ModelTester $I): void
    {
        $I->wantTo('verify unionAttributes() keeps existing values on conflict');

        $badge = (new Badge('Test'))
            ->attribute('data-a', 'original')
            ->unionAttributes(['data-a' => 'new', 'data-b' => '2']);

        $html = $badge->render();

        $I->assertStringContainsString('data-a="original"', $html);
        $I->assertStringContainsString('data-b="2"', $html);
    }

    public function testIdAttribute(ModelTester $I): void
    {
        $I->wantTo('verify id() sets the id attribute');

        $html = (new Badge('Test'))->id('my-badge')->render();

        $I->assertStringContainsString('id="my-badge"', $html);
    }

    public function testAddClassAccumulates(ModelTester $I): void
    {
        $I->wantTo('verify addClass() accumulates with component classes');

        $html = (new Badge('Test'))->addClass('custom-class')->render();

        $I->assertStringContainsString('custom-class', $html);
        // Component classes should still be present
        $I->assertStringContainsString('inline-flex', $html);
    }

    public function testAddStyleInline(ModelTester $I): void
    {
        $I->wantTo('verify addStyle() renders inline CSS');

        $html = (new Badge('Test'))->addStyle('color: red')->render();

        $I->assertStringContainsString('style="color: red', $html);
    }

    public function testRemoveStyle(ModelTester $I): void
    {
        $I->wantTo('verify removeStyle() removes inline CSS');

        $html = (new Badge('Test'))
            ->addStyle(['color' => 'red', 'font-size' => '14px'])
            ->removeStyle('color')
            ->render();

        $I->assertStringNotContainsString('color: red', $html);
        $I->assertStringContainsString('font-size: 14px', $html);
    }

    // ========== IMMUTABILITY ==========

    public function testImmutability(ModelTester $I): void
    {
        $I->wantTo('verify trait methods return new instances (immutability)');

        $original = new Badge('Test');
        $modified = $original->attribute('data-x', '1');

        $I->assertNotSame($original, $modified);
        $I->assertStringNotContainsString('data-x', $original->render());
        $I->assertStringContainsString('data-x="1"', $modified->render());
    }

    // ========== USER OVERRIDE OF WIDGET DEFAULTS ==========

    public function testUserOverridesWidgetDefault(ModelTester $I): void
    {
        $I->wantTo('verify user attribute overrides widget default (Abbr title)');

        $html = (new Abbr('HTML', 'HyperText Markup Language'))
            ->attribute('title', 'Custom Title')
            ->render();

        $I->assertStringContainsString('title="Custom Title"', $html);
        $I->assertStringNotContainsString('HyperText Markup Language', $html);
    }

    public function testUserClassesAccumulateWithComponent(ModelTester $I): void
    {
        $I->wantTo('verify user classes accumulate with component classes on Abbr');

        $html = (new Abbr('CSS', 'Cascading Style Sheets'))
            ->addClass('my-custom')
            ->render();

        // User class present
        $I->assertStringContainsString('my-custom', $html);
        // Component classes still present
        $I->assertStringContainsString('cursor-help', $html);
        $I->assertStringContainsString('border-b', $html);
    }

    // ========== BUTTON AFTER __call REMOVAL ==========

    public function testButtonSubmit(ModelTester $I): void
    {
        $I->wantTo('verify Button submit works after __call removal');

        $html = (new Button('Save'))->submit()->render();

        $I->assertStringContainsString('type="submit"', $html);
        $I->assertStringContainsString('Save', $html);
        $I->assertStringContainsString('<button', $html);
    }

    public function testButtonAttribute(ModelTester $I): void
    {
        $I->wantTo('verify Button accepts custom attributes');

        $html = (new Button('Delete'))
            ->danger()
            ->attribute('data-confirm', 'Are you sure?')
            ->render();

        $I->assertStringContainsString('data-confirm="Are you sure?"', $html);
    }

    public function testButtonDisabled(ModelTester $I): void
    {
        $I->wantTo('verify Button disabled works');

        $html = (new Button('Save'))->disabled()->render();

        $I->assertStringContainsString('disabled', $html);
    }

    public function testButtonIcon(ModelTester $I): void
    {
        $I->wantTo('verify Button with icon renders correctly');

        $html = (new Button('Save'))->icon('check')->render();

        $I->assertStringContainsString('<svg', $html);
        $I->assertStringContainsString('Save', $html);
    }

    // ========== ANCHOR AFTER __call REMOVAL ==========

    public function testAnchorUrl(ModelTester $I): void
    {
        $I->wantTo('verify Anchor url() works after __call removal');

        $html = (new Anchor('Link', '/page'))->render();

        $I->assertStringContainsString('href="/page"', $html);
        $I->assertStringContainsString('Link', $html);
        $I->assertStringContainsString('<a', $html);
    }

    public function testAnchorExternal(ModelTester $I): void
    {
        $I->wantTo('verify Anchor external() sets target and rel');

        $html = (new Anchor('Ext', 'https://example.com'))
            ->external()
            ->render();

        $I->assertStringContainsString('target="_blank"', $html);
        $I->assertStringContainsString('rel="noopener noreferrer"', $html);
    }

    public function testAnchorAttribute(ModelTester $I): void
    {
        $I->wantTo('verify Anchor accepts custom attributes');

        $html = (new Anchor('Link', '/page'))
            ->attribute('data-action', 'navigate')
            ->render();

        $I->assertStringContainsString('data-action="navigate"', $html);
    }

    // ========== PARAGRAPH AFTER __call REMOVAL ==========

    public function testParagraphContentEncode(ModelTester $I): void
    {
        $I->wantTo('verify Paragraph content() and encode() work');

        $html = (new Paragraph())
            ->content('<strong>Bold</strong>')
            ->encode(false)
            ->render();

        $I->assertStringContainsString('<strong>Bold</strong>', $html);
        $I->assertStringContainsString('<p', $html);
    }

    public function testParagraphAttribute(ModelTester $I): void
    {
        $I->wantTo('verify Paragraph accepts custom attributes');

        $html = (new Paragraph())
            ->content('Hello')
            ->attribute('data-section', 'intro')
            ->render();

        $I->assertStringContainsString('data-section="intro"', $html);
    }

    // ========== BLEET FACADE ==========

    public function testBleetFacadeBadge(ModelTester $I): void
    {
        $I->wantTo('verify Bleet facade works with trait methods');

        $html = Bleet::badge('Status')
            ->dot()
            ->success()
            ->attribute('data-status', 'active')
            ->addClass('ml-2')
            ->render();

        $I->assertStringContainsString('data-status="active"', $html);
        $I->assertStringContainsString('ml-2', $html);
    }

    public function testBleetFacadeButton(ModelTester $I): void
    {
        $I->wantTo('verify Bleet::button works with trait methods');

        $html = Bleet::button('OK')
            ->submit()
            ->primary()
            ->attribute('data-loading', 'true')
            ->render();

        $I->assertStringContainsString('type="submit"', $html);
        $I->assertStringContainsString('data-loading="true"', $html);
    }

    public function testBleetFacadeAnchor(ModelTester $I): void
    {
        $I->wantTo('verify Bleet::a works with trait methods');

        $html = Bleet::a('Click', '/path')
            ->addClass('nav-link')
            ->render();

        $I->assertStringContainsString('href="/path"', $html);
        $I->assertStringContainsString('nav-link', $html);
    }
}
