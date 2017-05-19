import Module from 'module';

import 'validate';
import 'adapters/fullcalendar';

export default class extends Module {
	templateUrl(){ return 'home/tab-agenda'; }
    getData(){
        var id = jstack.url.getParams(this.hash).id;
        let data = this.data;
        data.debtor_id = data.id;
        return [
            $serviceJSON('home/tab-agenda','load'),
        ];
    }
    domReady(){
        var el = this.element;
        var data = this.data;

		var calendar =  $('#calendar').fullCalendar({

			height: 750,
			locale: 'fr',
            lang: 'fr',
            eventColor: '#343b47',
			defaultView: 'month',
            editable: true,
            startEditable:true,
			header:
			{
				left: 'prev,next today',
				center: 'title',
				right: 'month,agendaWeek,agendaDay'
			},


            dayClick: function(date,allDay, jsEvent, view) {
			    var tab = 'actions';
                var myDate = date.format();
                data.currentDate = date.format();
                    $('.tab-nav a[href="#' + tab + '"]').tab('show');
            },
            eventClick: function(calEvent, jsEvent, view) {
                var tab = 'actions';
                var myDate = calEvent.start.format();
                data.currentDate = myDate;
                $('.tab-nav a[href="#' + tab + '"]').tab('show');
            },
            eventRender: function (event, element, view) {
                    element.find('.fc-title').append('<div class="hr-line-solid-no-margin"></div><span style="font-size: 12px">Emails : '+ (event.count_email || 0)+'<br> Appels : '+ (event.count_appel || 0)+'<br> Courriers : '+ (event.count_courrier || 0)+'<br> Taches : '+ (event.count_agenda || 0)+'<br> Alertes : '+ (event.count_alerte || 0)+'<br> Tâches : '+ (event.count_tache || 0)+'<br> Fax : '+ (event.count_fax || 0)+' <hr> TOTAL : '+ (event.total || 0)+'</span></div>');
            },

            events: function(start, end, timezone, callback, message) {
                var events = [];

                $.each(data.agenda, function (index, value) {
                    events.push(value);
                });
                callback(events);
            },

		});
	}
};
