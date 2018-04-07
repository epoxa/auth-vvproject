<?php

use YY\Develop\Tests\AuthTestCase;

require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/../config/config.php';

class TestOverlay extends AuthTestCase
{

    private function setOverlayUrl($url) {
        $_SERVER['ENV']['YY_OVERLAY_URL'] = $url;
        file_put_contents(CONFIGS_DIR . 'env.php', "<?php\n\n\$_SERVER['ENV'] = " . var_export($_SERVER['ENV'], true) . ";");
    }

    public function test_infrastructure()
    {
        // Foreign site

        $this->url("https://foreign");
        $title = $this->title();
        $this->assertEquals('Foreign site', $title);
        $text = $this->byCssSelector('p')->text();
        $this->assertEquals('This is a test page', $text);
        $this->url("http://foreign");
        $title = $this->title();
        $this->assertEquals('Foreign site', $title);
        $text = $this->byCssSelector('p')->text();
        $this->assertEquals('This is a test page', $text);

        // Overlay

        $this->url("https://overlay/repeater.php?dummy=check");
        $title = $this->title();
        $this->assertEquals('Overlay', $title);
        $this->assertTextPresent(['dummy', 'check']);

    }

    public function test_overlay_repeater()
    {
        $this->setOverlayUrl('https://overlay/repeater.php');
        $this->url("/");
        $user = $this->quickReg();
        $this->url("http://foreign");
        $this->pressBookmarklet();
        $this->frame('vvframe');
        $this->waitForEngine();
        $this->assertTextPresent('[state] =>');
    }

    public function test_overlay()
    {
        // Parameters here to include token exchange in code coverage later
        $this->setOverlayUrl("https://overlay?PHPUNIT_SELENIUM_TEST_ID=" . $this->getTestId());
        $this->url("/");
        $reg = $this->quickReg();
        $this->url("http://foreign");
        $this->pressBookmarklet();
        $this->frame('vvframe');
        $json = $this->byCssSelector('pre.user-info')->text();
        $user = json_decode($json, true);
        $this->assertEquals('public', $user['access_token']);
        $this->assertEquals('public', $user['token_type']);
        $this->assertEquals('public', $user['scope']);
        $this->assertEquals($reg['name'], $user['name']);
        $this->assertEquals('en', $user['language']);
        $this->assertEquals('0', $user['age']);
        $this->assertEquals('1', $user['active_days']);
    }

    public function test_overlay_yandex()
    {
        // Parameters here to include token exchange in code coverage later
        $this->setOverlayUrl("https://overlay?PHPUNIT_SELENIUM_TEST_ID=" . $this->getTestId());
        $this->url("/");
        $reg = $this->quickReg();
        $this->url("http://ya.ru"); // Started with plain HTTP
        $yandex = $this->windowHandle();
        $this->pressBookmarklet();
        $this->window(OVERLAY_WINDOW_NAME);
        $json = $this->byCssSelector('pre.user-info')->text();
        $user = json_decode($json, true);
        $this->assertEquals('public', $user['access_token']);
        $this->assertEquals('public', $user['token_type']);
        $this->assertEquals('public', $user['scope']);
        $this->assertEquals($reg['name'], $user['name']);
        $this->assertEquals('en', $user['language']);
        $this->assertEquals('0', $user['age']);
        $this->assertEquals('1', $user['active_days']);
        $this->assertContains('https://ya.ru', $user['page_url']); // Redirected to HTTPS
        $this->assertContains('Яндекс', $user['page_title']);
        $this->assertNotContains('Вокруг Веба', $user['page_title']);
        $this->assertEquals($user['page_title'], $this->title());
        $this->window($yandex);
        $this->assertContains('Яндекс', $this->title());
        $this->byCssSelector('input.input__input')->value('Вокруг Веба');
        $this->byCssSelector('input.input__input')->submit();
        sleep(3);
        $this->assertContains('Вокруг Веба', $this->title());
        $this->pressBookmarklet();
//        $this->window(OVERLAY_WINDOW_NAME); // Почему-то в этом случае яндекс разрешает открывать фрейм.
        $this->frame('vvframe');
        $this->waitForEngine();
        $this->assertContains('Яндекс', $this->title());
        $this->assertContains('Вокруг Веба', $this->title());
        $json = $this->byCssSelector('pre.user-info')->text();
        $user = json_decode($json, true);
        $this->assertContains('Яндекс', $user['page_title']);
        $this->assertContains('Вокруг Веба', $user['page_title']);
        $this->window($yandex);
        $this->frame();
        $this->assertTextPresent('Игры вокруг веба');
    }

}

