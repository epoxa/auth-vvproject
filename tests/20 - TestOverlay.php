<?php

use YY\Develop\Tests\AuthTestCase;

require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/../config/config.php';

class TestOverlay extends AuthTestCase
{

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

        $this->url("https://overlay?dummy=check");
        $title = $this->title();
        $this->assertEquals('Overlay', $title);
        $this->assertTextPresent(['dummy', 'check']);

    }

    public function test_overlay()
    {
        $this->url("/");
        $user = $this->quickReg();
        $this->url("http://foreign");
        $this->pressBookmarklet();
        $this->frame('vvframe');
        $this->waitForEngine();
//        $this->assertTextPresent($user['name']);
        $this->assertTextPresent('[mode] => inline');
    }

}

