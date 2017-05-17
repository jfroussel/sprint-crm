import 'validate';
import 'chart.js';
import 'app.datatables';

import Module from 'module';
export default class extends Module {
	templateUrl(){ return 'debtors/tab-debtors-paperworks'; }
	getData(){
		let id = jstack.url.getParams(this.hash).id;
		this.data.id = id;
		return [
            $serviceJSON('debtors/tab-debtors-paperworks','load', [id]),
		];
	}
	domReady(){
		var self = this;
		var data = self.data;

		var element = self.element;
		var table = element.find('#paperworks-table');

			
		var datatable = table.DataTable();

		
		var datatableUrl = table.jData().ajax.url;
		var paperworksType = $('#paperworksType');

		//datatable.ajax.url(datatableUrl+'&where_lettrage=').load();

		var selectPaperworksData = {};

		table.on('j:change','[name="_many2many_paperwork[]"]',function(){

			table.find('>tbody>tr').each(function(){
				var rowData = datatable.row(this).data();
				if($(this).find('.select_row').prop('checked')){
					selectPaperworksData[rowData.id] = rowData;
				}
			});

			data.amountNumber = 0;
			$.each(data._many2many_paperwork,function(k,id){
				if(id){
					data.amountNumber += Number(selectPaperworksData[id].debit.replace(',','.'));
				}
			});
			data.debit = data.amountNumber.toFixed(2).replace('.',',')+'€';
		});

	}
};
