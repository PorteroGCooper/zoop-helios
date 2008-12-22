<?php

include_once(dirname(__file__) . "/text.php");

/**
 * Hidden GuiControl... This one is basically a 'text' GuiControl, because a
 * button is an <input> of type 'hidden'. Nothing to see here...
 *
 * @ingroup gui
 * @ingroup guicontrol
 * @see TextControl
 */
class HiddenControl extends textControl {

	/**
	 * A 'hidden' form element shouldn't show on a Read page.
	 *
	 * @return string View state for hidden GuiControls
	 */
	function view() {
		return '';
	}

}