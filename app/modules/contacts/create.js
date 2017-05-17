import 'validate';

import Module from 'module';
export default class extends Module {
	templateUrl(){ return 'contacts/create'; }
	getData(){
		return [];
	}
	setData(){
		
	}
	domReady(){
		
		var data = this.data;
		var form = $(this.element).find('form');
		form.validate({
			submitHandler: function(e){
				e.preventDefault();
				$serviceJSON('action/contact/create','store',data.contact);
			}
			
		});

	}
};
