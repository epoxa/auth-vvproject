<?php
namespace YY\Develop;

use YY\Develop\Builder;
use YY\System\Robot;

/**
 * @property Builder master
 */
class ValueEditor extends Robot
{

	public function __construct($init)
	{
		parent::__construct($init);
		$this->new_value = null;
	}

	public function _PAINT()
	{
		$this->new_value = $this->master->activeNode->world[$this->master->activeField];
		$val = $this->new_value;
		if (is_string($this->new_value)) {
			echo $this->HUMAN_TEXT(['multiline' => true], 'new_value');
		} else {
			echo $this->HUMAN_TEXT(null, 'new_value');
		}
//    echo '<textarea name="new_value" id="' . $this->_YYID . '[new_value]" onchange="changed(this)">' . htmlspecialchars($this->new_value) . '</textarea>';
//    echo '<script type="text/javascript">document.getElementById("' . $this->_YYID . '[new_value]").focus()</script>';
		//      echo '<script type="text/javascript">setTimeout("CodeMirror.fromTextArea(document.getElementById(\''.$this->_YYID.'[new_value]\'),{mode: \'text/plain\'})",20);</script>';
	}

	public function checkUpdateModified()
	{

		//    TODO: Почему-то isset($this->master->activeNode) возвращает false, хотя свойство установлено!
		//    if (isset($this->master['activeNode'])) $this->master->message->show('isset!');
		//    else $this->master->message->show('not set :-(');

		$master = $this->master;
		$parent = $master->activeNode;
		$activeProp = $master->activeField;
		if (isset($parent) && isset($activeProp) && $master->editValueMode) {
			$old_value = $parent->world[$activeProp];
			if ($old_value !== $this->new_value) {
				$parent->world[$activeProp] = $this->new_value;
				Builder::markConfigModified($parent->world);
			}
		}
	}

}
