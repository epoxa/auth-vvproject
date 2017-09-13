<?php

use YY\Develop\BrowserTestCase;

require_once __DIR__ . '/../config/config.php';

class TestAuthentication extends BrowserTestCase
{

    protected function setUp()
    {
        $this->setBrowser(getenv('YY_TEST_BROWSER')); //  firefox
        $this->setBrowserUrl(getenv('YY_TEST_BASE_URL')); // http://yy.local/
        $this->setHost(getenv('YY_TEST_SELENIUM_HOST')); // 127.0.0.1
        $this->setPort((int)getenv('YY_TEST_SELENIUM_PORT')); // 4444
        $this->setDesiredCapabilities([
            'acceptSslCerts' => true,
            'acceptInsecureCerts' => true,
        ]);
        $this->setArtifactFolder(LOG_DIR);
        parent::setUp();
    }

    public function setUpPage()
    {
        $this->timeouts()->implicitWait(5000);
    }

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
// assertText | //div[@id='_YY_2']/div/div[3]/span[2]/span[2] | Your personal access button:
        $result = $this->byXPath("//div[@id='_YY_2']/div/div[3]/span[2]/span[2]")->text();
        $this->assertEquals("Your personal access button:", $result);
// assertText | //button[@type='button'] | English
        $result = $this->byXPath("//button[@type='button']")->text();
        $this->assertEquals("English", $result);
// click | link=English |
        $this->byXPath("//button[@type='button']")->click();
// click | link=Русский |
        $this->byLinkText("Русский")->click();
// assertText | //div[@id='_YY_2']/div/div[3]/span[2]/span[2] | Ваша персональная кнопка доступа:
        $result = $this->byXPath("//div[@id='_YY_2']/div/div[3]/span[2]/span[2]")->text();
        $this->assertEquals("Ваша персональная кнопка доступа:", $result);
// storeText | css=a.bm-template | bookmarkletText
        $bookmarkletText = $this->byCssSelector("a.bm-template")->text();
        $this->assertEquals($userName, $bookmarkletText);
// storeAttribute | css=a.bm-template | script
        $script = $this->byCssSelector("a.bm-template")->attribute("href");
// Emulate press bookmarklet
        $script = preg_replace('/^javascript:/','',$script);
        $script = urldecode($script);
//        $this->assertEquals('!', $script);
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
    }

    public function test_client()
    {
        $this->url("http://client");
        $this->assertTextPresent('You are not logged in');
        $this->byLinkText("Log in")->click();
        $result = $this->title();
        $this->assertEquals('Authentication', $result);
        $user = $this->quickReg();
        $this->assertTextPresent("Hello, $user[name]!");
        $this->assertEquals("http://client/index.php", $this->url());
    }

    protected function quickReg()
    {
        $this->byLinkText("English")->click();
        $this->byCssSelector("a.btn-primary i.fa-plus")->click();
        $this->byCssSelector("a.btn-default")->click();
        $this->byCssSelector("a.btn-default")->click();
        $script = $this->byCssSelector("a.bm-template")->attribute("href");
        $script = preg_replace('/^javascript:/','',$script);
        $script = urldecode($script);
        $this->exec($script);
        $result = $this->title();
        $this->assertEquals('Authentication', $result);
        $greeting = $this->byXPath('//p')->text();
        $greeting = explode(' ', $greeting);
        $this->assertEquals('Hello', $greeting[0]);
        $name = $greeting[1];
//        $this->byCssSelector("a.btn-primary")->click(); // TODO: Does not work! WTF?
        $this->byLinkText("Done")->click();
        return [
            'name' => $name,
        ];
    }

}

