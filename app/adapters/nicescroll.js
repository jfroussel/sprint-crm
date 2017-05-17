import 'jquery.nicescroll';

$.fn.wrapNicescroll = function(color, cursorWidth){
	if(!color) color = 'rgba(0,0,0,0.5)';
	if(!cursorWidth) cursorWidth = '5px';
	return this.each(function(){
		var $this = $(this);
		if($this.data('nicescroll-handled')) return;
		$this.data('nicescroll-handled', true);
		$this.niceScroll({
			cursorcolor: color,
			cursorborder: 0,
			cursorborderradius: 0,
			cursorwidth: cursorWidth,
			bouncescroll: true,
			mousescrollstep: 100,
			//autohidemode: false,
			scriptpath:'../img/nicescroll/',
			oneaxismousemode: false, //only vertical mousescroll
			horizrailenabled: false,  //only vertical
		});
	});
};
$.autoNicescroll = function(){
	if(!jstack.isMobile()){
		$('.c-overflow').wrapNicescroll();
	}
};
