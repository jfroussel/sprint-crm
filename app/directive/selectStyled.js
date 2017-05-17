import "jstack";

export default jstack.directive( 'selectStyled' ,class extends jstack.Component{
	domReady(){
		const $el = this.element;
		if(!$el.parent().hasClass()){
			$el.wrap('<div class="select" />');
		}
		$el.addClass('ignore-fg');
		$el.addClass('form-control');
	}
});
