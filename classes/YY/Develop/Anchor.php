<?php
namespace YY\Develop;

use YY\Develop\Builder;
use YY\Core\Data;
use YY\System\Robot;

/**
 * @property Data     $object
 * @property mixed    $field
 * @property  Builder $master
 */
class Anchor extends Robot
{

	public function _PAINT()
	{
		foreach ($this as $element) {
			echo '<div style="background-color: silver; border-bottom: 1px solid gray">';
			echo $element->object->_short_name() . '/' . $element->field;
			echo '<sup>' . $this->HUMAN_COMMAND(null, '&times;', 'remove', ['element' => $element]) . '</sup>';
			echo '</div>';
		}
		if (count($this) > 1) {
			echo '<div style="background-color: silver; border-bottom: 1px solid gray">';
			echo $this->HUMAN_COMMAND(null, 'CLEAR ALL', 'remove');
			echo '</div>';
		}
	}

	public function remove($params)
	{
		if (isset($params['element'])) {
			$element = $params['element'];
			$idx = $this->_index_of($element);
			unset($this[$idx]);
		} else {
			$this->_CLEAR();
		}
	}

	public function add($params)
	{
		$this[] = $params;
	}

	public function set($params)
	{
		$this->_CLEAR();
		$this[] = $params;
	}

}
