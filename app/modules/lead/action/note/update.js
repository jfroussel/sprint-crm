import 'validate';

import Module from 'module';
export default class extends Module {
	templateUrl(){ return 'lead/action/note/update'; }
	getData(){
		let debtor_id = jstack.url.getParams(this.hash).id;
		return [
			$serviceJSON('action/note/update','load',[debtor_id]),
		];
	}
	domReady(){
        var data = this.data;
        console.log(data);

        var form = $(this.element).find('form');
        form.validate({
            submitHandler: function(){
                $serviceJSON('action/note/update','store',[data.note]);
            }
        });
	}
};
