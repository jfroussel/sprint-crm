import 'validate';
import 'notify-js';

import Module from 'module';
export default class extends Module {
	templateUrl(){ return 'templates/create'; }
	getData(){
		return [];	
	}
	setData(){
		this.data.template = {};
	}
	domReady(){

		var data = this.data;
		var form = $(this.element).find('form');
		form.validate({
			submitHandler: function(){
				$serviceJSON('templates/create','store',[data.template],function(r){
					if(r.id){
						//jstack.route('templates/update',{id:r.id});
                        jstack.route('templates/all');
					}
				});
				$.notify("Votre template a bien été enregistré", "success");
			}

		});



    }
};
