import 'validate';
import 'app.datatables';

import Module from 'module';
export default class extends Module {
	templateUrl(){ return 'parameters/bank/update'; }
    getData(){

        let id = $(this.element).data('datatable-edit-id');
        //let id = jstack.url.getParams(this.hash).id;

        return [
            $serviceJSON('parameters/bank/update','load',[id]),
        ];
    }
    setData(bank){
        var data = this.data;

        data.bank = bank;
        data.id = bank.id;
        console.log(data.id);
    }
    domReady(){

        var self = this;
        var data = this.data;
        var el = $(self.element);
        var form = $(this.element).find('form');

        console.log(form);
        form.validate({
            submitHandler: function(e){

                $serviceJSON('parameters/bank/update','store',[data.bank],function(r){
                    el.closest('.modal').modal('hide');
                    if($('#bank-table').length){
                        $('#bank-table').DataTable().ajax.reload();

                    }
                });

            }
        });



    }
};
