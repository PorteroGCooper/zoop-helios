<?php

class FormzField {

	private $form;
	private $name;

	function __construct($name, &$form) {
		$this->name = $name;
		$this->form = $form;
	}

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