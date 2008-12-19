<?php

/**
 * Formz Field objects.
 *
 * @group formzfield
 *
 * FormzField objects are returned by calls to $formz->field('fieldname'); These objects
 * exist to simplify performing multiple formz actions on the same field.
 *
 * @code
 *    $this->form->addField('Google')
 *       ->setDisplayOverride('Google this fool.')
 *       ->setListshow(true)
 *       ->setLabel('This is fake')
 *       ->setListlink('http://google.com/search?q=%name%');
 *    $this->form->field('name')
 *       ->
 * @endcode
 *
 * @endgroup
 *
 * @ingroup formz
 * @ingroup formzfield
 *
 * @author Justin Hileman {@link http://justinhileman.com}
 *
 * @see Formz::field
 * @see Formz::addField
 * @see Formz::addAggregateField
 */
class FormzField {

	private $form;
	private $name;

	/**
	 * FormzField constructor.
	 *
	 * Don't call this constructor directly, access it via calls to $formz->field('name')
	 * or $formz->addField('newfield');
	 *
	 * @param string $name
	 * @param Formz $form
	 * @return FormzField
	 */
	function __construct($name, &$form) {
		$this->name = $name;
		$this->form = $form;
	}

	/**
	 * Wrapper function for Formz::setFieldConstraint(), since that function takes a different
	 * set of arguments and isn't easily handled by the __call() magic method which handles
	 * all of the setFoo and setDisplayFoo calls.
	 *
	 * @access public
	 * @param string $value Value (or array of values) with which to constrain this field.
	 * @param bool $is_fixed True if this value cannot be changed (default, only logical use case).
	 * @return FormzField $this, for chaining field manipulation actions.
	 */
	function setConstraint($value, $is_fixed = true) {
		$this->form->setFieldConstraint($this->name, $value, $is_fixed);
		return $this;
	}

	/**
	 * Magic method for thunking FormzField methods into Formz setFieldParam magic method calls...
	 *
	 * @access public
	 * @param string $method
	 * @param array $args
	 * @return FormzField $this, for chaining field manipulation actions.
	 */
	function __call($method, $args) {
		array_unshift($args, $this->name);
		
		if (substr($method, 0, 10) == 'setDisplay') {
			array_unshift($args, lcfirst(substr($method, 10)));
			$function = 'setFieldDisplay';
		} else if (substr($method, 0, 3) == 'set') {
			array_unshift($args, lcfirst(substr($method, 3)));
			$function = 'setFieldParam';
		} else {
			trigger_error($method . " method undefined on Formz Field object.");
		}
		
		call_user_func_array(array($this->form, $function), $args);
		return $this;
	}
}