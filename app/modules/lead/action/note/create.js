import 'validate';
import 'notify-js';
import 'app.datatables';

import Module from 'module';
export default class extends Module {
	templateUrl(){ return 'lead/action/note/create'; }
	getData(){
		let id = jstack.url.getParams(this.hash).id;
		if(!id){
			id = $(this.element).data('datatable-edit-id');
        }
        this.data.id = id;
		return [
			$serviceJSON('action/note/create','load',[ id ]),
		];
	}
	setData(json){
		$.extend(this.data, json);
		
		let data = this.data;
		data.seconds = '0';
		data.minutes = '0';
	}
	domReady(){

		var data = this.data;
		var form = $(this.element).find('form');

		let sec = 0;
        function pad(val) {
            return val > 9 ? val : "0" + val;
        }

        var timing = setInterval(function () {
            data.seconds = pad(++sec % 60);
            data.minutes = pad(parseInt(sec / 60, 10));
        }, 1000);

        function secondsToMinutes(time){
            return Math.floor(time / 60)+':'+Math.floor(time % 60);
        }

		form.validate({
			submitHandler: function(){
				clearInterval(timing);
				data.note.timer =  secondsToMinutes(sec);
				$serviceJSON('action/note/create','store',[data.note],function(r){
					$('#debtor-note').modal('hide');
                    $.notify('Votre note a bien été enregistrée en ! ' + data.note.timer + ' minutes',"success");
					if($('#historique-table').length){
						$('#historique-table').DataTable().ajax.reload();
					}
				});
			}
		});

        var table = $('#paperworks-table-note');

		var selectPaperworksData = {};
		table.on('j:input','[name="_many2many_paperwork[]"]',function(){
			
			let datatable = table.DataTable();
			table.find('>tbody>tr').each(function(){
				var rowData = datatable.row(this).data();
				if($(this).find('.select_row').prop('checked')){
					selectPaperworksData[rowData.id] = rowData;
				}
			});

			data.amountNumber = 0;
			$.each(data.note._many2many_paperwork,function(k,id){
				if(id){
					// data.amountNumber += Number(selectPaperworksData[id].montant.replace(',','.'));
					data.amountNumber += Number(selectPaperworksData[id].debit.replace(',','.'));
				}
			});
			data.note.debit = data.amountNumber.toFixed(2).replace('.',',')+'€';
		});


	}
};
