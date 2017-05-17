import 'validate';

import Module from 'module';
export default class extends Module {
	templateUrl(){ return 'action/promise/update'; }
	getData(){
		return [
			$serviceJSON('action/promise/update','load',[jstack.url.getParams(this.hash).id]),
		];
	}
	domReady(){
		

	}
};
