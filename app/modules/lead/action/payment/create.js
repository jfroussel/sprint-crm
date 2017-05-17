import 'validate';
import 'notifyjs';
import 'app.datatables';

import Module from 'module';
export default class extends Module {
	templateUrl(){ return 'lead/action/payment/create'; }
    getData(){
        let id = jstack.url.getParams(this.hash).id;
        if(!id){
            id = $(this.element).data('datatable-edit-id');
        }
        this.data.id = id;
        return [
            $serviceJSON('action/payment/create','load',[ id ]),
        ];
    }
	setData(json){
        $.extend(this.data,json);
		var data = this.data;
        data.amountNumber = 0;
        data.amount = '';
        data.message = '';
        data.seconds = '0';
        data.minutes = '0';

        data.payment = {
			debtor_id:data.id,
            amount:data.amount,
			category:'reglement',
            message:data.message,

		};
		
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
                data.payment.timer =  secondsToMinutes(sec);
				$serviceJSON('action/payment/create','store',[data.payment],function(r){
					$('#debtor-payment').modal('hide');
                    $.notify('Votre paiement a bien été enregistré  ! ' ,"success");

                    if($('#historique-table').length){
						$('#historique-table').DataTable().ajax.reload();
					}
                    if($('#payments-table').length){
                        $('#payments-table').DataTable().ajax.reload();
                    }
				});
			}
		});

        var table = $('#paperworks-table-payment');
     

		var selectPaperworksData = {};
		let updatemessage = function () {
			data.payment.message = 'reglement de ' + data.payment.amount + ' €' +  ' Réglé par  ' + data.payment.payment_type;
		};
		$('[name=payment_type]').on('j:input', updatemessage);
		$('[name=type]').on('j:input', updatemessage);
		$('[name=amount]').on('j:input', updatemessage);
		updatemessage();


		table.on('j:input','[name="_many2many_paperwork[]"]',function(){

			let datatable = table.DataTable();
			table.find('>tbody>tr').each(function(){
				var rowData = datatable.row(this).data();
				if($(this).find('.select_row').prop('checked')){
					selectPaperworksData[rowData.id] = rowData;
				}
			});

			data.amountNumber = 0;
			$.each(data.payment._many2many_paperwork,function(k,id){
				if(id){
					data.amountNumber += Number(selectPaperworksData[id].debit.replace(',','.'));
				}
			});
			data.payment.debit = data.amountNumber.toFixed(2).replace('.',',')+'€';
			data.payment.amount = data.amountNumber.toFixed(2).replace('.',',');
			updatemessage();
        });
		
	}
};
