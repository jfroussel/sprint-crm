import moment from 'moment';
import 'validate';
import 'notify-js';
import 'app.datatables';

import Module from 'module';
export default class extends Module {
	templateUrl(){ return 'lead/action/schedule/create'; }
    getData(){
        let id = jstack.url.getParams(this.hash).id;
        if(!id){
            id = $(this.element).data('datatable-edit-id');
        }
        this.data.id = id;
        return [
            $serviceJSON('action/schedule/create','load',[ id ]),
        ];
    }
	setData(json){
		$.extend(this.data,json);
		let data = this.data;

        data.seconds = '0';
        data.minutes = '0';

    	data.schedule = {
			debtor_id:data.id,
			category:'echeancier',
			_xmany_deadline: [],
			rest: null,
			base_schedule: null,
			date_first_schedule: '',
			build_method: 'auto',
			nbschedule: null,
			late: null,
		};

		data.renderMethod = 'none';
	}
	domReady(){

		let data = this.data,
		self = this,
		form = $(this.element).find('form');

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
                data.schedule.timer =  secondsToMinutes(sec);
				let sendSchedule = false;
				let d = data.schedule;
				switch(d.build_method) {
					case 'auto':
						if(d.base_schedule > 0 && d.date_first_schedule.length > 0 && d.amount > 0 && d.nbschedule > 0) {
							sendSchedule = true;
						}
						break;

					case 'manual':
						if(d.base_schedule > 0 && Number(d.rest) == 0 && d._xmany_deadline.length > 0) {
							sendSchedule = true;
						}
						break;
				}
				if(sendSchedule === true) {
					$serviceJSON('action/schedule/create','store',[data.schedule],function(r){
						$('#debtor-schedule').modal('hide');
						$.notify('Votre écheancier a bien été enregistré', "success");
						if($('#historique-table').length){
							$('#historique-table').DataTable().ajax.reload();
						}
						if($('#schedules-table').length){
							$('#schedules-table').DataTable().ajax.reload();
						}

						$.ajax({
							url:'debtors.json?method=getOneEcheances',
							data:{params:[data.id]},
							dataType:'JSON',
							type:'POST',
							success:function(r){
								$('#count-echeances').html(r);
							}
						});
					});
				}
			}
		});

		let table = $('#paperworks-table-schedule');

		let datatable = table.DataTable(),
		selectPaperworksData = {};

		table.on('j:input:user','[name="_many2many_paperwork[]"]',function(){

			table.find('>tbody>tr').each(function(){
				let rowData = datatable.row(this).data();
				if($(this).find('.select_row').prop('checked')){
					selectPaperworksData[rowData.id] = rowData;
				}
			});

			data.amountNumber = 0;
			$.each(data.schedule._many2many_paperwork,function(k,id){
				if(id){
					data.amountNumber += Number(selectPaperworksData[id].debit.replace(',','.'));
				}
			});
			data.schedule.debit = data.amountNumber.toFixed(2).replace('.',',')+'€';

			data.schedule.base_schedule = data.amountNumber.toFixed(2);

			self.updateRest();

			if(data.renderMethod.length !== 0 && data.renderMethod!=='none') {
				self.updateOrNot();
			}
		});

		let keys = ['base_schedule', 'date_first_schedule', 'deadline_amount', 'late', 'date_deadline'];

		data.schedule.modelObserve(keys, function(change) {

			let d = data.schedule;

			switch(change.key) {
				case 'base_schedule':
					self.updateOrNot();
					self.updateRest();
					break;

				case 'date_first_schedule':
					self.checkDateAndNow('first');
					self.updateOrNot();
					break;

				case 'deadline_amount':
					if( Number(d.deadline_amount) > Number(d.rest) ) {
						d.deadline_amount = d.rest;
					}
					break;

				case 'late':
					if( Number(change.value) > Number(change.oldValue) ) {
						d.base_schedule = Number(d.base_schedule) + Number(change.value - change.oldValue);
						d.base_schedule = d.base_schedule.toFixed(2);
					} else {
						d.base_schedule = Number(d.base_schedule) - Number(change.oldValue - change.value);
						d.base_schedule = d.base_schedule.toFixed(2);
					}

					self.updateOrNot();
					self.updateRest();
					break;

				case 'date_deadline':
					self.checkDateAndNow('deadline');
					break;
			}
		});

		$('[name=nbschedule]').on('j:input:user', function() {
			data.renderMethod = 'countEcheance';
			self.countEcheance();
		});

		$('[name=amount]').on('j:input:user', function() {
			data.renderMethod = 'amount';
			self.amountJob();
		});
	}
	
	checkDateAndNow(type) {

		let date;

		switch(type) {
			case 'first':
				if(this.data.schedule.date_first_schedule.length > 0)
					date = this.data.schedule.date_first_schedule;
				else
					return false;
				break;

			case 'deadline':
				if(this.data.schedule.date_deadline.length > 0)
					date = this.data.schedule.date_deadline;
				else
					return false;
				break;
		}

		if(moment(date, "DD/MM/YYYY").isBefore(moment(moment(), "DD/MM/YYYY"), 'day')) {

			if(type === 'first') {
				this.data.schedule.date_first_schedule = '';
				this.showError('error_auto', 'Veuillez selectionner une date égale ou supérieur à aujourd\'hui.');
			}else if(type === 'deadline') {
				this.data.schedule.date_deadline = '';
				this.showError('add_error', 'Veuillez selectionner une date égale ou supérieur à aujourd\'hui.');
			}
		}else {
			let errorClass = type==='first' ? '.error_auto' : '.add_error';
			$(errorClass).empty();
		}
	}
	updateOrNot() {
		if(this.data.renderMethod!=='none')
			switch(this.data.renderMethod) {
				case 'amount':
					this.amountJob();
					break;

				case 'countEcheance':
					this.countEcheance();
					break;
			}
	}
	countEcheance() {
		let d = this.data.schedule;
		if( !d.base_schedule || !d.base_schedule.length ) {
			this.showError('error_auto', 'Veuillez selectionner une ou plusieurs factures pour calculer vos echeances.');
			d.nbschedule = null;
		}else if( !d.date_first_schedule || !d.date_first_schedule.length ) {
			this.showError('error_auto', 'Veuillez selectionner une date à partir de laquelle calculer vos echeances.');
			d.nbschedule = null;
		}else{
			if(!d.nbschedule.length) {
				d.amount = 0;
				return false;
			}
			d.amount = (d.base_schedule/d.nbschedule).toFixed(2);
		}
	}
	amountJob() {
		let d = this.data.schedule;
		if( !d.base_schedule || !d.base_schedule.length ) {
			this.showError('error_auto', 'Veuillez selectionner une ou plusieurs factures pour calculer vos echeances.');
			d.amount = null;
		}else if( !d.date_first_schedule || !d.date_first_schedule.length ) {
			this.showError('error_auto', 'Veuillez selectionner une date à partir de laquelle calculer vos echeances.');
			d.amount = null;
		}else{
			if( !d.amount.length ) {
				d.nbschedule = 0;
				return false;
			}
			d.nbschedule = Math.floor(d.base_schedule / d.amount);
		}
	}
	updateRest() {
		let d = this.data.schedule;
		if ( d._xmany_deadline.length > 0 ) {
			let m = 0;
			for( let i=0; i<d._xmany_deadline.length; i++ ) {
				m += Number(d._xmany_deadline[i][0]);
			}
			d.rest = Number(d.base_schedule - m).toFixed(2);
		} else {
			d.rest = Number(d.base_schedule);
		}
	}
	showError(a, b) {
		$( '.'+a ).text(b);
	}
	checkDate(date) {
		let d = this.data.schedule._xmany_deadline;
		if(d.length) {
			let oldDate = d[d.length-1][1];
			return moment(oldDate, "DD/MM/YYYY").isBefore(moment(date, "DD/MM/YYYY"));
		}else {
			return true;
		}
	}
	checkAmount(amount) {
		return Number(amount) <= Number(this.data.schedule.rest);
	}
	
	//API
	addDeadline() {
		let d = this.data.schedule;
		let amount = d.deadline_amount;
		let date = d.date_deadline;

		if(!amount || amount <= 0) {
			this.showError('add_error', 'Veuillez indiquer un montant pour l\'échéance.');
			return false;
		}else{
			$('.add_error').empty();
		}

		if(d._xmany_deadline.length == 0) {
			$('[name="late"]').attr('disabled', 'disabled');

			if(d.late == null)
				d.late = 0;
		}

		if(!this.checkDate(date)) {
			this.showError('add_error', 'Veuillez indiquer une date supérieur à la date de votre dernière échéance.');
		}else if(!this.checkAmount(amount)) {
			this.showError('add_error', 'Veuillez indiquer un montant inférieur ou égale au restant.');
		}else{
			d._xmany_deadline.push([amount, date]);
			this.updateRest();
		}
	}
	deleteDeadline(e, el) {
		let d = this.data.schedule;
		let id = $(el).attr('data-increment');
		d._xmany_deadline.splice(id, 1);
		this.updateRest();
	}
	clearAutomatic() {
		let d = this.data.schedule;
		d.amount = '';
		d.nbschedule = '';
		d.date_first_schedule = '';
		d.rest = d.base_schedule > 0 ? d.base_schedule : 0;
		d.build_method = 'manual';
	}
	clearManual() {
		let d = this.data.schedule;
		d._xmany_deadline.length = 0;
		d.rest = '';
		d.deadline_amount = '';
		d.date_deadline = '';
		d.build_method = 'auto';
	}
};
