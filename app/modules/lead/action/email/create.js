import 'validate';
import 'notifyjs';
import 'app.datatables';

import Module from 'module';
export default class extends Module {
	templateUrl(){ return 'lead/action/email/create'; }
	getData(){
        let id = jstack.url.getParams(this.hash).id;
        if(!id){
            id = $(this.element).data('datatable-edit-id');
        }
        this.data.id = id;
        return [
            $serviceJSON('action/email/create','load',[ id ]),
        ];
	}
	setData(json){
        $.extend(this.data,json);

        let data = this.data;
        data.seconds = '0';
        data.minutes = '0';
	}
	domReady(){
	    //var el = this.element;
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


		let email = data.email;
        form.validate({
            submitHandler: function(){
                clearInterval(timing);
                data.email.timer =  secondsToMinutes(sec);
                $serviceJSON('action/email/create','send',[email],function (result) {
                    if(result&&result.error){
                        sweetAlert({
                            'title' : result.error,
                            'type'  : "error",
                            'timer' : 2500
                        });
                        $.notify('Votre email n\'a pas été été envoyé !');
                    }
                    else{

                        $('#debtor-email').modal('hide');
                        $.notify('Votre email a bien été envoyé !',"success");
                        if($('#historique-table').length){
                            $('#historique-table').DataTable().ajax.reload();
                        }
                    }
                });

            }
        });


        var table = $('#paperworks-table-email');
       

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
			$.each(data.email._many2many_paperwork,function(k,id){
				if(id){
					console.log(id);
					data.amountNumber += Number(selectPaperworksData[id].debit.replace(',','.'));

				}
			});
			data.email.debit = data.amountNumber.toFixed(2).replace('.',',')+'€';
		});


        $('[name=template_id]', this.element).on('j:input', function(e){
			
			var id = data.email.template_id;
			data.email.message = id?data.templatesList[id].message:'';
			var email_preview = $('.email-preview');
			email_preview.empty();

			$serviceJSON('action/email/create', 'getTemplateRender', [data.email.message, data.email], function(message){
				message = message.replaceAll('{{', '{').replaceAll('}}', '}');
				console.log(message, email_preview);
				email_preview.html(message);
			});
			
        });

	}
};
