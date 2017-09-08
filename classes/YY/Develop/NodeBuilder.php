<?php
namespace YY\Develop;

use YY\Core\Data;
use YY\System\Robot;
use YY\System\YY;

/**
 * @property Builder master
 * @property Data    world
 * @property Data    expandedProperties
 */
class NodeBuilder extends Robot
{

	public function __construct($init)
	{
		parent::__construct($init);
		$this->currentProperty = null;
		$this->expandedProperties = [];
	}

	private function drawProperty($property_name, $property_value, $edit_name, $edit_value)
	{
		$is_active = $edit_name || $edit_value;
		$is_object = is_object($property_value);
		$is_empty = $is_object && !count(array_diff($property_value->_scalar_keys(), ['_path', '_source'])) && !count($property_value->_object_keys());
		$is_expanded = $is_object && isset($this->expandedProperties[$property_name]);

		// TODO: Это перенести в дейcтвия. Недопустимо менять чего-то в SHOW
		if ($is_empty && $is_expanded) {
			unset($this->expandedProperties[$property_name]);
			$is_expanded = false;
		}

		if ($is_active) {
			$hot_class = " class='hot'";
		} else $hot_class = "";

		if ($is_object) {
			$robot_hint = " title='" . $property_value . " - " . $property_value->_YYID . "'";
		} else $robot_hint = "";

		//    $space = '';
		//    for($i = 0; $i < $level; $i++) $space .= "&nbsp;&nbsp;";

		// TODO: Рисуем некую картинку, отображающее тип свойства и состояние раскрытости для объектов
		$brace_1 = '';
		$brace_2 = '';
		if ($is_object) {
			echo "<div class='complex-property' style='border-top: 1px dotted silver'>";
			if ($is_empty) {
				echo "&middot";
			} else if ($is_expanded) {
				echo $this->HUMAN_COMMAND(null, '-', 'collapse', array('prop' => $property_name));
			} else {
				echo $this->HUMAN_COMMAND(null, '+', 'expand', array('prop' => $property_name));
			}
			if ($property_value->_OWNER) {
				$brace_1 = '[';
				$brace_2 = ']';
			} else {
				$brace_1 = '{';
				$brace_2 = '}';
			}
			// У объектов, считанных с диска, рисуем ссылку на редактирование в IDE
			if (isset($property_value['_source'])) {
				$sourcePath = $property_value['_source'];
				$sourcePath = str_replace('\\', '\\\\', $sourcePath);
				$visual = array(
					'before' => '
          <a href="webcal:' . $sourcePath . '"
          class="editor-item-caption active-item">'
						. '&nbsp;>>' . '</a>',
				);
				$brace_2 .= YY::drawVisual($visual);
			}
		} else if (is_null($property_value)) {
			echo "<div class='null-property' style='border-top: 1px dotted silver'>&nbsp;";
		} else if (is_string($property_value)) {
			echo "<div class='string-property' style='border-top: 1px dotted silver'>&nbsp;";
		} else if (is_int($property_value)) {
			echo "<div class='integer-property' style='border-top: 1px dotted silver'>&nbsp;";
		} else if (is_float($property_value)) {
			echo "<div class='float-property' style='border-top: 1px dotted silver'>&nbsp;";
		} else {
			echo "<div class='unknown-property' style='border-top: 1px dotted silver'>?";
		}
		echo $brace_1;
		echo "<strong$hot_class$robot_hint>";
		echo $this->HUMAN_COMMAND(null, $property_name, 'press', ['prop' => $property_name]);
		echo "</strong>";
		echo $brace_2;

		if ($is_object) {
			if ($is_active) $this->master->commander->_SHOW();
			echo "</div>";
			if ($is_expanded) {
				// Должен существовать, так как $is_expanded === true
				/** @var $childBuilder NodeBuilder $childBuilder */
				$childBuilder = $this->expandedProperties[$property_name];
				$childBuilder->_SHOW();
			};
		} else {
			if ($edit_value) {
				$this->master->editor->_SHOW();
			} else {
				if (strpos($property_value, "return (require '") === 0) {
					preg_match("#return \(require \'(.*)\'#", $property_value, $a);
					$fileName = $a[1];
					$fileName = str_replace('\\', '\\\\', $fileName);
					$shellParams = "'" . EDITOR_PATH . "', '" . $fileName . "'";
					$visual = array(
						'before' => '
          <a href="webcal:' . $fileName . '" class="editor-item-caption active-item">'
							. $property_value . '</a>',
					);
					echo YY::drawVisual($visual);
				} else {
					if (is_int($property_value) && $property_value > 1300000000 && $property_value < 1500000000) { // TIMESTAMP
						$property_value = date('d/m/Y H:i:s', $property_value);
					}
					echo $this->HUMAN_COMMAND(null, '<span class="property-value">' . htmlspecialchars($property_value) . '</span>', 'edit',
						['prop' => $property_name]);
				}
			}
			if ($edit_name) {
				$this->master->commander->_SHOW();
			}
			echo "</div>";
		}

	}

	private function drawProperties()
	{
		$activeNode = $this->master->activeNode;
		$im_active = self::_isEqual($activeNode, $this);
		$active_property = $this->master->activeField;
		$editor_mode = $this->master->editValueMode;
		$world = $this->world;
		if ($world) {
			// Сначала собираем все свойства в отдельный массив, так как внутри цикла сбивается итератор
			$allProperties = [];
			foreach ($world as $property_name => $property_value) {
				$allProperties[] = ['key' => $property_name, 'val' => $property_value];
			}
			foreach ($world->_object_keys() as $property_name) {
				$property_value = $world[$property_name];
				$allProperties[] = ['key' => $property_name, 'val' => $property_value];
			}
			// А потом уже обрабатываем в общем цикле
			foreach ($allProperties as $propPair) {
				$property_name = $propPair['key'];
				$property_value = $propPair['val'];
				$prop_active = $im_active && Data::_isEqual($property_name, $active_property);
				$edit_name = $prop_active && !$editor_mode;
				$edit_value = $prop_active && $editor_mode;
				$this->drawProperty(
					$property_name, $property_value, $edit_name, $edit_value
				);
			}
		}
	}

	public function _PAINT()
	{
		echo '<div class="properties" style="margin-left: 20px; border-left: 1px dotted silver">';
		$this->drawProperties();
		echo '</div>';
	}

	public function press($param)
	{
		$data = new Data();
		$master = $this->master;
		$commander = $master->commander;
		$master->deactivateCurrent();
		$master->activeNode = $this;
		$master->activeField = $param['prop'];
		$master->editValueMode = false;
		$commander->mode = ""; // Где-то не здесь это надо делать, а в самом коммандере
		$commander->submode = "";
	}

	public function edit($param)
	{
		$master = $this->master;
		$master->deactivateCurrent();
		$master->activeNode = $this;
		$master->activeField = $param['prop'];
		$master->editValueMode = true;
	}

	public function expand($param)
	{ // public, так как вызывается из коммандера
		$propName = $param['prop'];
		if (!isset($this->world[$propName])) return; // TODO: Странный случай. Может генерить исключение?
		$propValue = $this->world[$propName];
		if (!is_object($propValue)) return; // TODO: Странный случай. Может генерить исключение?
		if (isset($this->expandedProperties[$propName])) { // TODO: Странный случай. Может генерить исключение?
			unset($this->expandedProperties[$propName]);
		}
		$childWay = null;
		if ($propValue->_OWNER && !is_object($propName)) {
			$childWay = $this->way . '/' . $propName;
		}
		$childBuilder = new NodeBuilder(array(
			'world' => $propValue,
			'master' => $this->master,
			'way' => $childWay,
		));
		$this->expandedProperties[$propName] = $childBuilder;
	}

	public function collapse($param)
	{
		$this->master->editor->checkUpdateModified();
		unset($this->expandedProperties[$param['prop']]);
	}

	public function get($params)
	{
		$what = $this->world;
		if ($params['what'] === 'prop') {
			$what = $what[$this->master->activeField]; // Должно существовать и быть объектом, если не менялось извне
			$file_name = strtr($this->master->activeField, array(
					'<' => '',
					'>' => '',
					'\\' => '',
					'/' => '',
					'|' => '',
					'?' => '',
					'*' => '',
					':' => '"',
				)) . '.xml';
		} else {
			$file_name = $what->_YYID . '.xml';
		}
		header('Content-Type: application/octet-stream');
		//      header('Content-Type: text/html');
		header('Content-Disposition: inline; filename="' . $file_name . '"');
		header("Content-Transfer-Encoding: binary\n");
		// suppress client-side caching:
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");
		header("Expires: " . gmdate("D, d M Y H:i:s", mktime(date("H") + 2, date("i"), date("s"), date("m"), date("d"), date("Y"))) . " GMT");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		// Выводим
		$xmlDoc = DataConverter::exportXML($what);
		$str = $xmlDoc->saveXML(null, LIBXML_NOENT);
		//    set_error_handler('HandleXmlError');
		//    $doc = new DOMDocument('1.0','utf-8');
		//    $doc->loadXML($str);
		//    restore_error_handler();
		echo $str;
	}

}
