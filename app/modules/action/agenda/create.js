import 'validate';
import 'notify-js';
import 'app.datatables';

import Module from 'module';
export default class extends Module {
	templateUrl(){ return 'action/agenda/create'; }
	getData(){
        let id = jstack.url.getParams(this.hash).id;
        if(!id){
            id = $(this.element).data('datatable-edit-id');
        }
        this.data.debtor_id = id;
        return [
            $serviceJSON('action/agenda/create','load',[ id ]),
        ];
	}
	setData(json){
		$.extend(this.data,json);
		var data = this.data;

		data.agenda = {
			debtor_id:data.debtor_id,
			category:'agenda',
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
                data.agenda.timer =  secondsToMinutes(sec);
				$serviceJSON('action/agenda/create','store',[data.agenda],function(r){
					$('#debtor-agenda').modal('hide');
                    $.notify('Votre note Agenda a bien été enregistrée !',"success");
					if($('#historique-table').length){
						$('#historique-table').DataTable().ajax.reload();
					}
				});
			}
		});


	}
};
