import 'jquery-validation';
import 'jquery-validation/dist/additional-methods';
import 'jquery-validation/dist/localization/messages_fr';
import 'intl-tel-input';
import 'intl-tel-input/build/js/utils';
	
$.fn.hasValidator = function(){
	return this.getValidator() ? true : false;
};
$.fn.getValidator = function(createIfNotExists,options){
	let input = $(this);
	let form = input.closest('form');
	let validator;
	if(form.length){
		validator = form.data('validator');
		if(createIfNotExists&&!validator){
			form.validate(options || {});
		}
	}
	else{
		if(createIfNotExists){
			if(console){
				console.log(this);
			}
			throw new Error("this element doesn't have parent form");
		}
	}
	return validator;
};
$.fn.isValid = function(){
	let valid = true;
	this.each(function(){
		let input = $(this);
		let form = input.closest('form');
		let validator = input.getValidator();
		if(form.length){
			validator = form.data('validator');
		}
		if(!validator){
			if(!input.is(':valid')){
				valid = false;
				return false;
			}
			return;
		}
		
		let element = validator.validationTargetFor(this);
		if(!element) return;
		
		let rules = $( element ).rules(),
			rulesCount = $.map( rules, function( n, i ) {
				return i;
			} ).length,
			dependencyMismatch = false,
			val = validator.elementValue( element ),
			result, method, rule;

		if ( typeof rules.normalizer === "function" ) {
			val = rules.normalizer.call( element, val );
			if ( typeof val !== "string" ) {
				throw new TypeError( "The normalizer should return a string value." );
			}
			delete rules.normalizer;
		}

		for ( method in rules ) {
			rule = { method: method, parameters: rules[ method ] };
			result = $.validator.methods[ method ].call( validator, val, element, rule.parameters );
			if( !result ){
				valid = false;
				return false;
			}
		}
	});
	return valid;
};

$.validator.setDefaults({
	errorClass: 'help-block with-errors validation-error',
	highlight: function(element) {
		$(element).closest('.form-group').removeClass('has-success').addClass('has-error');
	},
	success: function(element) {
		$(element).closest('.form-group').removeClass('has-error').addClass('has-success');
	},
	errorPlacement: function(error, element) {
		var container = element.closest('.form-group');
		if(container.length){
			container.append(error);
		}
		else{
			error.insertAfter(element);
		}
	}
});
var resetForm = $.validator.prototype.resetForm;
$.validator.prototype.resetForm = function(){
	$(this.currentForm).find('.form-group').removeClass('has-error').removeClass('has-success');
	return resetForm.apply(this,arguments);
};

$.validator.addMethod('specificRemote',function( value, element, param ) {
	if ( this.optional( element ) ) {
		return "dependency-mismatch";
	}
	
	var
		validator = this,
		previous = $.data( element, "previousValue" ) || $.data( element, "previousValue", {
			old: null,
			valid: true,
			message: null,
			messages: this.defaultMessage( element, { method: 'specificRemote', parameters: value } )
		})
	;

	if (!this.settings.messages[ element.name ] ) {
		this.settings.messages[ element.name ] = {};
	}
	previous.originalMessage = this.settings.messages[ element.name ].specificRemote;
	this.settings.messages[ element.name ].specificRemote = previous.message;

	if ( previous.old === value ) {
		return previous.valid;
	}

	previous.old = value;
	this.startRequest( element );
	param = typeof param === "string" && { url: param } || param;
	
	if(typeof(param.data)=="undefined"){
		param.data = {};
		param.data[element.name] = value;
	}
	else if(typeof(param.data)=="function"){
		param.dataGen = param.data;
	}
	if(typeof(param.dataGen)=="function"){
		param.data = param.dataGen(value,element.name);
	}
	
	$.ajax($.extend(true,{
		mode: "abort",
		port: 'validate' + element.name,
		dataType: "json",
		context: validator.currentForm,
		success: function( response ) {
			var valid = response === true || response === "true",
				errors, message, submitted;

			validator.settings.messages[ element.name ].specificRemote = previous.originalMessage;
			if ( valid ) {
				submitted = validator.formSubmitted;
				validator.prepareElement( element );
				validator.formSubmitted = submitted;
				validator.successList.push( element );
				delete validator.invalid[ element.name ];
				validator.showErrors();
			} else {
				errors = {};
				message = previous.messages[response];
				errors[ element.name ] = previous.message = $.isFunction( message ) ? message( value ) : message;
				validator.invalid[ element.name ] = true;
				validator.showErrors( errors );
			}
			previous.valid = valid;
			validator.stopRequest( element, valid );
		}
	},param));
	return "pending";
});

$.validator.addMethod('intltel',function( value, element, param ) {
	if(!value){
		return true;
	}
	element = $(element);
	if(typeof($.fn.intlTelInput)!='function'){
		throw "you have to include intlTelInput library to use intltel validation rule";
	}
	return element.intlTelInput('isValidNumber');
},"Numéro de téléphone incorrect");

$.validator.addMethod('uniqselection',function( value, element, param ) {
	if(!value){
		return true;
	}
	var form = $(element).closest('form');
	var collection = $('[data-rule-uniqselection="'+param+'"]',form);
	
	var ok = true;
	collection.each(function(){
		if(element!==this){
			var self = $(this);
			if(self.val()==value){
				ok = false;
			}
			//if(!$(element).data('uniqselection-iterated')){
				//self.data('uniqselection-iterated',true);
				//self.valid();
				//self.data('uniqselection-iterated',null);
			//}
		}
	});
	
	return ok;
	
},"Déjà utilisé");

export default $.validator;
