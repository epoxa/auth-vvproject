<?php

namespace YY\Develop\Tests;

use YY\Develop\BrowserTestCase;

class AuthTestCase extends BrowserTestCase
{

    protected $coverageScriptUrl = 'http://web/selenium/phpunit_coverage.php';

    private $bookmarkletScript;

    /**
     * @return string
     */
    protected function getBookmarkletScript()
    {
        return $this->bookmarkletScript;
    }

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
//        $this->shareSession(false);
        $this->setArtifactFolder(LOG_DIR);
        parent::setUp();
    }

    public function setUpPage()
    {
        $this->timeouts()->implicitWait(5000);
    }

    protected function quickReg()
    {
        $this->assertEquals('Authentication', $this->title());
        $this->byLinkText("English")->click();
        $this->byCssSelector("a.btn-primary i.fa-plus")->click();
        $this->byCssSelector("a.btn-default")->click();
        $this->byCssSelector("a.btn-default")->click();
        $script = $this->byCssSelector("a.bm-template")->attribute("href");
        $script = preg_replace('/^javascript:/','',$script);
        $script = urldecode($script);
        $this->bookmarkletScript = $script;
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

    protected function pressBookmarklet()
    {
        $this->exec($this->getBookmarkletScript());
    }

    protected function restartWorld()
    {
        $d = opendir(DATA_DIR);
        while ($fileName = readdir($d)) {
            if (!preg_match('/^\./', $fileName)) {
                unlink(DATA_DIR . $fileName);
            }
        };
        closedir($d);
    }

}
