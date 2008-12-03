<?php

include_once(dirname(__file__) . "/select.php");

/**
 * MultiSelect GuiControl
 *
 * @ingroup gui
 * @ingroup GuiControl
 * @see SelectControl
 */
class MultipleControl extends SelectControl {
	protected function render() {
		$this->params['multiple'] = 1;
		if (!isset($this->params['size'])) {
			$this->params['size'] = 4;
		}
		return parent::render();
	}
}