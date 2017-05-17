import 'validate';

import Module from 'module';
export default class extends Module {
	templateUrl(){ return 'debtors/tab-parametres'; }
	getData(){
		var id = jstack.url.getParams(this.hash).id;
		this.data.id = id;
		return [
			$serviceJSON('debtors/tab-parametres','load',[id]),
		];
	}
	domReady(){
		var self = this;
		var data = self.data;
		var el = $(self.element);
	}

	updateManager(e,el){
		var self = this;
		var data = self.data;
		$serviceJSON('debtors/tab-parametres','updateManagement',[data.id,data.user_id]);
	}
};
