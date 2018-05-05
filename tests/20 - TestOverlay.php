<?php

use YY\Develop\Tests\AuthTestCase;

require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/../config/config.php';

class TestOverlay extends AuthTestCase
{

    private function setOverlayUrl($url) {
        $_SERVER['ENV']['LINKS']['OVERLAY'] = $url;
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
        $this->assertEquals($reg['public_key'], $user['public_key']);
        $this->assertEquals('en', $user['language']);
        $this->assertEquals('0', $user['age']);
        $this->assertEquals('1', $user['active_days']);
        $this->assertEquals("http://foreign/", $user['page_url']); // Notice trailing slash emerged
        $this->assertEquals("Foreign site", $user['page_title']);
    }

    public function test_csp()
    {
        $this->setOverlayUrl("https://overlay?PHPUNIT_SELENIUM_TEST_ID=" . $this->getTestId());
        $this->url("/");
        $reg = $this->quickReg();
        $hWin = $this->windowHandle();

        foreach ([
            "http://foreign/unsafe-eval.php" => 'frame',
            "http://foreign/no-eval.php" => $this->getBrowser() == 'chrome' ? 'frame' : 'window', // seems Chrome always allows eval from bookmarklet
            "http://foreign/frame-src.php" => 'frame',
            "http://101.ru/radio/channel/30" => 'window',
        ] as $url => $mode) {
            $this->window($hWin);
            $this->frame(null);
            $this->url($url);
            $foreign_title = $this->title();
            $this->pressBookmarklet();
            if ($mode == 'frame') {
                $this->frame('vvframe');
            } else if ($mode == 'window') {
                sleep(3);
                $this->window(OVERLAY_WINDOW_NAME . '-' . $url);
            } else if ($mode == 'inplace') {
                sleep(4);
            }
//            $wh = $this->windowHandles();
//            foreach ($wh as $idx => $w) {
//                $this->window($w);
//                fwrite(STDERR, "\nWindow $idx:" . $this->exec('return name') . "!\n");
//            }

            $json = $this->byCssSelector('pre.user-info')->text();
            $user = json_decode($json, true);
            $this->assertEquals($reg['name'], $user['name']);
            $this->assertEquals($url, $user['page_url']);
            $this->assertEquals($foreign_title, $user['page_title']);
        }
    }

    public function test_overlay_yandex()
    {
        // Parameters here to include token exchange in code coverage later
        $this->setOverlayUrl("https://overlay?PHPUNIT_SELENIUM_TEST_ID=" . $this->getTestId());
        $this->url("/");
        $reg = $this->quickReg();
        $this->url("http://ya.ru"); // Started with plain HTTP
        $yandex = $this->windowHandle();
        $yaUrl = $this->url();
        $this->pressBookmarklet();
        $this->window(OVERLAY_WINDOW_NAME . '-' . $yaUrl);
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

