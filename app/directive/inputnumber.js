import "jstack";
import "fileinput-img";

export default jstack.directive( 'inputnumber' , class extends jstack.Component{
	domReady(){
		let input = this.element;
		let config = this.options;
		config = $.extend({
			max: input.attr('max'),
			min: input.attr('min'),
			step: input.attr('step'),
			downInterval: 150,
			downWait: 500,
			onlyInteger: true,
		},config);
		
		config.min = parseInt(config.min,10);
		config.max = parseInt(config.max,10);
		config.step = parseInt(config.step,10);
		
		var jInput = !!input.attr('name');
		
		input.wrap('<div class="input-group inputnumber">');
		var wrap = input.parent();
		wrap.append('<div class="input-group-btn-vertical"><button class="btn btn-default" type="button"><i class="fa fa-caret-up"></i></button><button class="btn btn-default" type="button"><i class="fa fa-caret-down"></i></button></div>');
		var format;
		if(config.onlyInteger){
			format = function(val){	
				val = val.replace(/[^0-9\-]/g, '');
				val = parseInt(val, 10);
				return val;
			};
		}
		else{
			format = function(val){
				val = val.replace(/[^0-9\.\-]/g, '');
				val = Number(val);
				return val;
			};
		}
		var filterVal = function(val,modify){
			if(val=='-') return val;
			val = format(val);
			if(!val){
				val = 0;
			}
			if(modify){
				val += modify;
			}
			if(val>config.max){
				val = config.max;
			}
			if(val<config.min){
				val = config.min;
			}
			return val;
		};
		var setVal = function(val,modify){
			input.val( filterVal(val,modify) );
		};
		var incrementBtn = wrap.find('.btn:first-of-type');
		var decrementBtn = wrap.find('.btn:last-of-type');
		var increment = function(){
			setVal(input.val(), config.step);
		};
		var decrement = function(){
			setVal(input.val(), -1*config.step);
		};
		
		if(jInput){
			input.data('j-filter',function(){
				return filterVal( input.val() );
			});
			input.on('j:input:user',function(){
				input.trigger('inputnumber:change');
			});
		}
		else{
			input.on('input',function(){
				input.val( filterVal( input.val() ) );
				input.trigger('inputnumber:change');
			});
		}
		
		var inputKeyboard = function(e) {
			switch(e.keyCode){
				case $.ui.keyCode.UP:
					setVal(input.val(), config.step);
				break;
				case $.ui.keyCode.DOWN:
					setVal(input.val(), -1*config.step);
				break;
			}
		};
		
		var listenDown = function(el,eventdown,eventup,callback,endCallback){
			el = $(el);
			var stillDown = false;
			var timeout = false;
			el.on(eventdown,function(e){
				continueDown(function(){
					callback(e);
				});
			});
			el.on(eventup,function(e){
				clearInterval(stillDown);
				clearTimeout(timeout);
				stillDown = false;
				timeout = false;
				endCallback(e);
			});
			var continueDown = function(c){
				c();
				if(timeout){
					clearTimeout(timeout);
				}
				timeout = setTimeout(function(){
					if(!stillDown){
						stillDown = setInterval(function(){
							continueDown(c);
						}, config.downInterval);
					}
				},config.downWait);
			}
		};
		
		var endCallbackKeyboard = function(e){
			if(e.keyCode==$.ui.keyCode.UP || e.keyCode==$.ui.keyCode.DOWN){
				input.trigger('j:update');
				input.trigger('inputnumber:change');
			}
		}
		var endCallbackMouse = function(e){
			input.trigger('j:update');
			input.trigger('inputnumber:change');
		};
		//listenDown(incrementBtn,'mousedown','mouseup mouseleave',increment,endCallbackMouse);
		//listenDown(decrementBtn,'mousedown','mouseup mouseleave',decrement,endCallbackMouse);
		listenDown(incrementBtn,'mousedown','mouseup',increment,endCallbackMouse);
		listenDown(decrementBtn,'mousedown','mouseup',decrement,endCallbackMouse);
		listenDown(input,'keydown','keyup',inputKeyboard,endCallbackKeyboard);

		
		
	}
});
