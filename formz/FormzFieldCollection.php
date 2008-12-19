<?php

/**
 * Formz Field Collection object.
 *
 * This is a collection of FormzField objects, returned from a $formz->fields() call... Don't create these
 * all by yourself, as they're largely useless :)
 *
 * @ingroup formz
 * @ingroup formzfield
 *
 * @author Justin Hileman {@link http://justinhileman.com}
 *
 * @see Formz::fields
 */
class FormzFieldCollection {

	private $form;
	private $fields = array();

	/**
	 * FormzFieldCollection constructor.
	 *
	 * Don't call this constructor directly, access it via calls to $formz->fields(array('foo', 'bar'))
	 *
	 * @param string $name
	 * @param Formz $form
	 * @return FormzField
	 */
	function __construct($fieldnames, &$form) {
		$this->form = $form;
		
		foreach ($fieldnames as $name) {
			$this->fields[] = $this->form->field($name);
		}
		return $this;
	}

	/**
	 * Call specified method on each FormzField in this collection.
	 *
	 * @access public
	 * @param string $method
	 * @param array $args
	 * @return FormzFieldCollection $this, for chaining field manipulation actions.
	 */
	function __call($method, $args) {
		// apply this call to all fields in this collection.
		foreach($this->fields as $field) {
			call_user_func_array(array($field, $method), $args);
		}
		
		return $this;
	}
}