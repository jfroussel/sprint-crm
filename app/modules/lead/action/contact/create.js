import 'validate';
import 'app.datatables';

import Module from 'module';
export default class extends Module {
	templateUrl(){ return 'lead/action/contact/create'; }
	getData(){
        let id = jstack.url.getParams(this.hash).id;
        if(!id){
            id = $(this.element).data('datatable-edit-id');
        }
        this.data.debtor_id = id;
        return [
            $serviceJSON('action/contact/create','load',[ id ]),
        ];
	}
	setData(json){
        $.extend(this.data,json);
		var data = this.data;;
		data.contact = {
			debtor_id:data.debtor_id,
			category:'contact',
		};
	}
	domReady(){
		var self = this;
		var data = this.data;
		
		var form = $(this.element).find('form');

		form.validate({
			submitHandler: function(e){				
				$serviceJSON('action/contact/create','store',[data.contact],function(id){
					$('#debtor-contact').modal('hide');
					if($('#contacts-table').length){
						$('#contacts-table').DataTable().ajax.reload();					
						
					}
				});
			}
		});

	}
};
