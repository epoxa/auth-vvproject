<?php

namespace YY\Develop\Tests;

use SebastianBergmann\ObjectEnumerator\Exception;
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
//        fwrite(STDERR, print_r($_SERVER['ENV'], true));
        $this->setBrowser($_SERVER['ENV']['YY_TESTS']['YY_TEST_BROWSER']); //  firefox
        $this->setBrowserUrl($_SERVER['ENV']['YY_TESTS']['YY_TEST_BASE_URL']); // http://yy.local/
        $this->setHost($_SERVER['ENV']['YY_TESTS']['YY_TEST_SELENIUM_HOST']); // 127.0.0.1
        $this->setPort($_SERVER['ENV']['YY_TESTS']['YY_TEST_SELENIUM_PORT']); // 4444
        $this->setDesiredCapabilities([
            'acceptSslCerts' => true,
            'acceptInsecureCerts' => true,
//            'unhandledPromptBehavior' => 'ignore',
        ]);
//        $this->shareSession(false);
        $this->setArtifactFolder(LOG_DIR);
        parent::setUp();
    }

    public function setUpPage()
    {
        // TODO: That's crazy! But I can not manage to set up more recent version of firefox selenium-node  :(
        if ($this->getBrowser() == 'firefox') {
            $this->timeouts()->implicitWait(5000);
        } else { // Chrome
            $this->__call('timeouts', [
                'pageLoad' => 5000,
                'script'=> 5000,
                'implicit' => 5000,
            ]);
        }
        parent::setUpPage();
    }

    protected function quickReg($full = true)
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
        $res = preg_match('/([0-9a-f]{32})/', $script, $a);
        $this->assertEquals(1, $res, 'Can not find public key in bookmarklet script');
        $public_key = $a[1];
        $this->exec($script);
        $result = $this->title();
        $this->assertEquals('Authentication', $result);
        $greeting = $this->byXPath('//p')->text();
        $greeting = explode(' ', $greeting);
        $this->assertEquals('Hello', $greeting[0]);
        $name = $greeting[1];
//        $this->byCssSelector("a.btn-primary")->click(); // TODO: Does not work! WTF?
        $this->byLinkText("Done")->click();
        if ($full) {
            $this->byLinkText("display")->click();
            usleep(500000);
            $this->acceptAlert();
            $access_key = $this->byCssSelector('tr:nth-child(3) strong')->text();
            $this->byLinkText("hide")->click();
            return [
                'name' => $name,
                'public_key' => $public_key,
                'access_key' => $access_key,
            ];
        } else {
            return [
                'name' => $name,
                'public_key' => $public_key,
            ];
        }
    }

    protected function pressBookmarklet()
    {
        $this->exec($this->getBookmarkletScript());
        usleep(300000);
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

    protected function killSession()
    {
        $url = $this->url();
        $host = parse_url($url, PHP_URL_HOST);
        $this->exec('document.cookie = "YY=; expires=Thu, 01 Jan 1970 00:00:01 GMT; path=/; domain=' . $host . '"');
        $this->exec('document.cookie = "YY=; expires=Thu, 01 Jan 1970 00:00:01 GMT; path=/; domain=.' . $host . '"');
    }

}
