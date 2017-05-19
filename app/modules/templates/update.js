import 'validate';
import 'notify-js';

import Module from 'module';
export default class extends Module {
	
	templateUrl(){ return 'templates/update'; }
	
	getData(){
		return [
			$serviceJSON('templates/update','load',[jstack.url.getParams(this.hash).id]),
		];
	}
	
	domReady(){

		var data = this.data;
		console.log(data);

		var form = $(this.element).find('form');
        form.validate({
            submitHandler: function(){
                $serviceJSON('templates/update','store',[data.template]);
                jstack.route('templates/all');
                $.notify("Votre template a bien été modifié", "success");
            }

        });


	}
};
