import "jstack";
import "bootstrap";
import "bootstrap-select";
import "bootstrap-select/js/i18n/defaults-fr_FR";
	
export default jstack.directive( 'selectpicker' , class extends jstack.Component{
	domReady(){
		const $el = this.element;
		
		$el.selectpicker($.extend({
			style: 'btn-default'
		},this.options));
		
		$el.closest('form').on('reset',function(){
			$el.selectpicker('val','');
		});
		$el.on('j:input',function(){
			$el.selectpicker('val',$el.val());
		});
	}
});
