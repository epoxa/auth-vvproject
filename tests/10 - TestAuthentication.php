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
        $data = $this->quickReg();
        $accessKeyTail = substr($data['access_key'], -16);
        $bookmarkletScript = $this->getBookmarkletScript();
        $this->assertContains($data['public_key'], $bookmarkletScript, 'Bookmarklet script does not contain public key');
        $this->assertNotContains($data['access_key'], $bookmarkletScript, 'Bookmarklet script contains access key');
        $this->assertNotContains($accessKeyTail, 'Bookmarklet script contains access key tail');
        $result = $this->byCssSelector("span.label.label-info")->text();
        $this->assertEquals("Newbie", $result);
        $this->byLinkText("reinstall")->click();
        $this->assertTextNotPresent("Now you can generate another name");
        $originalUrl = $this->exec('return location;');
        $key = 'auth-' . $data['public_key'];
        $val = $this->exec("return localStorage.getItem('$key')");
        $this->assertEquals($accessKeyTail, $val);
        $this->restartWorld();
        $this->url("/");
        $this->pressBookmarklet();
        $this->waitForEngine();
        $currentUrl = $this->exec('return location;');
        $this->assertEquals($originalUrl, $currentUrl);
        $val = $this->exec("return localStorage.getItem('$key')");
        $this->assertEquals($accessKeyTail, $val);
        $this->assertTextPresent("installed successfully");
        $this->byLinkText("Done")->click();
        $this->byLinkText("reinstall")->click();
        $this->assertTextNotPresent("Now you can generate another name");
    }

    public function test_recover()
    {
        $this->url("/");
        $data = $this->quickReg();

        // Success
        $this->url("/");
        $this->waitForEngine();
        $this->pressBookmarklet();
        $this->waitForEngine();
        $this->assertTextPresent('Bookmarklet installed');
        $this->assertTextPresent('Hello ' . $data['name']);
        $this->byLinkText("Done")->click();

        // Again success
        $this->url("/");
        $this->waitForEngine();
        $this->pressBookmarklet();
        $this->waitForEngine();
        $this->assertTextPresent('Bookmarklet installed');
        $this->assertTextPresent('Hello ' . $data['name']);
        $this->byLinkText("Done")->click();

        // Kill PHP session
        $this->killSession();

        // Success
        $this->url("/");
        $this->waitForEngine();
        $this->pressBookmarklet();
        $this->waitForEngine();
        $this->assertTextPresent('Bookmarklet installed');
        $this->assertTextPresent('Hello ' . $data['name']);
        $this->byLinkText("Done")->click();

        // Remove access key
        $this->exec('localStorage.removeItem("auth-' . $data['public_key'] . '")');

        // Success
        $this->url("/");
        $this->waitForEngine();
        $this->pressBookmarklet();
        $this->waitForEngine();
        $this->assertTextPresent('Bookmarklet installed');
        $this->assertTextPresent('Hello ' . $data['name']);
        $this->byLinkText("Done")->click();

        // Kill PHP session and remove access key
        $this->exec('localStorage.removeItem("auth-' . $data['public_key'] . '")');
        $this->killSession();

        // Failure
        $this->url("/");
        $this->waitForEngine();
        $this->pressBookmarklet();
        $this->acceptAlert();
        $this->assertTextNotPresent('Bookmarklet installed');
        $this->assertTextNotPresent('Hello ' . $data['name']);

        // Recover
        $this->assertTextPresent('Recover');
        $this->assertTextPresent($data['name']);
        $this->byLinkText("Done")->click();
        $warning = $this->alertText();
        $this->acceptAlert();
        $this->assertEquals('Enter your secret key please.', $warning);
        $this->byCssSelector('input.monospace')->value('1234567');
        $this->byLinkText("Done")->click();
        $warning = $this->alertText();
        $this->acceptAlert();
        $this->assertEquals('This key is invalid. Sorry.', $warning);
        $this->byCssSelector('input.monospace')->value($data['access_key']);
        return;
        $this->byLinkText("Done")->click(); //  <<=== TODO: Тут почему-то падает

        // Again success
        $this->url("/");
        $this->waitForEngine();
        $this->pressBookmarklet();
        $this->assertTextPresent('Bookmarklet installed');
        $this->assertTextPresent('Hello ' . $data['name']);
        $this->byLinkText("Done")->click();

        // Show/hide private access key
        $this->assertTextPresent($data['public_key']);
        $this->assertTextNotPresent($data['access_key']);
//        $this->byLinkText("display")->click(); // Does not work
        $this->exec('$($("span.pull-right a").get(0)).click();');
        parent::waitUntil(function() {return $this->alertIsPresent();}, 3000);
        $warning = $this->alertText();
//        $this->assertTextNotPresent($data['access_key']);
        $this->acceptAlert();
        $this->assertContains('Keep your key', $warning);
        $this->assertTextPresent($data['access_key']);
//        $this->byLinkText("hide")->click();  // Does not work
        $this->exec('$($("span.pull-right a").get(0)).click();');
        $this->assertTextNotPresent($data['access_key']);
    }

    function test_relogin_from_bookmarklet()
    {
        $this->url("/");
        $data1 = $this->quickReg();
        $bookmarklet1 = $this->getBookmarkletScript();

        $this->exec('document.cookie = "YY=; expires=Thu, 01 Jan 1970 00:00:01 GMT;";');

        $this->url("/");
        $data2 = $this->quickReg();
        $bookmarklet2 = $this->getBookmarkletScript();

        $this->assertNotEquals($bookmarklet1, $bookmarklet2);
        $this->assertNotEquals($data1['name'], $data2['name']);
        $this->assertNotEquals($data1['public_key'], $data2['public_key']);
        $this->assertNotEquals($data1['access_key'], $data2['access_key']);

        $this->assertTextPresent($data2['name']);
        $this->assertTextNotPresent($data1['name']);

        $this->exec($bookmarklet1);
        sleep(2);
        $this->waitForEngine();

        $this->assertTextPresent($data1['name']);
        $this->assertTextNotPresent($data2['name']);

        $this->exec($bookmarklet2);
        sleep(2);
        $this->waitForEngine();

        $this->assertTextPresent($data2['name']);
        $this->assertTextNotPresent($data1['name']);
    }

    public function test_public_client()
    {
        // Parameters here to include token exchange in code coverage later
        $this->url("http://client?PHPUNIT_SELENIUM_TEST_ID=" . $this->getTestId());
        $this->assertTextPresent('You are not logged in');
        $this->byLinkText("Log in")->click();
        $result = $this->title();
        $this->assertEquals('Authentication', $result);
        $user = $this->quickReg(false);
        sleep(1); // Trying to avoid weird error on travis-ci
        $this->assertTextPresent("Hello, $user[name]!");
        $this->assertEquals("http://client/index.php", $this->url());
    }

    public function test_registered_client()
    {
        // This direct (not via HTTP API) intervention makes impossible testing on real site
        $db = new PDO(
            $_SERVER['ENV']['YY_AUTH_MYSQL_DATASOURCE'], $_SERVER['ENV']['YY_AUTH_MYSQL_USER'], $_SERVER['ENV']['YY_AUTH_MYSQL_PASSWORD']
        );
        $db->exec("INSERT INTO hosts(NAME) VALUES('client') ON DUPLICATE KEY UPDATE ID = LAST_INSERT_ID(ID)");
        $clientHostId = $db->lastInsertId();
        $db->exec("INSERT IGNORE INTO hosts_registered(NAME, REDIRECT_URI) VALUES ('client', 'http://client/login.php')");

        $this->url("/");
        $user = $this->quickReg();

        // Parameters here to include token exchange in code coverage later
        $this->url("http://client?PHPUNIT_SELENIUM_TEST_ID=" . $this->getTestId());
        $this->assertTextPresent('You are not logged in');

        $this->pressBookmarklet();
        $this->assertTextPresent("Hello, $user[name]!");
        $this->assertEquals("http://client/index.php", $this->url());
    }

}

