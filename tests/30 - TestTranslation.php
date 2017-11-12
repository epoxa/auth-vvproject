<?php

use YY\Develop\Tests\AuthTestCase;

require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/../config/config.php';

class TestTranslation extends AuthTestCase
{

    public function test_language_edit_mode()
    {
        $this->url("/");
        $this->byLinkText('Add your language')->click();
        $this->byCssSelector('input')->value('Esperanto');
        $this->byLinkText('Save')->click();
        $this->byCssSelector('span.fa-edit')->click();
        sleep(2);
        $this->byLinkText('Save')->click();
    }

}

