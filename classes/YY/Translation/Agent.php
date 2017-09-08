<?php


namespace YY\Translation;


use YY\System\Translation\TranslatorInterface;
use YY\Core\Exporter;
use YY\System\Robot;
use YY\System\YY;

class Agent extends Robot implements TranslatorInterface
{

	function __construct($init = null)
	{
		parent::__construct($init);
        $this->includeAsset([
            '<script src="/translate/agent.js"></script>',
            '<script src="/js/jquery.editable.js"></script>',
        ]);
		$this['slugs'] = [];
	}

    function close()
    {
        YY::clientExecute("window.yy_translate_agent.close();");
    }

	function _PAINT()
	{
		$myHandle = YY::GetHandle($this);
		YY::clientExecute("window.yy_translate_agent.setTranslatorHandle('$myHandle');");
	}

	function registerTranslatable($trace, $slug, $original, $attributes)
	{
        $attributes['data-translate-slug'] = $slug;
        $languages = YY::Config('LANGUAGES');
        if (empty($languages[''])) $languages[''] = []; // Original draft phrase
        $languages[''][$slug] = $original;
		$this['slugs'][$slug] = $original;
        if (isset(YY::$CURRENT_VIEW, YY::$CURRENT_VIEW['TRANSLATION'])) {
            if (!isset(YY::$CURRENT_VIEW['TRANSLATION'][$slug])) {
                YY::$CURRENT_VIEW['TRANSLATION'][$slug] = null; // Indicates need to translate
                $style = (isset($attributes['style']) ? $attributes['style'] . ';' : '' ) .'color: red';
                $attributes['style'] = $style;
            }
        }
		$slug = json_encode($slug);
        $yyid = YY::GetHandle($this);
		YY::clientExecute("window.yy_translate_agent.registerTranslatable($slug, $yyid);");
        return $attributes;
	}

	function showTranslatePrompt($_params)
	{
		$slug = $_params['slug'];
		$original = json_encode($this['slugs'][$slug]);
		$current = json_encode(isset(YY::$CURRENT_VIEW['TRANSLATION'][$slug]) && YY::$CURRENT_VIEW['TRANSLATION'][$slug] !== null ? YY::$CURRENT_VIEW['TRANSLATION'][$slug] : '');
		$slug = json_encode($slug);
		YY::clientExecute("window.yy_translate_agent.showTranslatePrompt($slug,$original,$current);");
	}

	function setTranslation($_params)
	{
		// TODO: Filter some html tags
		$slug = $_params['slug'];
		$translation = $_params['translation'];
		YY::$CURRENT_VIEW['TRANSLATION'][$slug] = $translation;
        Exporter::exportSubtree(YY::Config('LANGUAGES'), CONFIGS_DIR . '/.current/CONFIG/LANGUAGES', ['']);
	}

}
