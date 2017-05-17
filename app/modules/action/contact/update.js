import 'validate';
import 'app.datatables';

import Module from 'module';
export default class extends Module {
	templateUrl(){ return 'action/contact/update'; }
    getData(){
        let id = $(this.element).data('datatable-edit-id');
        let data = this.data;
        data.id = id;
        data.debtor = data.debtorTab.debtorOne;
        return [
            $serviceJSON('action/contact/update','load',[id]),
        ];
    }
    domReady(){

        var self = this;
        var data = this.data;
        var el = $(self.element);
        var form = $(this.element).find('form');

        console.log(form);
        form.validate({
            submitHandler: function(e){

                $serviceJSON('action/contact/update','store',[data.contact],function(r){
                    el.closest('.modal').modal('hide');
                    if($('#contacts-table').length){
                        $('#contacts-table').DataTable().ajax.reload();
                    }
                });

            }
        });

    }
};
