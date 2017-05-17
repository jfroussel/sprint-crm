import "twitter-bootstrap-wizard";
import "validate";
import "siret.js";

$.getScript("https://js.stripe.com/v2/").then(function(){

    Stripe.setPublishableKey('pk_test_GsignUlPp4WAXvPThWgxGyEw');

	var container = $('#simulator');
    var $form = $('#step5');
	if(!container.length)
		return;

	var corporate_email = '';
	var invoiceRowTemplate = container.find('#invoice-row-template');
	var tbody = container.find('#lead_invoices tbody');
	invoiceRowTemplate.removeAttr('id').detach();
	var leadToken = jstack.url.getParams(document.location.href).lead;
	var leadType = $('body').attr('data-lead-type');
	
	$('#simulator-modal').modal('show');
	$(".formule-selector").click(function(e){
		e.preventDefault();
		$("#simulator-modal").fadeOut('slow');
		$("#simulation-inscription").modal('show');
		var type = $(this).closest('[data-lead-type]').attr('data-lead-type');
		$serviceJSON('lead/simulator','selectLeadType',[leadToken, type]);
	});
	
	var handleRow = function(row){
		
		row.find('input[type="date"]').each(function(){
			var self = $(this);
			self.attr('data-input-type','date').attr('type','text');
			//self.datepicker({
				//onSelect: function(dateText){					
					//self.valid();
				//}
			//});
			self.attr('is','datepicker');
			jstack.compileDom(this, {});
			self.on('dp.change',function(){
				self.valid();
			});
		});
		
		var moneyValidate = new RegExp(/^\d+(?:\.\d{1,2})?$/);
		row.find('.money-field').each(function(){
			var val = '';
			$(this).on('input',function(){
				var v = $(this).val();
				var test = v.replace(',','.').rtrim('.');
				if(v&&!moneyValidate.test(test)){
					$(this).val(val);
				}
				else{
					val = v;
				}
			});
		});

	};
	var setNameFromData = function(row,countRows){
		row.data('mandat-index',countRows);
		row.find('[data-name]').each(function(){
			var name = $(this).attr('data-name');
			name = name.replace('%i',countRows);
			$(this).attr('name',name);
		});
	};
	var addRow = function(){
		var newRow = invoiceRowTemplate.clone();
		var countRows = tbody.find('tr').length+1;
		newRow.find('[data-invoice]').html( countRows );
		setNameFromData(newRow, countRows);
		
		var requiredOnce = newRow.find('[required-once]');
		if(countRows==1){ //au moins une ligne requise
			newRow.find('.remove-lead-invoice').remove();
			requiredOnce.prop('required',true);
		}
		requiredOnce.removeAttr('required-once');
		
		tbody.append(newRow);
		handleRow(newRow);
	};
	addRow();
	container.find("#add_row").click(function(e){
		e.preventDefault();
		addRow();
		return false;
	});
	invoiceRowTemplate.append();
	container.on('click','.remove-lead-invoice',function(){
		$(this).closest('tr').remove();
		
		tbody.find('tr').each(function(index){
			var countRows = index+1;
			$(this).find('[data-invoice]').html( countRows );
			setNameFromData( $(this), countRows );
		});
		
	});

	container.on('click','.lead-upload-doc',function(e){
		e.preventDefault();
		$(this).parent().find('input[type=file]').click();
		return false;
	});

	container.on('click','.input-file-clear',function(e){
		e.preventDefault();
		var line = $(this).closest('tr');
		var input = line.find('input[type=file]');
		input.replaceWith( input.clone(true) );
		line.find('.file-names').empty();
		line.find('.input-file-clear').hide();
		return false;
	});
	container.on('change','input[type=file]',function(){
		var target = $(this).parent().find('.file-names');
		var line = $(this).closest('tr');
		target.empty();
		var rowIndex = line.data('mandat-index');
		var documents = [];
		var files = this.files;
		for(var i=0, l=files.length; i<l; i++){
			var name = files[i].name;
			target.append('<span class="file-name">'+name+' </span>');
			documents.push(name);
		}
		var removeFile = line.find('.input-file-clear');
		if(files.length){
			removeFile.show();
		}
		else{
			removeFile.hide();
		}
		documents = JSON.stringify(documents);
		var hidden = $('<input type="hidden" name="lead_invoice['+rowIndex+'][documents]">');
		hidden.val(documents);
		target.append(hidden);
	});

	var steps = {};
	steps[1] = $('#step1',container);
	steps[2] = $('#step2',container);
	steps[3] = $('#step3',container);
	steps[5] = $('#step5',container);
	var wizard = container.find('#lead-form-wizard');
	
	var finalStep = function(){
		wizard.find('.pagination .previous').hide();
		wizard.find('.tab-nav a[data-toggle="tab"]').attr('disabled','disabled');
		var close = wizard.find('.pagination .finish button');
		close.html('<i class="zmdi zmdi-close"></i> Quitter');
		close.click(function(){
			window.location.href='';
		});
		wizard.bootstrapWizard('show', 5);
	};
	
	var stepSubmitCallback;
	wizard.on('click','.pagination .finish',function(){
		var step = steps[wizardCurrentIndex];
		if(step){
			if(step.valid()){

				var validated = step.find('input[name="validated"]');
				if(!validated.length){
					validated = $('<input type="hidden" name="validated">').appendTo(step);
				}
				validated.val('1').trigger('j:update');
				
				stepSubmitCallback = finalStep;
				
				step.submit();
			}
			return false;
		}
		else{
			jstack.ajax({
				url:'lead/simulator',
				data: {
					method: 'addStep5',
					lead:leadToken
				},
				success: function(){
					finalStep();
				}
			});
		}
	});
	
	//wizard.find('.tab-nav a[data-toggle="tab"]').attr('disabled','disabled'); //lock tabs, comment this line for debug
	var wizardCurrentIndex = 0;
	wizard.bootstrapWizard({
		tabClass: 'fw-nav',
		nextSelector: '.next',
		previousSelector: '.previous',
		onNext: function(tab, navigation, index){			
			if(steps[index]){
				if(steps[index].valid()){
					steps[index].submit();
				}
				return false;
			}
		},
		onTabShow: function(tab, navigation, index) {
			var tabIndex = index+1;
			wizardCurrentIndex = tabIndex;
			wizard.find('.tab-nav [href="#tab'+tabIndex+'"]').removeAttr('disabled'); //unlock tab
			if(wizardFinishOnTab==tabIndex){
				wizard.find('.pagination .next').addClass('hidden');
				wizard.find('.pagination .finish').removeClass('hidden');
			}
			switch(tabIndex){
				case 4:
					jstack.ajax({
						url:'lead/simulator',
						dataType: 'html',
						data: {
							method: 'getTab4',
							params: [leadToken],
						},
						success: function(tab4Content){
							$('#tab4',container).html(tab4Content);
							steps[4] = $('#step4',container);
							if(type_frais){
								steps[4].find('input[name="type_frais"]').val([type_frais]);
							}
							validateCommon(steps[4],{
								messages:{
									cgu_accepted: 'Vous devez accepter les conditions générales de vente',
								}
							},null,4);
						}
					});
				break;
				case 6:
					jstack.ajax({
						url:'lead/simulator',
						dataType: 'html',
						data: {
							method: 'getTab6',
							params: [leadToken],
						},
						success: function(tab4Content){
							var tab = $('#tab6',container);
							tab.html(tab4Content);
							tab.find('.resend-mail').click(function(){
								$serviceJSON('lead/simulator','resendMail',[leadToken]);
							});
						}
					});
				break;
			}
		}
	});
	
	var wizardFinishOnTab = 4;
	var type_frais;
	$('#tab4',container).on('change','input[name="type_frais"]',function(){
		var val = $(this).val();
		type_frais = val;
		if(val=='prepaid'){
			wizard.bootstrapWizard('display', 4);
			wizardFinishOnTab = 5;
			wizard.find('.pagination .finish').addClass('hidden');
			wizard.find('.pagination .next').removeClass('hidden');
		}
		else if(val=='success'){
			wizard.bootstrapWizard('hide', 4);
			wizardFinishOnTab = 4;
			wizard.find('.pagination .next').addClass('hidden');
			wizard.find('.pagination .finish').removeClass('hidden');
		}
	});
	wizard.bootstrapWizard('hide', 4);
	
	var validateCommon = function(form, options, ajaxOptions, n, callback){
		if(!options) options = {};
		if(!ajaxOptions) ajaxOptions = {};
		var validateConfig = $.extend(true,{
			submitHandler: function(){
				var scope = form.attr('j-name');
				var data = form.serializeForm()[scope];
				data.method = form.find('[name=method]').val();
				data.lead = leadToken;
                corporate_email = $("input[name=corporate_email]").val();
                console.log(data);
				jstack.ajax($.extend(true,{
					type: 'post',
					url: 'lead/simulator.json',
					data: data,
					success: function(data,status){
                        var $defer;
					    if(callback)
					         $defer = callback();

						var endpoint = function () {
                            if(wizardFinishOnTab!=n){
                                wizard.bootstrapWizard('show', n);
                            }
                            if(typeof(stepSubmitCallback)=='function'){
                                stepSubmitCallback();
                                stepSubmitCallback = false;
                            }
                        };
                        if($defer)
                            $defer.then(endpoint);
						else
						    endpoint();
					}
				},ajaxOptions));
			},
			rules:{
				
			},
			messages:{
				
			},
			onfocusout: function(e){ //validate on focusout even if is empty
				this.element(e);
			},
		},options);
		form.validate(validateConfig);
	};
	validateCommon(steps[1],{
		rules:{
			phone:{
				intltel: true,
			},
			mobile_phone:{
				intltel: true
			},
			fax:{
				intltel: true
			},
		},
	},null,1);
	validateCommon(steps[2],null,null,2);
	validateCommon(steps[3],{
		rules:{
			
		},
	},null,3);
	validateCommon(steps[5],null,null,5, function () {

        Stripe.card.createToken($form, stripeResponseHandler);

        return stripeOk;

    });
	
	var bindSirenToTva = function(sirenName,tvaName){
		container.on('input','[name="'+sirenName+'"]',function(){
			var $this = $(this);
			var val = $this.val(), tva;
			val = val
				.replace(/\s/g,'')
				.replace(/\D/g,'')
				.substr(0,9)
			;
			$this.val(val);
			if(val.length==9&&checkLuhn(val)){
				tva = siren2tvaFR(val);
			}
			else{
				tva = '';
			}
			$this.closest('form').find('[name="'+tvaName+'"]').val(tva);
		});
	};
	bindSirenToTva('siren','tva');
	bindSirenToTva('debit_siren','debit_tva');
	
	container.on('input','[name$="[restant]"]',function(){
		var restantTotal = 0;
		container.find('[name$="[restant]"]').each(function(){
			restantTotal += parseInt($(this).val(),10) || 0;
		});
		container.find('.restant-total').html(restantTotal+' €');
	});
	
	var selectTypeStep1 = function(){
		var input = container.find('input[name="profil_type"]');
		var handle = function(input) {
			var val = input.val();
			if (val == 'particular') {
				$('#form_corporate_name').hide();
				$('#form_corporate_siren').hide();
				$('#form_corporate_tva').hide();
				$('#form_corporate_price_category').hide();
			}
			else if (val == 'enterprise') {
				$('#form_corporate_name').show();
				$('#form_corporate_siren').show();
				$('#form_corporate_tva').show();
				$('#form_corporate_price_category').show();
			}
		};
		input.change(function(){
			handle( $(this) );
		});
		handle(input);
	};
	selectTypeStep1();

	var selectTypeStep2 = function(){
		var input = container.find('input[name="deb_type"]');
		var handle = function(input){
			var val = input.val();
			if (val == 'particular') {
				$('#form_debit_name').hide();
				$('#form_debit_fonction').hide();
				$('#form_debit_siren').hide();
				$('#form_debit_tva').hide();
				$('#form_corporate_price_category').hide();
			}
			else if (val == 'enterprise') {
				$('#form_debit_name').show();
				$('#form_debit_fonction').show();
				$('#form_debit_siren').show();
				$('#form_debit_tva').show();
				$('#form_corporate_price_category').show();
			}
		};
		input.change(function(){
			handle( $(this) );
		});
		handle(input);
	};
	selectTypeStep2();
	
	var tels = $('input[type=tel]',container);
	var defaultCountry = {
		iso2:'fr',
		dialCode:'33',
	};
	var nameToPrefixName = function(name){
		return name+'_prefix';
	};
	tels.each(function(){
		var $this = $(this);
		var name = nameToPrefixName($this.attr('name'));
		var inputPrefix = $('<input type="hidden" name="'+name+'">').insertAfter(this);
		inputPrefix.val(defaultCountry.dialCode);
	});
	tels.intlTelInput({
		preferredCountries: [defaultCountry.iso2],
	});
	tels.intlTelInput('setCountry',defaultCountry.iso2);
	tels.on("countrychange", function(e, countryData) {
		var name = nameToPrefixName($(this).attr('name'));
		$('input[name="'+name+'"]',container).val(countryData.dialCode);
	});


	$('[data-lead-type="'+leadType+'"]',container).addClass('active');

    var stripeOk = $.Deferred();
	/*$form.submit(function (e) {
        $form.find('.submit').prop('disabled', true);

        return false;
    });*/

    function stripeResponseHandler(status, response) {

        if (response.hasOwnProperty('error')) {
            $form.find('.payment-errors').text(response.error.message);
            $form.find('.submit').prop('disabled', false);
        } else {
            var token = response.id;
            var data = {
                'token' : token,
				'amount' : 3500,
                'email' : corporate_email
            };

            jstack.ajax({
                'url'       : 'lead/simulator.json',
                'type'      : 'POST',
                'data': {
                    method: 'payment',
                    params: [data]
                },
                'dataType'  : 'JSON',
                success     : function (response) {
                    if(response.hasOwnProperty('success')) {
                        stripeOk.resolve();
					}else{
                        $('#step5 p:first-of-type').append('<p class="error">' + response.error + '</p>');
                    }
                }
            });
        }
    }
    
    
    
    
    
    
    
    
    
    
	var simulationPrice = function(){
	var seniority = $('#seniority_ci');
	var amount = $('#form-control-mc-input');
	seniority.change(function(){

		var selectType = $('#select-type');
			if(seniority.val() == '+1an'){
				//selectType.hide();
				selectType.val('amiable');
			}
			else{
				//selectType.show();
				selectType.val('judiciaire');
			}
		});

	};
	simulationPrice();
	
	var container = $('.devis-first-step');
	if(!container.length)
		return;
	
	var leadCategory = $('body').attr('data-lead-category');
	var selecType = $('select[name="type"]',container);
	var selectSeniority = $('select[name="seniority"]',container);
	if(leadCategory=="cheque-impaye"){
		selectSeniority.change(function(){
			var val = $(this).val();
			if(val=='+1an'){
				selecType.find('option[value="judiciaire"]').prop('disabled',true);
			}
			else if(val=='-1an'){
				selecType.find('option').prop('disabled',false);
			}
		});
	}

});
