import 'validate';

import Module from 'module';
export default class extends Module {
	templateUrl(){ return 'debtors/tab-debtors-contacts'; }
	getData(){
        let id = jstack.url.getParams(this.hash).id;
        this.data.id = id;
		return [
		];
	}
	domReady(){
	
	}	
};
