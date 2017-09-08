<?php

use YY\Develop\BrowserTestCase;

class TestFirst extends BrowserTestCase
{

    protected function setUp()
    {
        $this->setBrowser(getenv('YY_TEST_BROWSER')); //  firefox
        $this->setBrowserUrl(getenv('YY_TEST_BASE_URL')); // http://yy.local/
        $this->setHost(getenv('YY_TEST_SELENIUM_HOST')); // 127.0.0.1
        $this->setPort((int)getenv('YY_TEST_SELENIUM_PORT')); // 4444
    }

    public function setUpPage()
    {
        $this->timeouts()->implicitWait(5000);
    }

    public function test_demo()
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
// click | link=Create new character |
        $this->byLinkText("Create new character")->click();
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
    }

}
