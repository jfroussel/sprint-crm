import 'validate';

import Module from 'module';
export default class extends Module {
	templateUrl(){ return 'parameters/tab-scenarios'; }
	getData(){
		return [
		];
	}
	domReady(){	
		let data = this.data;
		let el = this.element;
	}
};
