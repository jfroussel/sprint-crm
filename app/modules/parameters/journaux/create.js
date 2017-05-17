import 'validate';
import 'app.datatables';

import Module from 'module';
export default class extends Module {
	templateUrl(){ return 'parameters/journaux/create'; }
    getData(){
		return [];
    }
    domReady(){
        let data = this.data;
        let form = $(this.element).find('form');
        form.validate({
            submitHandler: function(e){

                $serviceJSON('parameters/journaux/update','store',[data.journaux],function(r){
                    $('#journaux-new').modal('hide');
                    if($('#journaux-table').length){
                        $('#journaux-table').DataTable().ajax.reload();

                    }
                });

            }
        });

    }
};
