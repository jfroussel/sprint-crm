import Module from 'module';

import 'validate';
import 'chart.js';
import moment from 'moment';

export default class extends Module {
	templateUrl(){ return 'debtors/update'; }
	
	getData(){
		var id = jstack.url.getParams(this.hash).id;
		return [
			$serviceJSON('debtors/update','load', [id]),

		];
	}

	setData(){
	    super.setData();
	    this.data.dateOfDay = moment().format('LL');
    }
	domReady(){
		var self = this;
		var data = self.data;
		var element = self.element;

        $('.debtor-name').text(data.ct_intitule);
        $('.debtor-adresse').text(data.ct_adresse);
        $('.debtor-codepostal').text(data.ct_codepostal);
        $('.debtor-ville').text(data.ct_ville);

		//jstack.log(data.contact);






		
	}
};
