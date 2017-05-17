import "jstack";
import "fileinput-img";

export default jstack.directive( 'fileinputImg', class extends jstack.Component{
	domReady(){			
		this.element.fileinputImg(this.options);
	}
});
