//Add blue animated border and remove with condition when focus and blur
$.fn.formControlEnhance = function(){
	return this.each(function(){
		var $this = $(this);
		if($this.hasClass('fg-float')||$this.closest('.form-group').hasClass('fg-float')){
			var handleVal = function(){
				var i = $this.val();
				if(i.length!=0){
					$this.closest('.fg-line').addClass('fg-toggled');
				}					
			};
			$this.on('val j:val',handleVal);
			handleVal();
		}
		else{
			if(!$this.closest('.fg-line,.fg-col').length){
				$this.wrap('<div class="fg-col" />');
			}
		}	
		$this.on({
			focus: function(){
				var p = $(this).closest('.form-group');
				var fgl = $(this).closest('.fg-line, .fg-col');
				if(p.hasClass('fg-float')) fgl.addClass('fg-toggled');
				fgl.addClass('fg-focus');
			},
			blur: function(e){
				e.stopPropagation();
				var p = $(this).closest('.form-group');
				var fgl = $(this).closest('.fg-line, .fg-col');
				var i = p.find('.form-control').val();
				if (p.hasClass('fg-float')&&!p.is('.ui-autocomplete-input')&&i.length==0){
					fgl.removeClass('fg-toggled');
				}
				fgl.removeClass('fg-focus');
			}
		});	
	});
};