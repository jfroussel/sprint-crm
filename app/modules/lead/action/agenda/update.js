import 'validate';
import 'notify-js';
import 'app.datatables';

import Module from 'module';
export default class extends Module {
	templateUrl(){ return 'lead/action/agenda/update'; }
    getData(){
        let id = $(this.element).data('datatable-edit-id');
        this.data.id = id;
        return [
            $serviceJSON('action/agenda/update','load',[id]),
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
                $serviceJSON('action/agenda/update','store',[data.agenda],function(r){
                    $('#debtor-agenda').modal('hide');
                    $.notify('Votre note Agenda a bien été mise à jour !',"success");
                    if($('#paperworks-table-agenda').length){
                        $('#paperworks-table-agenda').DataTable().ajax.reload();
                    }
                });


            }
        });
    }
};
