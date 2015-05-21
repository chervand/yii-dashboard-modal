<?php

/**
 * Class YDModalContentWidget
 * @author chervand <chervand@gmail.com>
 * todo: not found object
 */
class YDModalContentWidget extends CWidget
{
	public $header;
	public $body;
	public $footer;

	public function init()
	{
		if ($this->header) {
			echo '<div class="modal-header">';
			if (is_callable($this->header)) {
				call_user_func($this->header, [$this]);
			} else {
				echo '<button type="button" class="close" data-dismiss="modal">&times;</button>';
				echo '<h4 class="modal-title">' . $this->header . '</h4>';
			}
			echo '</div>';
		}
		echo '<div class="modal-body">';
		if (is_callable($this->body)) {
			call_user_func($this->body, [$this]);
		} else {
			echo $this->body;
		}
	}

	public function run()
	{
		echo '</div>';
		if ($this->footer) {
			echo '<div class="modal-footer">';
			if (is_callable($this->footer)) {
				call_user_func($this->footer, [$this]);
			} else {
				echo $this->footer;
			}
			echo '</div>';
		}
	}
}
