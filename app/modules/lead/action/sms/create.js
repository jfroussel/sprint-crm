import 'validate';
import 'notify-js';
import 'app.datatables';

import Module from 'module';
export default class extends Module {
	templateUrl(){ return 'lead/action/sms/create'; }
    getData(){
        let id = jstack.url.getParams(this.hash).id;
        if(!id){
            id = $(this.element).data('datatable-edit-id');
        }
        this.data.id = id;
        return [
            $serviceJSON('action/sms/create','load',[ id ]),	
        ];
    }
	setData(json){
		$.extend(this.data, json);
		
		var data = this.data;
        data.sms = {
			debtor_id:data.id,
			category:'sms',
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
                data.sms.timer =  secondsToMinutes(sec);
                $serviceJSON('action/sms/create','send',[data.sms], function (result) {
					$('#debtor-sms').modal('hide');
                    $.notify('Votre sms a bien été enregistré  ! ',"success");
                    if($('#historique-table').length){
                        $('#historique-table').DataTable().ajax.reload();
                    }

                    if (result && result.error){
                        sweetAlert({
                            'title': result.error,
                            'type': "error",
                            'timer': 2500
                        });
                    }
                });
			}
		});

        var table = $('#paperworks-table-sms');


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
			$.each(data.sms._many2many_paperwork,function(k,id){
				if(id){
					data.amountNumber += Number(selectPaperworksData[id].debit.replace(',','.'));
				}
			});
			data.sms.debit = data.amountNumber.toFixed(2).replace('.',',')+'€';
		});


        $('#template-type').on('j:input', function(e){

            data.sms.message = data.templatesList[data.sms.template_id].message;
        });
	}
};
