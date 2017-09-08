<?php
namespace YY\Develop;

use YY\System\Robot;

/**
 * @property string message
 */
class MessageBox extends Robot
{

	public function _PAINT()
	{
		if (isset($this['message']) && $this['message'] !== null) {
			echo '<div>' . $this->message . '</div>';
			echo $this->HUMAN_COMMAND(null, 'Хорошо!', 'close_message');
			echo "<hr/>";

		}
	}

	public function show($messageText)
	{
		$message = '';
		if (isset($this['message'])) $message = $this->message;
		$message .= htmlspecialchars($messageText) . '<br/>';
		$this->message = $message;
	}

	public function close_message()
	{
		$this->message = null;
	}

}
