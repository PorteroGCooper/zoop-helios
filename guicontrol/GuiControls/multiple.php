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
		$this->setParam('multiple', true);
		if (!$this->getParam('size')) {
			$this->setParam('size', 4);
		}
		return parent::render();
	}
}