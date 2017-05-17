import moment from 'moment';
import 'validate';
import 'app.datatables';

import Module from 'module';
export default class extends Module {
	templateUrl(){ return 'lead/action/schedule/update'; }
    
    getData(){
        let id = jstack.url.getParams(this.hash).id;
        return [
            $serviceJSON('action/schedule/update','load',[id]),
        ];
    }
    domReady(){

        let self = this;
        let data = self.data;

        let table = $('#table-schedule');

		let datatable = table.DataTable();
		
		datatable.on( 'order.dt search.dt', function () {
			datatable.column(1, {search:'applied', order:'applied'}).nodes().each( function (cell, i) {
				cell.innerHTML = i+1;
			});
		} ).draw();

    }
};
