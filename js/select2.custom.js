$.fn.select2.defaults.set('language', 'fr'); //<html lang="fr"> is overrinding
$.fn.select2.defaults.set('theme', 'bootstrap');
$.fn.select2.defaults.set('debug', true);

$.fn.wrapSelect2 = function(opts){
	return this.each(function(){
		var $this = $(this);
		
		$this.select2(opts);
		
		var opened = [];
		$this.on('select2:open',function(){
			opened.push(this);
		});
		$this.on('select2:close',function(){
			var index = opened.indexOf(this);
			if(index>-1){
				opened.splice(index, 1);
			}
		});
		$this.on('container-scroll',function(){
			$this.select2('close');
			//select.select2('open');
		});
		var scroller = $this.closest('.c-overflow');
		if(!scroller.data('scroll-select2-handled')){
			scroller.data('scroll-select2-handled',true);
			scroller.scroll(function(){
				if(opened.length){
					setTimeout(function(){
						$.each(opened,function(i,widget){
							$(widget).trigger('container-scroll');
						});
					},0);
				}
			});
		}
		
	});
};