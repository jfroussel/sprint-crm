import 'validate';
import 'app.datatables';

import Module from 'module';
export default class extends Module {
	templateUrl(){ return 'parameters/bank/create'; }
    getData(){
		return [

		];
	}
    domReady(){

        var data = this.data;

        var form = $(this.element).find('form');

        console.log(form);
        form.validate({
            submitHandler: function(e){

                $serviceJSON('parameters/bank/create','store',[data.bank],function(r){
                    $('#bank-new').modal('hide');
                    if($('#bank-table').length){
                        $('#bank-table').DataTable().ajax.reload();

                    }
                });

            }
        });

    }
};
