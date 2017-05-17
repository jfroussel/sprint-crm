import Waves from "node-waves";

import 'jstack';
import 'formControlEnhance';

$('.form-control:not(.ignore-fg)').formControlEnhance();

jstack.onLoad('.form-control:not(.ignore-fg)',function(){
	$(this).formControlEnhance();
});


Waves.attach('.btn:not(.btn-icon):not(.btn-float)');
Waves.attach('.btn-icon, .btn-float', ['waves-circle', 'waves-float']);
Waves.init();

$(document).on('show.bs.collapse', '.collapse', function (e) {
	$(this).prev('.panel-heading').addClass('active');
});
$(document).on('hide.bs.collapse', '.collapse', function (e) {
	$(this).prev('.panel-heading').removeClass('active');
});
$('.collapse.in').each(function(){
	$(this).closest('.panel').find('.panel-heading').addClass('active');
});
	
$(document).on('mouseover','[data-toggle="tooltip"]',function(){
	var $this = $(this);
	require(['bootstrap'],function(){
		if($this.data('tooltip-loaded')) return;
		$this.data('tooltip-loaded',1);
		$this.tooltip();
	});
});


var scroller = $('html, body');
var speed = 750;
$('.page-scroll').on('click', function(e){
	e.preventDefault();
	var href = $(this).attr('href') || '#';
	href = href.substr(href.indexOf('#'));
	var top;
	if(href=='#'){
		top = 0;
	}
	else{
		top =  $(href).offset().top;
	}
	scroller.stop(true,true).animate({
		scrollTop: top 
	}, speed );
	return false;
});
