<?php

declare(strict_types=1);

namespace Blackcube\Bleet\Tests\Model;

use Blackcube\Bleet\Tests\Support\ModelTester;
use Blackcube\Bleet\Widgets\Alert;
use Blackcube\Bleet\Widgets\Blockquote;
use Blackcube\Bleet\Widgets\Card;
use Blackcube\Bleet\Widgets\Figure;
use Blackcube\Bleet\Widgets\Pre;

class SlotCaptureCest
{
    // ========== STRING MODE ==========

    public function testCardContentStringMode(ModelTester $I): void
    {
        $I->wantTo('verify Card content accepts string');

        $card = new Card();
        $card = $card->content('<p>Hello</p>')->encode(false);

        $html = $card->render();

        $I->assertStringContainsString('<p>Hello</p>', $html);
    }

    public function testAlertContentStringMode(ModelTester $I): void
    {
        $I->wantTo('verify Alert content accepts string');

        $alert = new Alert();
        $alert = $alert->content('Alert message');

        $html = $alert->render();

        $I->assertStringContainsString('Alert message', $html);
    }

    public function testBlockquoteContentStringMode(ModelTester $I): void
    {
        $I->wantTo('verify Blockquote content accepts string');

        $blockquote = new Blockquote('Citation text');

        $html = $blockquote->render();

        $I->assertStringContainsString('Citation text', $html);
        $I->assertStringContainsString('<blockquote', $html);
    }

    // ========== CLOSURE MODE ==========

    public function testCardContentClosureMode(ModelTester $I): void
    {
        $I->wantTo('verify Card content accepts closure with return');

        $card = new Card();
        $card = $card->content(fn() => '<p>From closure</p>');

        $html = $card->render();

        $I->assertStringContainsString('<p>From closure</p>', $html);
    }

    public function testCardContentClosureWithEchoMode(ModelTester $I): void
    {
        $I->wantTo('verify Card content accepts closure with echo');

        $card = new Card();
        $card = $card->content(function () {
            echo '<p>Echoed content</p>';
        });

        $html = $card->render();

        $I->assertStringContainsString('<p>Echoed content</p>', $html);
    }

    // Modal has been simplified for AJAX pattern - no content() method anymore

    // ========== WIDGET MODE ==========

    public function testCardContentWidgetMode(ModelTester $I): void
    {
        $I->wantTo('verify Card content accepts Widget');

        $alert = (new Alert())->content('Nested alert');
        $card = new Card();
        $card = $card->content($alert);

        $html = $card->render();

        $I->assertStringContainsString('Nested alert', $html);
        $I->assertStringContainsString('border-l-4', $html); // Alert class
    }

    // Modal has been simplified for AJAX pattern - no content() method anymore

    // ========== CAPTURE MODE (begin/end) ==========

    public function testCardContentCaptureMode(ModelTester $I): void
    {
        $I->wantTo('verify Card content capture with begin/end');

        $card = new Card();
        $card = $card->encode(false)->beginContent();
        echo '<p>Captured content</p>';
        $card = $card->endContent();

        $html = $card->render();

        $I->assertStringContainsString('<p>Captured content</p>', $html);
    }

    public function testAlertContentCaptureMode(ModelTester $I): void
    {
        $I->wantTo('verify Alert content capture with begin/end');

        $alert = new Alert();
        $alert = $alert->beginContent();
        echo 'Captured alert message';
        $alert = $alert->endContent();

        $html = $alert->render();

        $I->assertStringContainsString('Captured alert message', $html);
    }

    public function testBlockquoteContentCaptureMode(ModelTester $I): void
    {
        $I->wantTo('verify Blockquote content capture with begin/end');

        $blockquote = new Blockquote();
        $blockquote = $blockquote->beginContent();
        echo 'Captured citation';
        $blockquote = $blockquote->endContent();

        $html = $blockquote->render();

        $I->assertStringContainsString('Captured citation', $html);
    }

    public function testPreContentCaptureMode(ModelTester $I): void
    {
        $I->wantTo('verify Pre content capture with begin/end');

        $pre = new Pre();
        $pre = $pre->beginContent();
        echo 'function test() {}';
        $pre = $pre->endContent();

        $html = $pre->render();

        $I->assertStringContainsString('function test() {}', $html);
        $I->assertStringContainsString('<code', $html);
    }

    // ========== IMMUTABILITY ==========

    public function testCardContentImmutability(ModelTester $I): void
    {
        $I->wantTo('verify Card content methods are immutable');

        $card1 = new Card();
        $card2 = $card1->content('Content 1');
        $card3 = $card2->content('Content 2');

        $I->assertNotSame($card1, $card2);
        $I->assertNotSame($card2, $card3);

        $html2 = $card2->render();
        $html3 = $card3->render();

        $I->assertStringContainsString('Content 1', $html2);
        $I->assertStringContainsString('Content 2', $html3);
        $I->assertStringNotContainsString('Content 2', $html2);
    }

    public function testAlertContentImmutability(ModelTester $I): void
    {
        $I->wantTo('verify Alert content methods are immutable');

        $alert1 = new Alert();
        $alert2 = $alert1->content('Message 1');
        $alert3 = $alert2->content('Message 2');

        $I->assertNotSame($alert1, $alert2);
        $I->assertNotSame($alert2, $alert3);

        $html2 = $alert2->render();
        $html3 = $alert3->render();

        $I->assertStringContainsString('Message 1', $html2);
        $I->assertStringContainsString('Message 2', $html3);
    }

    public function testCaptureImmutability(ModelTester $I): void
    {
        $I->wantTo('verify begin/end capture methods are immutable');

        $card1 = new Card();
        $card2 = $card1->beginContent();
        echo 'Captured';
        $card3 = $card2->endContent();

        $I->assertNotSame($card1, $card2);
        $I->assertNotSame($card2, $card3);
    }

    // ========== CARD HEADER/FOOTER SLOTS ==========

    public function testCardHeaderSlotString(ModelTester $I): void
    {
        $I->wantTo('verify Card header slot accepts string');

        $card = new Card();
        $card = $card->encode(false)->header('<div class="custom-header">Header</div>');

        $html = $card->render();

        $I->assertStringContainsString('<div class="custom-header">Header</div>', $html);
    }

    public function testCardHeaderSlotWidget(ModelTester $I): void
    {
        $I->wantTo('verify Card header slot accepts Widget');

        $alert = (new Alert())->content('Header alert');
        $card = new Card();
        $card = $card->header($alert);

        $html = $card->render();

        $I->assertStringContainsString('Header alert', $html);
    }

    public function testCardHeaderSlotCapture(ModelTester $I): void
    {
        $I->wantTo('verify Card header capture with begin/end');

        $card = new Card();
        $card = $card->encode(false)->beginHeader();
        echo '<h2>Captured Header</h2>';
        $card = $card->endHeader();

        $html = $card->render();

        $I->assertStringContainsString('<h2>Captured Header</h2>', $html);
    }

    public function testCardFooterSlotString(ModelTester $I): void
    {
        $I->wantTo('verify Card footer slot accepts string');

        $card = new Card('Body');
        $card = $card->footer('Footer text');

        $html = $card->render();

        $I->assertStringContainsString('Footer text', $html);
    }

    public function testCardFooterSlotCapture(ModelTester $I): void
    {
        $I->wantTo('verify Card footer capture with begin/end');

        // Footer uses Closure to avoid being wrapped in <p> tag
        $card = new Card('Body');
        $card = $card->footer(function () {
            return '<span>Captured footer</span>';
        });

        $html = $card->render();

        $I->assertStringContainsString('<span>Captured footer</span>', $html);
    }

    // ========== FIGURE CAPTION SLOT ==========

    public function testFigureCaptionSlotString(ModelTester $I): void
    {
        $I->wantTo('verify Figure caption slot accepts string');

        $figure = new Figure('/img/test.jpg', 'Alt text');
        $figure = $figure->caption('Image caption');

        $html = $figure->render();

        $I->assertStringContainsString('Image caption', $html);
        $I->assertStringContainsString('<figcaption', $html);
    }

    public function testFigureCaptionSlotClosure(ModelTester $I): void
    {
        $I->wantTo('verify Figure caption slot accepts closure');

        $figure = new Figure('/img/test.jpg', 'Alt text');
        $figure = $figure->caption(fn() => '<em>Styled caption</em>');

        $html = $figure->render();

        $I->assertStringContainsString('<em>Styled caption</em>', $html);
    }

    public function testFigureCaptionSlotCapture(ModelTester $I): void
    {
        $I->wantTo('verify Figure caption capture with begin/end');

        $figure = new Figure('/img/test.jpg', 'Alt text');
        $figure = $figure->beginCaption();
        echo '<strong>Captured caption</strong>';
        $figure = $figure->endCaption();

        $html = $figure->render();

        $I->assertStringContainsString('<strong>Captured caption</strong>', $html);
    }

    // Modal/Drawer have been simplified for AJAX pattern - no content capture methods anymore
}
