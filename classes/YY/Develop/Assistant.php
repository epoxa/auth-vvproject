<?php

namespace YY\Develop;

use Exception;
use YY\Core\Cache;
use YY\Core\Data;
use YY\Core\Exporter;
use YY\Core\Importer;
use YY\Develop\Builder;
use YY\System\Robot;
use YY\System\YY;

/**
 * @property Data world
 */
class Assistant extends Robot
{

	public function __construct($init)
	{

		parent::__construct($init);

		$this->builder = null;
		$this['otherYYID'] = null;

		$this->god = array(
			'mode' => false,
			'submode' => null,
			'file' => null,
		);

	}

	public function _PAINT()
	{
//		if (!YY::$CURRENT_VIEW['secure']) {
//			YY::redirectUrl('https://' . ROOT_URL);
//		}
//
		// Аутентификация

//		if (IS_LOCAL_DEVEL) {
//			YY::$WORLD['godId'] = YY::$ME['id']; // Опять изменение данных в отрисовке? Ай-яй-яй!
//		}
//
//		if (!isset(YY::$WORLD['godId']) || YY::$WORLD['godId'] != YY::$ME['id']) {
//			echo YY::drawText(null, 'Скажи пароль');
//			$this['password'] = '';
//			echo YY::drawInput(null, $this, 'password');
//			echo YY::drawCommand(null, 'Вход', $this, 'login');
//			echo '&nbsp;';
//			echo YY::drawCommand(null, "Отмена", $this, "closeWorldAssistant");
//			return;
//		}

		// Системный редактор

		$sync = 'Sync';
		if (isset(YY::$WORLD['configModified'])) $sync = '<span style="color:red">' . $sync . '</span>';
		echo '<div style="background-color: silver; border-bottom: 1px solid gray; height: 22px">'
			. $this->HUMAN_COMMAND(null, '&nbsp;' . ($this->god->mode ? "&uarr;" : "&darr;") . '&nbsp;', "sys")
			. (IS_LOCAL_DEVEL ? 'my-project' : 'vvproject')
			. '&nbsp;|&nbsp;' . $this->HUMAN_COMMAND(null, $sync, "synchronizeConfig")
			. '&nbsp;|&nbsp;' . $this->HUMAN_COMMAND(null, "Kill me", "killMe")
			. '&nbsp;|&nbsp;' . $this->HUMAN_COMMAND(null, "Restart", "recreateWorld")
			. '&nbsp;|&nbsp;' . $this->HUMAN_COMMAND(null, "Exit", "closeWorldAssistant")
			. '</div>';
		if (isset($this['message'])) {
			echo '<div style="background-color: yellow; border-bottom: 1px solid gray">'
				. $this->HUMAN_COMMAND(null, '<sup style:"float: right">&times;</sup>', 'closeMessage')
				. $this->MY_TEXT(null, $this['message'])
				. '</div><hr/>';
		}
		if ($this->god->mode) {
			if (Data::_isEqual($this->builder, $this->god->curatorBuilder)) {
				echo htmlspecialchars('<Я>') . ' ';
			} else {
				echo $this->HUMAN_COMMAND(null, htmlspecialchars('<Я>'), 'edit_life') . ' ';
			}
			if (Data::_isEqual($this->builder, $this->god->worldBuilder)) {
				echo htmlspecialchars('<Мир>') . ' ';
			} else {
				echo $this->HUMAN_COMMAND(null, htmlspecialchars('<Мир>'), 'edit_world') . ' ';
			}
			if (Data::_isEqual($this->builder, $this->god->otherBuilder) || $this->builder == null) {
				echo htmlspecialchars('<Другое>') . ' ';
				if ($this->builder != null) {
					echo $this->HUMAN_COMMAND(null, $this->builder['root']['world'], 'change_other') . ' ';
				}
			} else {
				echo $this->HUMAN_COMMAND(null, htmlspecialchars('<Другое>'), 'edit_other') . ' ';
			}
			echo "</div>";
			if ($this->builder) {
				$this->builder->_SHOW();
			} else { // Режим выбора произвольного объекта
				echo "<br>YYID: " . $this->HUMAN_TEXT(null, 'otherYYID');
				echo $this->HUMAN_COMMAND(null, 'OK', 'change_other_ok');
			}
		}
	}

	///////////////////////////////////////////////////////////////////
	//
	// Общие команды
	//
	///////////////////////////////////////////////////////////////////

	public function login($params)
	{
		$password = $this['password'];
		unset($this['password']);
		if (YY::Config('user')->checkMasterPassword(['password' => $password])) {
			YY::$WORLD['godId'] = YY::$ME['id'];
		}
	}

	public function closeMessage()
	{
		unset($this['message']);
	}

	public function sys()
	{
		$this->builder = null;
		if ($this->god->mode) {
			$this->god->mode = false;
			$this->god->curatorBuilder = null;
			$this->god->worldBuilder = null;
			$this->god->otherBuilder = null;
		} else {
			$this->god->worldBuilder = new Builder(array('world' => YY::$WORLD, 'isWorld' => true));
			$this->god->curatorBuilder = new Builder(array('world' => YY::$ME));
			$this->god->curatorBuilder->anchor = $this->god->worldBuilder->anchor;
			$this->god->otherBuilder = null;
			$this->god->mode = true;
		}
	}

	public function killMe()
	{
//        unset(YY::$ME['CURRENT_MAIN_CURATOR'], YY::$ME['CURRENT_MAIN_SCENERY']);
		YY::$ME->_delete();
		YY::$CURRENT_VIEW = null;
		Cache::Flush();
		YY::redirectUrl('/build');
	}


	public function recreateWorld()
	{
		YY::$WORLD['SYSTEM']->restartWorld();
	}


	public function closeWorldAssistant()
	{
		unset(YY::$ME['CURRENT_MAIN_CURATOR'], YY::$ME['CURRENT_MAIN_SCENERY']);
		YY::redirectUrl('/', true);
	}

	///////////////////////////////////////////////////////////////////
	//
	// Переключение между разными деревьями (инкарнация, мир, произвольное поддерево)
	//
	///////////////////////////////////////////////////////////////////

	public function edit_life()
	{
		$this->builder = $this->god->curatorBuilder;
	}

	public function edit_world()
	{
		$this->builder = $this->god->worldBuilder;
	}

	public function edit_other()
	{
		$this->builder = $this->god->otherBuilder;
	}

	public function change_other()
	{
		if ($this->god->otherBuilder) {
			$yyid = $this->god->otherBuilder['root']['world']->_YYID;
		} else {
			$yyid = null;
		}
		$this->god->otherBuilder = null;
		$this->god->builder = null;
		$this['otherYYID'] = $yyid;
	}

	public function change_other_ok()
	{
		$other = Data::_load($this['otherYYID']);
		if ($other) {
			$this->god->otherBuilder = new Builder(array('world' => $other));
			$this->god->otherBuilder->anchor = $this->god->worldBuilder->anchor;
			$this->builder = $this->god->otherBuilder;
		}
	}

	///////////////////////////////////////////////////////////////////
	//
	// Синхронизация сценария с конфигурацией
	//
	///////////////////////////////////////////////////////////////////


	private static function getLastTime($dir)
	{
		$maxTime = filemtime($dir);
		$files = array_diff(scandir($dir), array('.', '..'));
		foreach ($files as $file) {
			$curTime = is_dir("$dir/$file") ? self::getLastTime("$dir/$file") : filemtime("$dir/$file");
			if ($curTime > $maxTime) $maxTime = $curTime;
		}
		return $maxTime;
	}

	private static function delTree($dir)
	{
		$files = array_diff(scandir($dir), array('.', '..'));
		foreach ($files as $file) {
			$result = is_dir("$dir/$file") ? self::delTree("$dir/$file") : unlink("$dir/$file");
			if (!$result) return false;
		}
		return rmdir($dir);
	}

	public function synchronizeConfig($params)
	{
		if (isset($params['force'])) {

			$load = $params['force'] === 'load';
			$runtimeChanged = !$load;
			$scriptChanged = $load;

		} else {

			$runtimeChanged = isset(YY::$WORLD['configModified']);
			$scriptChanged = self::getLastTime(CONFIGS_DIR . '.current') > YY::$WORLD->configTimestamp;

		}

		if ($runtimeChanged && $scriptChanged) {

			$this['message']
				= 'Update collision. Runtime and script both modified.<br>'
				. $this->HUMAN_COMMAND(null, 'Save to disk', 'synchronizeConfig', array('force' => 'save'))
				. '<br>'
				. $this->HUMAN_COMMAND(null, 'Revert from disk', 'synchronizeConfig', array('force' => 'load'));
			return;

		} else if ($runtimeChanged) {

			if (file_exists(CONFIGS_DIR . '.new') && !self::delTree(CONFIGS_DIR . '.new')) {
				$this['message'] = 'Can not delete .new folder';
				return;
			}
			$saved = new Data();
			foreach (YY::$WORLD as $prop => $val) {
				if (!in_array($prop, ['CONFIG', 'SYSTEM', 'SERVERS'])) {
					$saved[$prop] = YY::$WORLD->_DROP($prop);
				}
			}
			foreach ($saved->_all_keys() as $prop) {
				unset(YY::$WORLD[$prop]);
			}
			ob_start();
			Exporter::exportSubtree(YY::$WORLD, CONFIGS_DIR . '.new');
			$output = ob_get_clean();
			foreach ($saved->_all_keys() as $prop) {
				YY::$WORLD[$prop] = $saved->_DROP($prop);
			}
			$saved->_CLEAR();
			if ($output) {
				YY::Log('error', $output);
				$this['message'] = $output;
				return;
			}
			if (file_exists(CONFIGS_DIR . '.backup') && !self::delTree(CONFIGS_DIR . '.backup')) {
				$this['message'] = 'Can not delete .backup';
				return;
			};
			if (!rename(CONFIGS_DIR . '.current', CONFIGS_DIR . '.backup')) {
				$this['message'] = 'Can not rename .current to .backup';
				return;
			}
			if (!unlink(CONFIGS_DIR . '.new/new.php')) {
				$this['message'] = 'Can not delete .new/new.php';
				return;
			}
			if (!rename(CONFIGS_DIR . '.new', CONFIGS_DIR . '.current')) {
				throw new Exception('Can not rename .new to .current');
			}
			unset(YY::$WORLD['configModified']);

		} else if ($scriptChanged) {

			Importer::reloadWorld();

		}
		$this->closeMessage();
	}

	///////////////////////////////////////////////////////////////////
	//
	// Не пойми что
	//
	///////////////////////////////////////////////////////////////////


	public function upload()
	{
//		if ($this->god->submode == 'uploading') {
//			if (isset($this->god['file'])) {
//				$file = $this->god['file'];
//				$loadedData = DataConverter::importXML($file, $this->builder->message);
//				if ($loadedData) {
//					$world = $this->builder->root->world;
//					$keys = [];
//					foreach ($world as $prop => $val) {
//						$keys[] = $prop;
//					}
//					foreach ($keys as $key) {
//						unset($world[$key]);
//					}
//					$keys = [];
//					foreach ($world->_object_keys() as $prop) {
//						$keys[] = $prop;
//					}
//					foreach ($keys as $key) {
//						unset($world[$key]);
//					}
//					foreach ($loadedData as $prop => $val) {
//						$world[$prop] = $loadedData->_DROP($prop);
//					}
//					foreach ($loadedData->_object_keys() as $prop) {
//						$world[$prop] = $loadedData->_DROP($prop);
//					}
//					try { // На случай загрузки мира
//						$this->builder->message->show("Файл загружен.");
//					} catch (Exception $e) {
//					}
//				} else {
//					$this->builder->message->show("Неправильный формат файла.");
//				}
//				$this->god['file'] = null;
//				$this->god->submode = null;
//			} else {
//				$this->builder->message->show("Файл не выбран.");
//			}
//		} else {
//			$this->god->submode = 'uploading';
//			$this->god['file'] = null;
//		}
	}

}
