<?php

include_once(dirname(__file__) . "/text.php");

/**
 * Password GuiControl... This one is basically a 'text' GuiControl, because a
 * password field is an <input> of type 'password'. Nothing to see here...
 *
 * @ingroup gui
 * @ingroup GuiControl
 * @see TextControl
 */
class PasswordControl extends TextControl {
	function initControl() {
		$this->type = 'password';
	}
	
	/**
	 * A 'password' form element shouldn't show on a read page or a list, unless
	 * it is specifically asked for. DANGER!
	 *
	 * @return string View state for password GuiControls
	 */
	function view() {
		if ($this->getParam('show_password') || Config::get('zoop.guicontrol.enable_show_passwords')) {
			return parent::view();
		} else {
			return '********';
		}
	}
}