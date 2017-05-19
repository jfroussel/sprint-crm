import 'validate';
import moment from 'moment';
import 'notify-js';
import 'app.datatables';

import Module from 'module';
export default class extends Module {
	templateUrl(){ return 'action/promise/create'; }
	getData(){
        let id = jstack.url.getParams(this.hash).id;
        if(!id){
            id = $(this.element).data('datatable-edit-id');
        }
        this.data.id = id;
        return [
            $serviceJSON('action/promise/create','load',[ id ]),
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



        data.promise = {
			debtor_id:data.id,
			category:'promesse',
			amount:data.amount,
			message: data.message,
			date_reglement:data.date_reglement,
			solutionner:data.solutionner,
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


        let email = data;
		console.log(email);

		form.validate({
			submitHandler: function(){
                clearInterval(timing);
                data.promise.timer =  secondsToMinutes(sec);
				$serviceJSON('action/promise/create','store',[data.promise],function(r){
                    $('#debtor-promise').modal('hide');
                    $.notify('Votre promesse a bien été enregistrée ! ' ,"success");

                    if($('#historique-table').length){
						$('#historique-table').DataTable().ajax.reload();
					}
					if($('#promises-table').length){
						$('#promises-table').DataTable().ajax.reload();
					}

					$serviceJSON('action/promise/create','getOnePromesses',[data.id],function(r){
						$('#count-promesses').html(r +' €');
					});


				});
                $serviceJSON('action/promise/create','send',[email],function (result) {
                    if(result&&result.error){
                        sweetAlert({
                            'title' : result.error,
                            'type'  : "error",
                            'timer' : 2500
                        });
                        $.notify('Votre email n\'a pas été été envoyé !');
                    }
                    else{

                        $.notify('Votre email a bien été envoyé !',"success");
                        if($('#historique-table').length){
                            $('#historique-table').DataTable().ajax.reload();
                        }
                    }
                });

			}
		});


		var table = $('#paperworks-table-promise');
		
		var selectPaperworksData = {};
		var date = new Date();
		let updatemessage = function () {
			data.promise.message = 'promesse de reglement de ' + data.promise.debit + ' enregistrée le '+ data.promise.date_reglement + ' date de réglement theorique le : ' + data.promise.solutionner ;
			CKEDITOR.instances['editeur-one-promise'].setData( data.promise.message );
		};



		$('[name=date_reglement]').on('j:input', updatemessage);
		$('[name=payment_type]').on('j:input', function(){
			data.promise.solutionner = data.promise.date_reglement;
			let mode = $(this).val();
			let solutionner = data.promise.solutionner;
			let sendPayment = '';
			switch(mode){
				case 'check':
					sendPayment = moment(solutionner, "DD/MM/YYYY").add(7, 'days');
					data.promise.solutionner = moment(sendPayment).format("DD/MM/YYYY");
					break;
				case 'transfert':
					sendPayment = moment(solutionner, "DD/MM/YYYY").add(5, 'days');
					data.promise.solutionner = moment(sendPayment).format("DD/MM/YYYY");
					break;
				case 'mandat':
					sendPayment = moment(solutionner, "DD/MM/YYYY").add(10, 'days');
					data.promise.solutionner = moment(sendPayment).format("DD/MM/YYYY");
					break;
				case 'cash':
					sendPayment = moment(solutionner, "DD/MM/YYYY").add(10, 'days');
					data.promise.solutionner = moment(sendPayment).format("DD/MM/YYYY");
					break;
				case 'credit_card':
					sendPayment = moment(solutionner, "DD/MM/YYYY").add(5, 'days');
					data.promise.solutionner = moment(sendPayment).format("DD/MM/YYYY");
					break;
				default:
					sendPayment = solutionner;
					break;
			}
			updatemessage();
		});

		table.on('j:input','[name="_many2many_paperwork[]"]',function(){
			
			let datatable = table.DataTable();
			
			table.find('>tbody>tr').each(function(){
				var rowData = datatable.row(this).data();
				if($(this).find('.select_row').prop('checked')){
					selectPaperworksData[rowData.id] = rowData;
				}
			});
			
			data.amountNumber = 0;

			$.each(data.promise._many2many_paperwork,function(k,id){
				if(id){
					data.amountNumber += Number(selectPaperworksData[id].debit.replace(',','.'));
				}
			});
			data.promise.debit = data.amountNumber.toFixed(2).replace('.',',')+'€';
			data.promise.amount = data.amountNumber.toFixed(2).replace('.',',');
			updatemessage();
		});

		
	}
};
