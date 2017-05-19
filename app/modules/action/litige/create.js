import 'validate';
import 'notify-js';
import 'app.datatables';

import Module from 'module';
export default class extends Module {
	templateUrl(){ return 'action/litige/create'; }
	getData(){
        let id = jstack.url.getParams(this.hash).id;
        if(!id){
            id = $(this.element).data('datatable-edit-id');
        }
        this.data.id = id;
        return [
            $serviceJSON('action/litige/create','load',[ id ]),
        ];
	}
	setData(json){
	    $.extend(this.data,json);
		var data = this.data;

        data.amount = '';
        data.litige = {
			debtor_id:data.id,
            amount:data.amount,
			category:"litige",
		};
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
                data.litige.timer =  secondsToMinutes(sec);
				$serviceJSON('action/litige/create','store',[data.litige],function(r){
					$('#debtor-litige').modal('hide');
                    $.notify('Votre ltige a bien été enregistrée en ! ' + data.litige.timer + ' minutes',"success");

                    if($('#historique-table').length){
						$('#historique-table').DataTable().ajax.reload();
					}
					if($('#litiges-table').length){
						$('#litiges-table').DataTable().ajax.reload();
					}
                    $serviceJSON('action/litige/create','getOneLitiges',[data.id],function(r){
                        $('#count-litiges').html(r +' €');

                    });
				});
			}
		});



        var table = $('#paperworks-table-litige');

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
			$.each(data.litige._many2many_paperwork,function(k,id){
				if(id){
					data.amountNumber += Number(selectPaperworksData[id].debit.replace(',','.'));
				}
			});
			data.litige.debit = data.amountNumber.toFixed(2).replace('.',',')+'€';
			data.litige.amount = data.amountNumber.toFixed(2).replace('.',',');

		});


		
	}
};
