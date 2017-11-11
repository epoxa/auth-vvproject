<?php

use YY\Develop\Tests\AuthTestCase;

require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/../config/config.php';

class TestAuthentication extends AuthTestCase
{

    public function test_install()
    {
        $this->url("/");
        $result = $this->title();
        $this->assertEquals("Authentication", $result);
// assertText | //div[@id='_YY_0']/ul/li/a | Русский
        $result = $this->byXPath("//div[@id='_YY_0']/ul/li/a")->text();
        $this->assertEquals("Русский", $result);
// assertText | //div[@id='_YY_0']/ul/li[2]/a | English
        $result = $this->byXPath("//div[@id='_YY_0']/ul/li[2]/a")->text();
        $this->assertEquals("English", $result);
// assertText | //div[@id='_YY_1']/div[2]/p[2] | You can create new character in three steps:
        $result = $this->byXPath("//div[@id='_YY_1']/div[2]/p[2]")->text();
        $this->assertEquals("You can create new character in three steps:", $result);
// click | link=English |
        $this->byLinkText("English")->click();
// assertText | //div[@id='_YY_1']/div[2]/p[2] | Creating new character as simple as one-two-three:
        $result = $this->byXPath("//div[@id='_YY_1']/div[2]/p[2]")->text();
        $this->assertEquals("Creating new character as simple as one-two-three:", $result);
// click | //a[contains(text(),'Create new character')] |
        $this->byXPath("//a[contains(text(),'Create new character')]")->click();
// assertText | css=div.well.clearfix > span.text-muted | Select language first
        $result = $this->byCssSelector("div.well.clearfix > span.text-muted")->text();
        $this->assertEquals("Select language first", $result);
// assertText | //div[@id='_YY_2']/div/div[3]/span[2]/span | Select nickname first
        $result = $this->byXPath("//div[@id='_YY_2']/div/div[3]/span[2]/span")->text();
        $this->assertEquals("Select nickname first", $result);
// click | link=Done |
        $this->byLinkText("Done")->click();
// storeElementText | css=div.well.clearfix > strong | supposedUserName
        $supposedUserName = $this->byCssSelector("div.well.clearfix > strong")->text();
// click | link=random |
        $this->byLinkText("random")->click();
// storeElementText | css=div.well.clearfix > strong | anotherUserName
        $userName = $this->byCssSelector("div.well.clearfix > strong")->text();
        $this->assertNotEquals($supposedUserName, $userName, 'Supposed user name is not changed after regeneration');
// click | link=Done |
        $this->byLinkText("Done")->click();
// assertText | //div[@id='_YY_2']/div/div[3]/span[2]/span[2] | Your personal access button: (eng translation)
        $result = $this->byXPath("//div[@id='_YY_2']/div/div[3]/span[2]/span[2]")->text();
        $this->assertEquals("Your personal access button is ready:", $result);
// assertText | //button[@type='button'] | English
        $result = $this->byXPath("//button[@type='button']")->text();
        $this->assertEquals("English", $result);
// click | link=English |
        $this->byXPath("//button[@type='button']")->click();
// click | link=Русский |
        $this->byLinkText("Русский")->click();
// assertText | //div[@id='_YY_2']/div/div[3]/span[2]/span[2] | Ваша персональная кнопка доступа:
        $result = $this->byXPath("//div[@id='_YY_2']/div/div[3]/span[2]/span[2]")->text();
        $this->assertEquals("Ваша персональная кнопка доступа готова:", $result);
// storeText | css=a.bm-template | bookmarkletText
        $bookmarkletText = $this->byCssSelector("a.bm-template")->text();
        $this->assertEquals($userName, $bookmarkletText);
// storeAttribute | css=a.bm-template | script
        $script = $this->byCssSelector("a.bm-template")->attribute("href");
// Emulate press bookmarklet
        $script = preg_replace('/^javascript:/','',$script);
        $script = urldecode($script);
        //$this->assertEquals('!', $script);
        $this->exec($script);
        $result = $this->byCssSelector("h1")->text();
        $this->assertEquals("Установка завершена", $result);
        $this->byLinkText('Готово')->click();
// assertText | css=span.label.label-info | Новичок
        $result = $this->byCssSelector("span.label.label-info")->text();
        $this->assertEquals("Новичок", $result);

    }

    public function test_install2()
    {
        $this->url("/");
        $this->quickReg();
        $result = $this->byCssSelector("span.label.label-info")->text();
        $this->assertEquals("Newbie", $result);
        $this->byLinkText("reinstall")->click();
        $this->assertTextNotPresent("Now you can generate another name");
        $this->restartWorld();
        $this->url("/");
        $this->pressBookmarklet();
        $this->byLinkText("Done")->click();
        $this->byLinkText("reinstall")->click();
        $this->assertTextNotPresent("Now you can generate another name");
    }

    public function test_public_client()
    {
        $this->url("http://client");
        $this->assertTextPresent('You are not logged in');
        $this->byLinkText("Log in")->click();
        $result = $this->title();
        $this->assertEquals('Authentication', $result);
        $user = $this->quickReg();
        sleep(1); // Trying to avoid weird error on travis-ci
        $this->assertTextPresent("Hello, $user[name]!");
        $this->assertEquals("http://client/index.php", $this->url());
    }

    public function test_registered_client()
    {
        $db = new PDO(
            $_SERVER['ENV']['YY_AUTH_MYSQL_DATASOURCE'], $_SERVER['ENV']['YY_AUTH_MYSQL_USER'], $_SERVER['ENV']['YY_AUTH_MYSQL_PASSWORD']
        );
        $db->exec("INSERT INTO hosts(NAME) VALUES('client') ON DUPLICATE KEY UPDATE ID = LAST_INSERT_ID(ID)");
        $clientHostId = $db->lastInsertId();
        $db->exec("INSERT IGNORE INTO hosts_registered(NAME, REDIRECT_URI) VALUES ('client', 'http://client/login.php')");

        $this->url("/");
        $user = $this->quickReg();

        $this->url("http://client");
        $this->assertTextPresent('You are not logged in');

        $this->pressBookmarklet();
        $this->assertTextPresent("Hello, $user[name]!");
        $this->assertEquals("http://client/index.php", $this->url());
    }

//    function test_relogin_from_bookmarklet()
//    {
//        // TODO: Не работает релогин под другим именем без выхода, попробовать воспроизвести
//        $this->assertTrue(false);
//    }

}

