import 'validate';
import 'app.datatables';

import Module from 'module';
export default class extends Module {
	
	templateUrl(){ return 'parameters/journaux/update'; }
    
    getData(){

        let id = $(this.element).data('datatable-edit-id');
        //let id = jstack.url.getParams(this.hash).id;
        this.data.id = id;
        return [
            $serviceJSON('parameters/journaux/update','load',[id]),
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

                $serviceJSON('parameters/journaux/update','store',[data.journaux],function(r){
                    el.closest('.modal').modal('hide');
                    if($('#journaux-table').length){
                        $('#journaux-table').DataTable().ajax.reload();

                    }
                });

            }
        });



    }
};
