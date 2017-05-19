import 'validate';
import moment from 'moment';
import 'notify-js';

import Module from 'module';
export default class extends Module {
	templateUrl(){ return 'lead/update'; }
	getData(){
		let id = jstack.url.getParams(this.hash).id;
		this.data.id = id;
		return [
			$serviceJSON('lead/update','load',[id]),
		];
	}
	domReady(){
        var self = this;
        var data = this.data;
        var form = $(this.element).find('form');

        form.validate({
            submitHandler: function(e){
                $serviceJSON('lead/update','store',[data],function(id){
                });
            }
        });

	}
	
	updateManager(e,el){
		var self = this;
		var data = self.data;
		$serviceJSON('lead/update','updateManagement',[data.id,data.user_id],function(){
			$(".countDebtor").notify(
				"Le gestionnaire a bien été enregistré !","info",
				{ position:"right" }
			);

		});
	}
};
