/*
 * Really easy field validation with Prototype
 * http://tetlaw.id.au/view/blog/really-easy-field-validation-with-prototype
 * Author: Andrew Tetlaw
 * Contributor: Steve Francia
 * Version 1.6 (2006-09-28)
 *
 * Copyright (c) 2006 Andrew Tetlaw and contributors
 * http://www.opensource.org/licenses/mit-license.php
 */
Validator = Class.create();

Validator.prototype = {
	initialize : function(className, error, test, options) {
		this.options = Object.extend({}, options || {});
		this._test = test ? test : function(v,elm){ return true };
		this.error = error ? error : 'Validation failed.';
		this.className = className;
	},
	test : function(v, elm) {
		return this._test(v,elm);
	}
}

var Validation = Class.create();

Validation.prototype = {
	initialize : function(form, options){
		this.options = Object.extend({
			onSubmit : true,
			stopOnFirst : false,
			immediate : false,
			focusOnError : true,
			useTitles : false,
			onFormValidate : function(result, form) {},
			onElementValidate : function(result, elm) {}
		}, options || {});
		this.form = $(form);
		if(this.options.onSubmit) Event.observe(this.form,'submit',this.onSubmit.bind(this),false);
		if(this.options.immediate) {
			var useTitles = this.options.useTitles;
			var callback = this.options.onElementValidate;
			Form.getElements(this.form).each(function(input) { // Thanks Mike!
				Event.observe(input, 'blur', function(ev) { Validation.validate(Event.element(ev),{useTitle : useTitles, onElementValidate : callback}); });
			});
		}
	},
	onSubmit :  function(ev){
		if(!this.validate()) Event.stop(ev);
	},
	validate : function() {
		var result = false;
		var useTitles = this.options.useTitles;
		var callback = this.options.onElementValidate;
		if(this.options.stopOnFirst) {
			result = Form.getElements(this.form).all(function(elm) { return Validation.validate(elm,{useTitle : useTitles, onElementValidate : callback}); });
		} else {
			result = Form.getElements(this.form).collect(function(elm) { return Validation.validate(elm,{useTitle : useTitles, onElementValidate : callback}); }).all();
		}
		if(!result && this.options.focusOnError) {
			Form.getElements(this.form).findAll(function(elm){return $(elm).hasClassName('validation-failed')}).first().focus()
		}
		this.options.onFormValidate(result, this.form);
		return result;
	},
	reset : function() {
		Form.getElements(this.form).each(Validation.reset);
	}
}

Object.extend(Validation, {
	validate : function(elm, options){
		options = Object.extend({
			useTitle : false,
			onElementValidate : function(result, elm) {}
		}, options || {});
		elm = $(elm);
		var cn = elm.classNames();
		return result = cn.all(function(value) {
			var test = Validation.test(value,elm,options.useTitle);
			options.onElementValidate(test, elm);
			return test;
		});
	},
	test : function(name, elm, useTitle) {
		var v = Validation.get(name);
		var prop = '__advice'+name.camelize();
		if(Validation.isVisible(elm) && !v.test($F(elm), elm)) {
			if(!elm[prop]) {
				var advice = Validation.getAdvice(name, elm);
				if(typeof advice == 'undefined') {
					var errorMsg = useTitle ? ((elm && elm.title) ? elm.title : v.error) : v.error;
					advice = '<div class="validation-advice" id="advice-' + name + '-' + Validation.getElmID(elm) +'" style="display:none">' + errorMsg + '</div>'
					switch (elm.type.toLowerCase()) {
						case 'checkbox':
						case 'radio':
							var p = elm.parentNode;
							if(p) {
								new Insertion.Bottom(p, advice);
							} else {
								new Insertion.After(elm, advice);
							}
							break;
						default:
							new Insertion.After(elm, advice);
				    }
					advice = $('advice-' + name + '-' + Validation.getElmID(elm));
				}
				if(typeof Effect == 'undefined') {
					advice.style.display = 'block';
				} else {
					new Effect.Appear(advice, {duration : 1 });
				}
			}
			elm[prop] = true;
			elm.removeClassName('validation-passed');
			elm.addClassName('validation-failed');
			return false;
		} else {
			var advice = Validation.getAdvice(name, elm);
			if(typeof advice != 'undefined') advice.hide();
			elm[prop] = '';
			elm.removeClassName('validation-failed');
			elm.addClassName('validation-passed');
			return true;
		}
	},
	isVisible : function(elm) {
		while(elm.tagName != 'BODY') {
			if(!$(elm).visible()) return false;
			elm = elm.parentNode;
		}
		return true;
	},
	getAdvice : function(name, elm) {
		return Try.these(
			function(){ return $('advice-' + name + '-' + Validation.getElmID(elm)) },
			function(){ return $('advice-' + Validation.getElmID(elm)) }
		);
	},
	getMax : function(elm) {
		return Try.these(
			function(){ return Number($('max-' + Validation.getElmID(elm)).innerHTML)}
		);
	},
	getMin : function(elm) {
		return Try.these(
			function(){ return Number($('min-' + Validation.getElmID(elm)).innerHTML)}
		);
	},
	getParams : function(elm) {
		return Try.these(
			function(){ return $('params-' + Validation.getElmID(elm)).innerHTML.split("|")}
		);
	},
	getParam : function(elm) {
		return Try.these(
			function(){ return $('params-' + Validation.getElmID(elm)).innerHTML}
		);
	},
	getElmID : function(elm) {
		return elm.id ? elm.id : elm.name;
	},
	reset : function(elm) {
		elm = $(elm);
		var cn = elm.classNames();
		cn.each(function(value) {
			var prop = '__advice'+value.camelize();
			if(elm[prop]) {
				var advice = Validation.getAdvice(value, elm);
				advice.hide();
				elm[prop] = '';
			}
			elm.removeClassName('validation-failed');
			elm.removeClassName('validation-passed');
		});
	},
	add : function(className, error, test, options) {
		var nv = {};
		nv[className] = new Validator(className, error, test, options);
		Object.extend(Validation.methods, nv);
	},
	addAllThese : function(validators) {
		var nv = {};
		$A(validators).each(function(value) {
				nv[value[0]] = new Validator(value[0], value[1], value[2], (value.length > 3 ? value[3] : {}));
			});
		Object.extend(Validation.methods, nv);
	},
	get : function(name) {
		return  Validation.methods[name] ? Validation.methods[name] : new Validator();
	},
	methods : {}
});

Validation.add('IsEmpty', '', function(v) {
				return  ((v == null) || (v.length == 0)); // || /^\s+$/.test(v));
			});

Validation.add('GreaterThan', 'Your Number is too small.', function(v, elm) {
				var min = Validation.getMin(elm);
				if (!min){ return true; }
				return  (min.toInt <= v.toInt);
			});

Validation.add('LessThan', 'Your Number is too large.', function(v, elm) {
				var max = Validation.getMax(elm);
				if (max == null){ return true; }
				return  (max >= v);
			});

Validation.add('Between', 'Your number is too small or too large.', function(v, elm) {
				var min = Validation.getMin(elm);
				var max = Validation.getMax(elm);
				if (!min && !max){ return true; }
				if (!max) { return (min <= v)}
				if (!min) { return (max >= v)}
				return  (min <= v && v <= max);
			});

Validation.add('Length', 'Please enter the correct length.', function(v, elm) {
				var min = Validation.getMin(elm);
				var max = Validation.getMax(elm);
				return  (min <= v.length && v.length <= max );
			});

Validation.add('EqualTo', 'Does not match.', function(v, elm) {
				var params = Validation.getParams(elm);
				var otherField = String(params[0]);
				return  (v == $(otherField).value);
			});
Validation.add('validate-regexp', 'Please enter a valid format.', function(v, elm) {
				var x =  new RegExp(Validation.getParam(elm));
				return Validation.get('IsEmpty').test(v) ||  x.test(v);
			});

Validation.addAllThese([
	['required', 'This is a required field.', function(v) {
				return !Validation.get('IsEmpty').test(v);
			}],
	['validate-number', 'Please enter a valid number in this field.', function(v, elm) {
				return Validation.get('IsEmpty').test(v) || (!isNaN(v) && !/^\s+$/.test(v));
			}],
	['validate-digits', 'Please use numbers only in this field. please avoid spaces or other characters such as dots or commas.', function(v) { // SHOULD BE CALLED validate-int
				return Validation.get('IsEmpty').test(v) ||  !/[^\d]/.test(v);
			}],
	['validate-alpha', 'Please use letters only (a-z) in this field.', function (v) {
				return Validation.get('IsEmpty').test(v) ||  /^[a-zA-Z]+$/.test(v)
			}],
	['validate-alphanum', 'Please use only letters (a-z) or numbers (0-9) only in this field. No spaces or other characters are allowed.', function(v) {
				return Validation.get('IsEmpty').test(v) ||  !/\W/.test(v)
			}],
	['validate-date', 'Please enter a valid date.', function(v) {
				var test = new Date(v);
				return Validation.get('IsEmpty').test(v) || !isNaN(test);
			}],
	['validate-email', 'Please enter a valid email address. For example fred@domain.com .', function (v) {
				return Validation.get('IsEmpty').test(v) || /\w{1,}[@][\w\-]{1,}([.]([\w\-]{1,})){1,3}$/.test(v)
			}],
	['validate-ip', 'Please enter a valid IP address. For example 127.0.0.1 .', function (v) {
				return Validation.get('IsEmpty').test(v) || /^(?:\b(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\b)$/.test(v)
			}],
	['validate-domain', 'Please enter a valid Domain ie : supernerd.com .', function (v) {
				return Validation.get('IsEmpty').test(v) || /^[A-Z0-9._%-]+\.[A-Z]{2,4}$/i.test(v)
			}],
	['validate-password', 'Please enter a secure Password ( 1 UC, 1 LC, 1 Num and longer than 6 characters. ) .', function (v) {
				return Validation.get('IsEmpty').test(v) || /^(?=[-_a-zA-Z0-9]*?[A-Z])(?=[-_a-zA-Z0-9]*?[a-z])(?=[-_a-zA-Z0-9]*?[0-9])[-_a-zA-Z0-9]{6,}$/.test(v)
			}],
	['validate-ssn', 'Please enter a valid Social Security Number. ', function (v) {
				return Validation.get('IsEmpty').test(v) || /^[0-9]{3}-[0-9]{2}-[0-9]{4}$/.test(v)
			}],
	['validate-zip', 'Please enter a valid US Zip Code. ', function (v) { // WORKS WITH US ONLY
				return Validation.get('IsEmpty').test(v) || /^[0-9]{5}(?:-[0-9]{4})?$/.test(v)
			}],
	['validate-postal', 'Please enter a valid Zip or Postal Code. ', function (v) { // WORKS WITH US, CAN AND UK
				return Validation.get('IsEmpty').test(v) || /^[0-9]{5}(?:-[0-9]{4})?$/.test(v) || /^[ABCEGHJKLMNPRSTVXY][0-9][A-Z] [0-9][A-Z][0-9]$/i.test(v) || /^[A-Z]{1,2}[0-9][A-Z0-9]? [0-9][ABD-HJLNP-UW-Z]{2}$/i.test(v)
			}],
	['validate-cc', 'Please enter a valid Credit Card Number. ', function (v) {
				return Validation.get('IsEmpty').test(v) || /^(?:4[0-9]{12}(?:[0-9]{3})?|5[1-5][0-9]{14}|6011[0-9]{14}|3(?:0[0-5]|[68][0-9])[0-9]{11}|3[47][0-9]{13})$/.test(v)
			}],
	['validate-url', 'Please enter a valid URL.', function (v) {
				return Validation.get('IsEmpty').test(v) || /^(http|https|ftp):\/\/(([A-Z0-9][A-Z0-9_-]*)(\.[A-Z0-9][A-Z0-9_-]*)+)(:(\d+))?\/?/i.test(v)
			}],
	['validate-date-au', 'Please use this date format: dd/mm/yyyy. For example 17/03/2006 for the 17th of March, 2006.', function(v) {
				if(Validation.get('IsEmpty').test(v)) return true;
				var regex = /^(\d{2})\/(\d{2})\/(\d{4})$/;
				if(!regex.test(v)) return false;
				var d = new Date(v.replace(regex, '$2/$1/$3'));
				return ( parseInt(RegExp.$2, 10) == (1+d.getMonth()) ) &&
							(parseInt(RegExp.$1, 10) == d.getDate()) &&
							(parseInt(RegExp.$3, 10) == d.getFullYear() );
			}],
	['validate-currency-dollar', 'Please enter a valid $ amount. For example $100.00 .', function(v) {
				// [$]1[##][,###]+[.##]
				// [$]1###+[.##]
				// [$]0.##
				// [$].##
				return Validation.get('IsEmpty').test(v) ||  /^\$?\-?([1-9]{1}[0-9]{0,2}(\,[0-9]{3})*(\.[0-9]{0,2})?|[1-9]{1}\d*(\.[0-9]{0,2})?|0(\.[0-9]{0,2})?|(\.[0-9]{1,2})?)$/.test(v)
			}],
	['validate-one-required', 'Please select one of the above options.', function (v,elm) {
				var p = elm.parentNode;
				var options = p.getElementsByTagName('INPUT');
				return $A(options).any(function(elm) {
					return $F(elm);
				});
			}]
]);
