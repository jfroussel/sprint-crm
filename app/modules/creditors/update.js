import 'validate';

import Module from 'module';
export default class extends Module {
	templateUrl(){ return 'creditors/update'; }
	getData(){
		return [
			$serviceJSON('creditors/update','load',[jstack.url.getParams(this.hash).id]),
		];
	}
	domReady(){
		
        
	}
};
