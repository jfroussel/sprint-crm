import "jstack";
import './adapters/bootstrap';
import "./directive/ckeditor";
import "app.datatables";
import "./directive/datepicker";
import "./directive/fileinputImg";
import "./directive/inputnumber";
import "./directive/selectpicker";
import "./directive/selectStyled";
import "./layout/widgets";
import "../js/functions";
import 'sweetalert';
import './adapters/nicescroll';

let routes = {
	"": require("./modules/index").default,
	"action/agenda/all": require("./modules/action/agenda/all").default,
	"action/contact/all": require("./modules/action/contact/all").default,
	"action/email/all": require("./modules/action/email/all").default,
	"action/letter/all": require("./modules/action/letter/all").default,
	"action/litige/all": require("./modules/action/litige/all").default,
	"action/note/all": require("./modules/action/note/all").default,
	"action/payment/all": require("./modules/action/payment/all").default,
	"action/promise/all": require("./modules/action/promise/all").default,
	"action/schedule/all": require("./modules/action/schedule/all").default,
	"action/sms/all": require("./modules/action/sms/all").default,
	"contacts/all": require("./modules/contacts/all").default,
	"creditors/all": require("./modules/creditors/all").default,
	"debtors/all": require("./modules/debtors/all").default,
	"lead/all": require("./modules/lead/all").default,
	"lead/action/agenda/all": require("./modules/lead/action/agenda/all").default,
	"lead/action/contact/all": require("./modules/lead/action/contact/all").default,
	"lead/action/email/all": require("./modules/lead/action/email/all").default,
	"lead/action/letter/all": require("./modules/lead/action/letter/all").default,
	"lead/action/litige/all": require("./modules/lead/action/litige/all").default,
	"lead/action/note/all": require("./modules/lead/action/note/all").default,
	"lead/action/payment/all": require("./modules/lead/action/payment/all").default,
	"lead/action/promise/all": require("./modules/lead/action/promise/all").default,
	"lead/action/schedule/all": require("./modules/lead/action/schedule/all").default,
	"lead/action/sms/all": require("./modules/lead/action/sms/all").default,
	"paperworks/all": require("./modules/paperworks/all").default,
	"parameters/bank/all": require("./modules/parameters/bank/all").default,
	"parameters/billing/all": require("./modules/parameters/billing/all").default,
	"parameters/journaux/all": require("./modules/parameters/journaux/all").default,
	"scenarios/all": require("./modules/scenarios/all").default,
	"templates/all": require("./modules/templates/all").default,
	"users/all": require("./modules/users/all").default,
	"action/agenda/create": require("./modules/action/agenda/create").default,
	"action/contact/create": require("./modules/action/contact/create").default,
	"action/email/create": require("./modules/action/email/create").default,
	"action/letter/create": require("./modules/action/letter/create").default,
	"action/litige/create": require("./modules/action/litige/create").default,
	"action/note/create": require("./modules/action/note/create").default,
	"action/payment/create": require("./modules/action/payment/create").default,
	"action/promise/create": require("./modules/action/promise/create").default,
	"action/schedule/create": require("./modules/action/schedule/create").default,
	"action/sms/create": require("./modules/action/sms/create").default,
	"contacts/create": require("./modules/contacts/create").default,
	"lead/action/agenda/create": require("./modules/lead/action/agenda/create").default,
	"lead/action/contact/create": require("./modules/lead/action/contact/create").default,
	"lead/action/email/create": require("./modules/lead/action/email/create").default,
	"lead/action/letter/create": require("./modules/lead/action/letter/create").default,
	"lead/action/litige/create": require("./modules/lead/action/litige/create").default,
	"lead/action/note/create": require("./modules/lead/action/note/create").default,
	"lead/action/payment/create": require("./modules/lead/action/payment/create").default,
	"lead/action/promise/create": require("./modules/lead/action/promise/create").default,
	"lead/action/schedule/create": require("./modules/lead/action/schedule/create").default,
	"lead/action/sms/create": require("./modules/lead/action/sms/create").default,
	"parameters/bank/create": require("./modules/parameters/bank/create").default,
	"parameters/journaux/create": require("./modules/parameters/journaux/create").default,
	"templates/create": require("./modules/templates/create").default,
	"users/create": require("./modules/users/create").default,
	"home/lead": require("./modules/home/lead").default,
	"home/leadone": require("./modules/home/leadone").default,
	"action/no-ref": require("./modules/action/no-ref").default,
	"action/note/no-ref": require("./modules/action/note/no-ref").default,
	"lead/action/no-ref": require("./modules/lead/action/no-ref").default,
	"lead/action/note/no-ref": require("./modules/lead/action/note/no-ref").default,
	"parameters/parameters": require("./modules/parameters/parameters").default,
	"home/saas": require("./modules/home/saas").default,
	"home/tab-actions": require("./modules/home/tab-actions").default,
	"home/tab-agenda": require("./modules/home/tab-agenda").default,
	"debtors/tab-analyse": require("./modules/debtors/tab-analyse").default,
	"parameters/tab-api": require("./modules/parameters/tab-api").default,
	"parameters/tab-banques": require("./modules/parameters/tab-banques").default,
    "parameters/tab-parameters-home": require("./modules/parameters/tab-parameters-home").default,
	"parameters/tab-billings": require("./modules/parameters/tab-billings").default,
	"debtors/tab-debtors-contacts": require("./modules/debtors/tab-debtors-contacts").default,
	"debtors/tab-debtors-paperworks": require("./modules/debtors/tab-debtors-paperworks").default,
	"debtors/tab-debtors-surveillance": require("./modules/debtors/tab-debtors-surveillance").default,
	"debtors/tab-echeancier": require("./modules/debtors/tab-echeancier").default,
	"parameters/tab-files-import": require("./modules/parameters/tab-files-import").default,
	"home/tab-folders": require("./modules/home/tab-folders").default,
	"home/tab-home": require("./modules/home/tab-home").default,
	"debtors/tab-litige": require("./modules/debtors/tab-litige").default,
	"debtors/tab-parametres": require("./modules/debtors/tab-parametres").default,
	"parameters/tab-plugins": require("./modules/parameters/tab-plugins").default,
	"debtors/tab-promesse": require("./modules/debtors/tab-promesse").default,
	"debtors/tab-scenario": require("./modules/debtors/tab-scenario").default,
	"parameters/tab-scenarios": require("./modules/parameters/tab-scenarios").default,
	"action/agenda/update": require("./modules/action/agenda/update").default,
	"action/contact/update": require("./modules/action/contact/update").default,
	"action/note/update": require("./modules/action/note/update").default,
	"action/promise/update": require("./modules/action/promise/update").default,
	"action/schedule/update": require("./modules/action/schedule/update").default,
	"creditors/update": require("./modules/creditors/update").default,
	"debtors/update": require("./modules/debtors/update").default,
	"history/update": require("./modules/history/update").default,
	"lead/update": require("./modules/lead/update").default,
	"lead/action/agenda/update": require("./modules/lead/action/agenda/update").default,
	"lead/action/contact/update": require("./modules/lead/action/contact/update").default,
	"lead/action/note/update": require("./modules/lead/action/note/update").default,
	"lead/action/promise/update": require("./modules/lead/action/promise/update").default,
	"lead/action/schedule/update": require("./modules/lead/action/schedule/update").default,
	"paperworks/update": require("./modules/paperworks/update").default,
	"parameters/bank/update": require("./modules/parameters/bank/update").default,
	"parameters/billing/update": require("./modules/parameters/billing/update").default,
	"parameters/journaux/update": require("./modules/parameters/journaux/update").default,
	"scenarios/update": require("./modules/scenarios/update").default,
	"templates/update": require("./modules/templates/update").default,
	"users/update": require("./modules/users/update").default,
	"users/update_user": require("./modules/users/update_user").default,
};


//commons
window.addEventListener('error', function (e){
	let stack = e.error.stack;
	let message = e.error.toString();
	if (stack) {
		message += '\n' + stack;
	}
	alert(message);
});

//global ajax service fitting the current server-side route convention
window.$serviceJSON = function(uri,method,params,callback,ajaxOptions){
	let data = {};
	if(typeof(callback)!='function'&&typeof(params)=='undefined'&&typeof(method)=='function'){
		callback = method;
		method = null;
		if(typeof(callback)=='object'){
			ajaxOptions = callback;
			callback = null
		}
	}
	if(typeof(callback)=='undefined'&&typeof(params)=='function'){
		callback = params;
		params = null;
	}
	if(typeof(method)=='object'){
		if(typeof(params)=='function'){
			callback = params;
		}
		params = method;
		method = null;
	}
	if(method)
		data.method = method;
	if(params)
		data.params = params;
	if(uri.substr(-5)=='.json')
		uri = uri.substr(0,uri.length-5);
	const ajaxQuery = {
		url:uri+'.json',
		type:'POST',
		dataType:'json',
		data:data,
		success:callback
	};
	if(ajaxOptions)
		$.extend(true,ajaxQuery,ajaxOptions);
	return jstack.ajax(ajaxQuery);
};

window.$serviceXHR = function(url,data,success,ajax){
	if(typeof(success)=='object'){
		ajax = success;
		success = false;
	}
	ajax = ajax || {};
	ajax.dataType = 'json';
	ajax.type = 'POST';
	ajax.url = url;
	ajax.data = data;
	if(typeof(success)=='function'){
		ajax.success = success;
	}
	return jstack.ajax(ajax);
};

window.$sanitizeTableName = function(str){
	str = str
		.replace(/\s+/g, '_')           // Replace spaces with -
		.replace(/\-\-+/g, '_')         // Replace multiple - with single -
		.replace(/[^\w\-]+/g, '')       // Remove all non-word chars
		.replace(/^-+/, '')             // Trim - from start of text
		.replace(/-+$/, '')            // Trim - from end of text
		.snakeCase()
		.toLowerCase()
	;
	return str.trim('_');
	
};


$(document).ajaxError(function(event, jqxhr, settings, exception ){
	if(jqxhr.status==401){
		location.reload();
	}
});
$.ajaxSetup({
	error:function(XMLHttpRequest){
		if(XMLHttpRequest.status==401||XMLHttpRequest.status==400||XMLHttpRequest.status==0&&XMLHttpRequest.statusText=='abort') return;
		var msg = this.url+'<br>HTTP '+XMLHttpRequest.status+' '+XMLHttpRequest.statusText;
		if(XMLHttpRequest.responseText&&typeof(XMLHttpRequest.responseText)=='string'){
			var responseText = XMLHttpRequest.responseText;
			responseText = responseText.replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1<br>$2'); //nl2br
			msg += '<br>'+responseText;
		}
		
			
		switch(XMLHttpRequest.status){
			case 403:
				swal({
					title: "Action non authorisée",
					text: 'Contactez votre administrateur',
				});
			break;
			default:
				swal({
					title: "Oops !",
					text: '<div class="sweet-alert-error-box">'+msg+'</div>',
					html: true,
					type:'error',
					customClass:'swal-scrollable'
				});
			break;
		}
		$('.sa-error').click(function(){
			swal.close();
		});
		//if(console) console.log(XMLHttpRequest);
	}
});



if(jstack.isMobile()){
	$('html').addClass('ismobile');
}

//waiting loader
let spinner = $('<div class="waiting-loader"> <div></div><div></div><div></div> </div>');
$('body').append(spinner);
let fainter = $('[j-app]');
if(!fainter.length)
	fainter = $('main');
const is_firefox = navigator.userAgent.toLowerCase().indexOf('firefox') > -1;
const animationBorderRadiusbugfix = function(){
	$('body').css('min-height','calc(100vh + 10px)');
	window.scrollTo(0,document.body.scrollHeight);
	window.scrollTo(0,0);
	$('body').css('min-height','0');
};
const spinnerOn = function(hide){
	spinner.show();
	if(is_firefox){
		animationBorderRadiusbugfix();
	}
	fainter.css('opacity','0.2');		
};
const spinnerOff = function(hide){
	if(spinner)
		spinner.hide();
	fainter.css('opacity','1');
};
$(window).on('unload',spinnerOn);

const container = $('[j-app]');

const app = container.attr('j-app');
	
if(!app){
	$(document.body).addClass('no-j-app');
	$(document.body).addClass('c-overflow');
}

$.autoNicescroll();

if(app){

	//working in js application mode

	$(document)
		.on('j:route:load',spinnerOn)
		.on('j:route:loaded',spinnerOff)
	;

	//saas
	$(document)
		.on('click','.sub-menu > a',function(e){
			e.preventDefault();
			$(this).next().slideToggle(200);
			$(this).parent().toggleClass('toggled');
		})
		.on('click', '.a-prevent', function(e){
			e.preventDefault();
		})
		.on('j:route:loaded', function(e){
			const builderWrap = $('#builder-main');
			if(!builderWrap.find('.c-overflow').length){
				//builderWrap.find('> *').addClass('c-overflow');			
				builderWrap.addClass('c-overflow');			
			}
			
			$.autoNicescroll();
		})
	;


	jstack.config.templatesPath = 'app/modules/';
	jstack.config.controllersPath = 'app/modules/';

	const router = new jstack.Router({
		el: '[j-app]',
		routes: routes
	});

	router.run();


}
