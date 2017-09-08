<?php

namespace YY\Develop;

use Exception;
use YY\Core\Data;
use YY\Core\Importer;
use YY\System\Robot;

/**
 * @property Builder master
 */
class Commander extends Robot
{

	public function __construct($init)
	{
		parent::__construct($init);
		$this->mode = ""; // Режим отображения
		$this->submode = "";
		$this->file = null;
		$this->type = ""; // Тип создаваемого узла
		$this->new_name = ""; // Используется при переименовании и создании свойств
	}

	public function _PAINT()
	{
		$mode = $this->mode;
		if ($mode === "creating" && !$this->type) {
			echo "&nbsp;Create...";
			echo $this->HUMAN_COMMAND(null, htmlspecialchars(" <Object> "), "create", array('type' => 'object'));
			echo $this->HUMAN_COMMAND(null, htmlspecialchars(" <String> "), "create", array('type' => 'string'));
			echo $this->HUMAN_COMMAND(null, htmlspecialchars(" <Integer> "), "create", array('type' => 'integer'));
			echo $this->HUMAN_COMMAND(null, htmlspecialchars(" <Real> "), "create", array('type' => 'float'));
			echo $this->HUMAN_COMMAND(null, " <sup>&times;</sup> ", "cancel");
			return;
		} else if ($mode === "renaming" || preg_match('/^creating/', $mode)) {
			echo "&nbsp;New name: " . $this->HUMAN_TEXT(null, 'new_name');
			echo $this->HUMAN_COMMAND(null, htmlspecialchars(" <Save> "), "save", array('name' => array($this, "new_name")));
			echo $this->HUMAN_COMMAND(null, " <sup>&times;</sup> ", "cancel");
			return;
		} else if ($mode === "inserting") {
			echo $this->HUMAN_COMMAND(null, htmlspecialchars(" <Create link> "), "do_insert", ['mode' => 'link']);
			echo $this->HUMAN_COMMAND(null, htmlspecialchars(" <Build copy> "), "do_insert", ['mode' => 'copy']);
			echo $this->HUMAN_COMMAND(null, htmlspecialchars(" <Move> "), "do_insert", ['mode' => 'move']);
			echo $this->HUMAN_COMMAND(null, " <sup>&times;</sup> ", "cancel");
			return;
		}
		// Предполагаем, что объект всегда задан, раз уж вызвана отрисовка коммандера
		$parent = $this->master->activeNode;
		$world = $parent->world;
		$activeProp = $this->master->activeField;
		if (is_object($world[$activeProp])) {
			echo "<span style='font-size:xx-small;color:silver'>" . $world[$activeProp]->_YYID . '<br></span>';
		}
		list($up, $down, $obj) = [false, false, false]; // На случай, если свойство почему-то не будет найдено.
		if (is_object($activeProp)) {
			$object_keys = $world->_object_keys();
			$ancorCnt = count($object_keys);
			$idx = 0;
			foreach ($object_keys as $prop) {
				if (Data::_isEqual($prop, $activeProp)) {
					$up = $idx > 0;
					$down = $idx < $ancorCnt - 1;
					$obj = is_object($world[$prop]);
					break;
				}
				$idx++;
			}
		} else {
			$ancorCnt = count($world);
			$idx = 0;
			foreach ($world as $prop => $val) {
				if ($prop === $activeProp) {
					$up = $idx > 0;
					$down = $idx < $ancorCnt - 1;
					$obj = is_object($val);
					break;
				}
				$idx++;
			}
		}
		$anchor = $this->master->anchor;
		if (!!$ancorCnt = count($anchor)) {
			$first = isset($anchor[0]) ? $anchor[0] : null;
			echo "&nbsp;" . $this->HUMAN_COMMAND([
					'title' => $ancorCnt > 1 && $first ? $ancorCnt . ' items'
						: $first->object->_short_name() . '/' . $first->field,
				], htmlspecialchars('<insert>'), 'insert');
			echo $anchor->HUMAN_COMMAND(['title' => 'forget'], " <sup>&times;</sup> ", "remove");
		}
		if ($down) echo "&nbsp;" . $this->HUMAN_COMMAND(null, '&dArr;', 'move', ['direction' => 'down']);
		if ($up) echo "&nbsp;" . $this->HUMAN_COMMAND(null, '&uArr;', 'move', ['direction' => 'up']);
		echo "&nbsp;" . $this->HUMAN_COMMAND(null, htmlspecialchars('<rename>'), 'rename');
		if ($obj && !$val->_OWNER) {
			echo "&nbsp;" . $this->HUMAN_COMMAND(null, htmlspecialchars("<unlink>"), 'delete');
			echo "&nbsp;" . $this->HUMAN_COMMAND(null, htmlspecialchars("<take ownership>"), 'takeOwnership');
		} else {
			echo "&nbsp;" . $this->HUMAN_COMMAND(null, htmlspecialchars("<delete>"), 'delete');
		}
		echo "&nbsp;" . $this->master->anchor->HUMAN_COMMAND(null, htmlspecialchars('<remember>'), 'add', ['object' => $parent->world, 'field' => $activeProp]);
		if ($ancorCnt) {
			echo $anchor->HUMAN_COMMAND(['title' => 'forget'], " <sup>&times;</sup> ", "remove");
		}
		if ($obj) {
			echo "&nbsp;" . $this->HUMAN_COMMAND(null, htmlspecialchars('<create...>'), 'create');
			if (isset($this->master['isWorld']) && $parent->way !== null && $world[$activeProp]->_OWNER) {
				echo "&nbsp;" . $this->HUMAN_COMMAND(null, htmlspecialchars('<recreate>'), 'recreate');
			}
		} else {
			echo "&nbsp;" . $parent->HUMAN_COMMAND(null, htmlspecialchars('<edit>'), 'edit', ['prop' => $activeProp]);
		}
		echo $parent->HUMAN_COMMAND(null, " <sup>&times;</sup> ", "press", ['prop' => null]);
	}

	public function cancel()
	{
		$this->mode = "";
	}

	public function insert()
	{
		$this->mode = "inserting";
	}


	public function do_insert($param)
	{
		$this->mode = "";
		$insert_mode = $param['mode'];
		foreach ($this->master->anchor as $key => $element) {
			$val = $element['object'];
			if (is_object($val) && $val->offsetExists($fld = $element['field'])) {
				if ($insert_mode === 'link') {
					$val = $val[$fld];
				} else if ($insert_mode === 'move') {
					// TODO: Проверить отсутствие цикла
					if (is_object($val[$fld])) {
						$obj = $val->_DROP($fld);
					} else {
						$obj = $val[$fld];
					}
					unset($val[$key]);
					$val = $obj;
				} else if ($insert_mode === 'copy') {
					// TODO: Проверить отсутствие цикла
					if (is_object($val[$fld])) {
						$val = $val[$fld]->_CLONE();
					} else {
						$val = $val[$fld];
					}
				} else {
					throw new Exception('Invalid insert mode: ' . $insert_mode);
				}
				$parent = $this->master->activeNode->world;
				$currentPropertyName = $this->master->activeField;
				$recipient = $parent[$currentPropertyName];
				$justFieldName = $fld;
				$justFieldIndex = 1;
				while (isset($recipient[$fld])) {
					$justFieldIndex++;
					$fld = $justFieldName . ' (' . $justFieldIndex . ')';
				};
				$recipient[$fld] = $val;
				if (($insert_mode === 'move' || $insert_mode === 'copy') && isset($recipient['_path']) && is_object($val)) {
					Importer::UpdateNodePathInfo($val, $recipient['_path'] . Builder::WAY_SEPARATOR . $fld, true);
				}
				if ($insert_mode === 'move') {
					$element['object'] = $recipient;
					$element['field'] = $fld;
				}
			}
		}
		if ($insert_mode === 'move') {
			$this->master->anchor->_CLEAR();
		}
		Builder::markConfigModified($recipient);
	}

	public function takeOwnership()
	{
		$parent = $this->master->activeNode->world;
		assert(isset($parent['_path']));
		$propertyName = $this->master->activeField;
		$val = $parent[$propertyName];
		assert(!$val->_OWNER);
		$previousPath = explode(Builder::WAY_SEPARATOR, $val['_path']);
		$previousPropertyName = array_pop($previousPath);
		$previousParent = Builder::getExistingChild(implode(Builder::WAY_SEPARATOR, $previousPath));
		$parent[$propertyName] = null;
		$parent[$propertyName] = $previousParent->_DROP($previousPropertyName);
		Importer::UpdateNodePathInfo($val, $parent['_path'] . Builder::WAY_SEPARATOR . $propertyName, true);
		Builder::markConfigModified($val);
	}

	public function move($param)
	{

		$parent = $this->master->activeNode;
		$world = $parent->world;
		$activeProp = $this->master->activeField;


		$all_props = [];
		$get_next_p2 = false;
		$prev_p = null;
		$p1 = null;
		$p2 = null;

		if (is_object($activeProp)) {
			foreach ($world->_object_keys() as $prop) {
				$all_props[] = $prop;
				if ($get_next_p2) {
					$p2 = $prop;
					unset($get_next_p2);
				} else if (Data::_isEqual($prop, $activeProp)) {
					if ($param['direction'] === 'up') {
						$p1 = $prev_p;
						$p2 = $prop;
					} else {
						$p1 = $prop;
						$get_next_p2 = true;
					}
				}
				$prev_p = $prop;
			}
		} else {
			foreach ($world as $prop => $val) {
				$all_props[] = $prop;
				if ($get_next_p2) {
					$p2 = $prop;
					unset($get_next_p2);
				} else if ($prop === $activeProp) {
					if ($param['direction'] === 'up') {
						$p1 = $prev_p;
						$p2 = $prop;
					} else {
						$p1 = $prop;
						$get_next_p2 = true;
					}
				}
				$prev_p = $prop;
			}
		}
		if ($param['direction'] === 'up' && $p1 === null) {
			$this->master->message->show('Невозможно сдвинуть поле!');
			return;
		}
		if ($param['direction'] === 'down' && $p2 === null) {
			$this->master->message->show('Невозможно сдвинуть поле!');
			return;
		}
		$temp = new Data;
		foreach ($all_props as $prop) {
			if (Data::_isEqual($prop, $p1)) {
				$temp[$p2] = $world->_DROP($p2);
			} else if (Data::_isEqual($prop, $p2)) {
				$temp[$p1] = $world->_DROP($p1);
			} else {
				$temp[$prop] = $world->_DROP($prop);
			}
		}
		foreach ($all_props as $prop) {
			unset($world[$prop]);
		}
		foreach ($temp as $prop => $val) {
			$world[$prop] = $temp->_DROP($prop);
		}
		foreach ($temp->_object_keys() as $prop) {
			$world[$prop] = $temp->_DROP($prop);
		}
		Builder::markConfigModified($world);
	}

	public function delete()
	{
		$this->mode = "";
		$world = $this->master->activeNode->world;
		if (isset($world)) {
			unset($world[$this->master->activeField]);
			$this->master->activeNode = null;
			$this->master->activeField = null;
			Builder::markConfigModified($world);
		}
	}

	public function rename()
	{
		$this->mode = "renaming";
		$this->new_name = $this->master->activeField;
	}

	public function create($param)
	{
		$this->mode = "creating";
		$this->type = "" . $param['type'];
		$this->new_name = "";
	}

	public function save($param)
	{
		$parent = $this->master->activeNode;
		$currentPropertyName = $this->master->activeField;
		$newPropertyKey = $param['name'];
		if (substr($newPropertyKey, 0, 1) === '[' && substr($newPropertyKey, -1) == ']') {
			$path = substr($newPropertyKey, 1, -1);
			$newPropertyKey = Builder::getExistingChild($path);
		}

		if ($this->mode === 'creating') {
			$node = $parent->world[$currentPropertyName]; // Соответствует созданию дочернего узла. Можно и для соседей сделать.
		} else { // renaming
			$node = $parent->world;
		}
		$temp = new Data;

		if ($this->mode === 'creating') {
			switch ($this->type) {
				case 'object':
					$newVal = new Data();
					break;
				case 'string':
					$newVal = "";
					break;
				case 'integer':
					$newVal = 0;
					break;
				case 'float':
					$newVal = 0.0;
					break;
				default:
					$newVal = null;
					break;
			}
			$temp[$newPropertyKey] = $newVal; // В случае создания свойства вставляем его в начало, то есть ДО копирования остальных свойств
		}

		$all_props = $node->_all_keys();
		foreach ($all_props as $propKey) {
			if (is_string($propKey) && substr($propKey, 0, 1) === '_') continue;
			$propNewName = $propKey;
			if ($this->mode === 'renaming' && Data::_isEqual($propKey, $currentPropertyName)) {
				$propNewName = $newPropertyKey;
			}
			if ($this->mode === 'creating' && Data::_isEqual($propKey, $newPropertyKey)) {
				$this->master->message->show("Property already exists");
				return;
			}
			if ($this->mode === 'renaming' && !Data::_isEqual($propKey, $currentPropertyName) && Data::_isEqual($propKey, $newPropertyKey)) {
				$this->master->message->show("Property already exists");
				return;
			}
			$temp[$propNewName] = $node->_DROP($propKey);
		}
		foreach ($all_props as $propKey) {
			if (is_string($propKey) && substr($propKey, 0, 1) === '_') continue;
			unset($node[$propKey]);
		}
		$allNewKeys = $temp->_all_keys();
		foreach ($allNewKeys as $propKey) {
			$node[$propKey] = $temp->_DROP($propKey);
		}
		$val = $node[$newPropertyKey];
		if (is_object($val) && isset($node['_path']) && ($this->mode === 'creating' || $val->_OWNER)) {
			Importer::UpdateNodePathInfo($node[$newPropertyKey], $node['_path'] . Builder::WAY_SEPARATOR . $newPropertyKey, true);
		}
		Builder::markConfigModified($node);
		if ($this->mode === 'renaming') {
			if (isset($parent->expandedProperties[$currentPropertyName])) {
				$parent->expandedProperties[$newPropertyKey] = $parent->expandedProperties->_DROP($currentPropertyName);
				unset($parent->expandedProperties[$currentPropertyName]);
			}
			$this->master->activeField = $newPropertyKey;
		} else {
			if (!isset($parent->expandedProperties[$currentPropertyName])) {
				$parent->expand(['prop' => $currentPropertyName]);
			}
			$childNodeBuilder = $parent->expandedProperties[$currentPropertyName];
		}
		$this->mode = "";
		$this->master->message->close_message(); // Поди можно тут стирать, чо оно будет болтаться-то...
	}

	/*
	public function load()
	{
	  if ($this->submode === 'uploading') {
		$this->submode = '';
		if (isset($this['file'])) {
		  $file = $this['file'];
		  $newData = DataConverter::importXML($file, $this->master->message);
		  if ($newData) {
			$this->master->activeNode->world[$this->master->activeField] = $newData;
			$this->master->message->show("Файл загружен.");
		  } else {
			$this->master->message->show("Неправильный формат файла.");
		  }
		  $this->file = null;
		  $this->submode = '';
		} else {
		  $this->master->message->show("Файл не выбран.");
		}
	  } else {
		$this->submode = 'uploading';
	  }
	  $this->file = null;
	}
	*/

	public function recreate()
	{
		$parent = $this->master->activeNode;
		$activeProp = $this->master->activeField;
		$way = $parent->way . Builder::WAY_SEPARATOR . $activeProp;
		Importer::reloadWorldPart($way, CONFIGS_DIR . '.current');
	}

}
