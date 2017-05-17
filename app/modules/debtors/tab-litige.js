import 'validate';
import 'chart.js';

import Module from 'module';
export default class extends Module {
	templateUrl(){ return 'debtors/tab-litige'; }
	getData(){
		var id = jstack.url.getParams(this.hash).id;
		this.data.id = id;
		return [
            $serviceJSON('debtors/tab-litige','load', [id]),
		];
	}
	domReady(){
		let self = this;
		let data = self.data;
		let element = self.element;
	}
};
