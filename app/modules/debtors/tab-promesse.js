import 'validate';

import Module from 'module';
export default class extends Module {
	templateUrl(){ return 'debtors/tab-promesse'; }
	getData(){
		let id = jstack.url.getParams(this.hash).id;
		return [            
		];
	}
	domReady(){
		var self = this;
		var data = self.data;
		var el = self.element;
		
	}	
};
