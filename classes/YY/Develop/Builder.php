<?php

namespace YY\Develop;

use YY\Core\Ref;
use YY\Develop\Anchor;
use YY\Develop\Commander;
use YY\Develop\MessageBox;
use YY\Develop\NodeBuilder;
use YY\Develop\ValueEditor;
use YY\System\Robot;
use YY\System\YY;

/**
 * @property MessageBox  message
 * @property NodeBuilder activeNode
 * @property mixed       activeField
 * @property boolean     $editValueMode
 * @property Anchor      $anchor
 */
class Builder extends Robot
{

	const WAY_SEPARATOR = '/';
	const FILE_SEPARATOR = '/';

	public function __construct($init)
	{

		$world = $init['world'];
		unset($init['world']);

		$init = array_merge($init, array(
			'message' => new MessageBox(),
			'activeNode' => null,
			'activeField' => null,
			'editValueMode' => false,
		));

		parent::__construct($init);

		$master_ref = new Ref($this, false); // TODO: Пока слабая ссылка на главный объект сделана так, может можно и поизящнее как-то сделать

		$this->root = new NodeBuilder(array(
			'world' => $world,
			'master' => $master_ref,
			'way' => '',
		));

		$this->commander = new Commander(array(
			'master' => $master_ref,
		));

		$this->editor = new ValueEditor(array(
			'master' => $master_ref,
		));

		$this->anchor = new Anchor();

		$this->include = array( // TODO: Наверное, это не здесь должно устанавливаться
			'css' => '<link rel="stylesheet" type="text/css" href="/themes/builder/css/builder.css" media="all"/>'
		);

	}

	public function _PAINT()
	{
		$this->anchor->_SHOW();
		$this->message->_SHOW();
		$this->root->_SHOW();
	}

	public function deactivateCurrent()
	{
		if (isset($this->activeNode) && isset($this->activeField)) {
			$this->editor->checkUpdateModified();
			$this->activeNode = null;
			$this->activeField = null;
		}
	}

	static public function markConfigModified($worldNode)
	{
		if (isset($worldNode['_path'])) {
			YY::$WORLD['configModified'] = true;
		}
	}

	static public function getExistingChild($wayFromRoot)
	{
		if ($wayFromRoot === '/') $wayFromRoot = '';
		$props = explode('/', $wayFromRoot);
		if ($props[0] === '') array_shift($props);
		$obj = YY::$WORLD;
		foreach ($props as $prop) {
			if (!isset($obj[$prop])) {
				$obj = YY::$WORLD;
				break;
			}
			$obj = $obj[$prop];
			if (!is_object($obj)) throw new Exception('Not an object: ' . $wayFromRoot . " (" . var_export($obj, true) . ")");
		}
		return $obj;
	}

}
namespace YY\Develop;

use DOMException;

function HandleXmlError($errno, $errstr, $errfile, $errline)
{
    if ($errno == E_WARNING && (substr_count($errstr, "DOMDocument::loadXML()") > 0)) {
        throw new DOMException($errstr);
    } else {
        return false;
    }
}

// Пока что реализовано на обычных объектах PHP, но впоследствии, в принципе, можно будет переделать на процесс,
// написанный на нескольких Gatekeeper, и редактировать его прямо посредством выполнения самого себя.
