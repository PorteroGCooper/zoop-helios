<?php

include_once(dirname(__file__) . "/text.php");

/**
 * Password GuiControl... This one is basically a 'text' GuiControl, because a
 * button is an <input> of type 'password'. Nothing to see here...
 *
 * @ingroup gui
 * @ingroup GuiControl
 * @see TextControl
 */
class PasswordControl extends TextControl {
	function __construct($var) {
		parent::__construct($var);
		$this->type = 'password';
	}
}