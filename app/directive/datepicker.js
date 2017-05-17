import "jstack";
import "eonasdan-bootstrap-datetimepicker";

export default jstack.directive( 'datepicker' ,class extends jstack.Component{
	domReady(){			
		let datepicker = this.element;
		let config = this.options;
		
		datepicker.addClass('date-picker');
		var defaultConfig = {
			 locale: 'fr',
			 format: 'DD/MM/YYYY',
		};
		config = $.extend(true,defaultConfig,config);


		let value = datepicker.val();
		if(value&&value.substr(4,1)=='-'){
			let x = value.split('-');
			value = x.pop()+'/'+x.pop()+'/'+x.pop();
			//datepicker.val(value);
			datepicker.trigger('j:update',[value]);
		}

		datepicker.datetimepicker(config);

		datepicker.on('val',function(){
			datepicker.trigger('j:update');
		});
	}

});
