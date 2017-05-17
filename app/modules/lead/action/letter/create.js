import 'validate';
import 'notifyjs';
import 'app.datatables';

import Module from 'module';
export default class extends Module {
	templateUrl(){ return 'lead/action/letter/create'; }
	getData(){
        let id = jstack.url.getParams(this.hash).id;
        if(!id){
            id = $(this.element).data('datatable-edit-id');
        }
        this.data.id = id;
        return [
            $serviceJSON('action/letter/create','load',[ id ]),
        ];
	}
	setData(json){
		$.extend(this.data, json);
		let data = this.data;
        data.seconds = '0';
        data.minutes = '0';
	}
	domReady(){
		var self = this;
		
		var data = this.data;
		let letter = data.letter;
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
                data.letter.timer =  secondsToMinutes(sec);
                $serviceJSON('action/letter/create','send',[letter.debtor_id,letter.type,letter.message,letter.contact_id,letter.template_id,letter.timer],function (result) {
                    if(result&&result.error)
                        sweetAlert({
                            'title' : result.error,
                            'type'  : "error",
                            'timer' : 2500
                        });
                    else{
                        $('#debtor-letter').modal('hide');
                        $.notify('Votre courrier a bien été enregistré ! ' ,"success");

                        if($('#historique-table').length){
                            $('#historique-table').DataTable().ajax.reload();
                        }
                        window.open(result, '_blank', 'fullscreen=yes');
                    }
                });

            }
        });

        var table = $('#paperworks-table-letter');


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
			$.each(data.letter._many2many_paperwork,function(k,id){
				if(id){
					data.amountNumber += Number(selectPaperworksData[id].debit.replace(',','.'));
				}
			});
			data.letter.debit = data.amountNumber.toFixed(2).replace('.',',')+'€';
		});

        $('[name=template_id],[name=contact_id], [name=type]', this.element).on('j:input', function(e){
			var id = data.letter.template_id;
			data.letter.message = id?data.templatesList[id].message:'';
			var letter_preview = $('.letter-preview');
			letter_preview.empty();
			if(!data.letter.contact_id || !data.letter.template_id || !data.letter.type){
				return;
			}

			$serviceJSON('action/letter/create', 'getTemplateRender', [data.letter.message, data.letter], function(message){
				letter_preview.html(message);
			});
			
        });




		
	}
};
