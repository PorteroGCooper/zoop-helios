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
	 * Return this field's name.
	 *
	 * @return string Field name
	 */
	function name() {
		return $this->name;
	}
	
	function field($name) {
		return $this->form->field($name);
	}
	
	function fields() {
		$args = func_get_args();
		return call_user_func_array(array($this->form, 'fields'), $args);
	}


	/**
	 * Insert this field after $field.
	 *
	 * This is useful when creating fake or aggregate fields, so that a resort isn't necessary.
	 * Simply add the field and call ->after('foo') on the result.
	 *
	 * This call can be chained after any call which returns a FormzField object.
	 *
	 * @code
	 *    $form->addField('fake')
	 *       ->after('name');
	 * @endcode
	 *
	 * @see FormzField::after
	 * @see Formz::addField
	 * @see Formz::setOrder
	 *
	 * @param mixed $field Field or field name to insert this field after.
	 * @return FormzField This field, for chaining calls.
	 */
	function after($field) {
		if ($field instanceof FormzField) {
			$field = $field->name();
		}

		$sort_keys = array();
		foreach (array_keys($this->form->getFields()) as $_val) {
			$sort_keys[] = $_val;
			if ($_val == $field) {
				$sort_keys[] = $this->name;
				break;
			}
		}
		$this->form->setOrder($sort_keys);
		return $this;
	}

	/**
	 * Insert this field after $field.
	 *
	 * This is useful when creating fake or aggregate fields, so that a resort isn't necessary.
	 * Simply add the field and call ->after('foo') on the result.
	 *
	 * This call can be chained after any call which returns a FormzField object.
	 *
	 * @code
	 *    $form->addField('fake')
	 *       ->after('name');
	 * @endcode
	 *
	 * @see FormzField::after
	 * @see Formz::addField
	 * @see Formz::setOrder
	 *
	 * @param mixed $field Field or field name to insert this field after.
	 * @return FormzField This field, for chaining calls.
	 */
	function before($field) {
		if ($field instanceof FormzField) {
			$field = $field->name();
		}
		
		$sort_keys = array();
		foreach (array_keys($this->form->getFields()) as $_val) {
			if ($_val == $field) {
				$sort_keys[] = $this->name;
				break;
			}
			$sort_keys[] = $_val;
		}
		
		$this->form->setOrder($sort_keys);
		return $this;
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
	 * Embed a Formz object in this field.
	 *
	 * Optionally, pass a Formz object to embed. If none is passed, this Formz object will create
	 * a Formz object to embed. This will prob'ly be your best bet.
	 *
	 * @code
	 *    $form->field('user')
	 *       ->setEmbeddedForm();
	 *
	 *    $user_form = new Formz('user');
 	 *    $form->field('user')
	 *       ->setEmbeddedForm($user_form);
	 * @endcode
	 * 
	 * @access public
	 * @param Formz $form. Optionally, pass a Formz object to embed.
	 * @return Formz the newly created embedded Formz object.
	 */
	function setEmbeddedForm($form = true) {
		return $this->form->setFieldEmbeddedForm($this->name, $form);
	}
	
	/**
	 * Set display parameters in one huge chunk. Expects an associative array of parameters to
	 * set on this field's GuiControl.
	 *
	 * @code
	 *    $form->field('name')
	 *       ->setDisplayParams(array(
	 *          'label' => 'User Name',
	 *          'caption' => 'Enter a user name.',
	 *          'type' => 'text'
	 *       ));
	 * @endcode
	 * 
	 * @access public
	 * @param array $params
	 * @return FormzField $this, for chaining field manipulation actions.
	 */
	function setDisplayParams($params) {
		$this->form->setFieldDisplayFromArray($this->name, $params);
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
	
	function __dump() {
		$ret = array();
		$fields = $this->form->getFields();
		if (isset($fields[$this->name])) {
			$ret['field'] = $fields[$this->name];
		}
		$ret['value'] = $this->form->getValue($this->name);

		return $ret;
	}
}