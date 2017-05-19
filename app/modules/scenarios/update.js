import 'notify-js';

import Module from 'module';
export default class extends Module {
	
	templateUrl(){ return 'scenarios/update'; }
	
	getData(){
		var id = jstack.url.getParams(this.hash).id;
		return [
			$serviceJSON('scenarios/update','load',[id]),
		];
	}
	
	setData(json){
		$.extend(this.data,json);
		let data = this.data;
		data.remove_scenario_step = [];
	}
	
	domReady(){
		
        var self = this;
		var el = this.element;
		var data = this.data;
		var form = el.find('form');

		$(el).on('inputnumber:change','.step-length, input[name="start_day"]',function(){
			self.intervalDaysCalculation();
		});
		$(el).on('inputnumber:change','.end-day',function(){
			var tr = $(this).closest('tr');
			var stepLength = tr.find('input.step-length');
			var startDay = tr.find('.start-day');
			var endDay = tr.find('.end-day');
			var startDayVal = parseInt(startDay.val());
			var endDayVal = parseInt(endDay.val());
			var stepLengthVal = endDayVal-startDayVal;
			stepLength.val(stepLengthVal);
			stepLength.trigger('j:update');
			self.intervalDaysCalculation();
		});

		self.intervalDaysCalculation();

	}
	
	intervalDaysCalculation(){
		var el = this.element;
		var data = this.data;


			
		$(el).find('table.scenario-step-list > tbody > tr').each(function(i){
			var tr = $(this);
			var startDay = tr.find('.start-day');
			var endDay = tr.find('.end-day');
			var stepLength = tr.find('input.step-length');
			
			var startDayVal;
			var endDayVal;
			var index = tr.attr('j-for-id');
			
			if(i==0){
				//startDayVal = 1;
				startDayVal = parseInt(data.scenario.start_day);
				if(isNaN(startDayVal)){
					startDayVal = 1;
				}
			}
			else{
				var prevEndDay = parseInt(tr.prev('tr').find('input.end-day').val());
				if(isNaN(prevEndDay)){
					prevEndDay = 1;
				}
				startDayVal = prevEndDay+1;
			}
			
			var stepLengthVal = parseInt(stepLength.val());
			if(isNaN(stepLengthVal)){
				stepLengthVal = 1;
				stepLength.val(stepLengthVal);
			}
			endDayVal = startDayVal + stepLengthVal;
			startDay.val(startDayVal);
			endDay.val(endDayVal);
			
		});
	}

	updateScenario(e){
		e.preventDefault();
		let data = this.data;
		$serviceJSON('scenarios/update','store',[data.scenario,data.scenario_step,data.remove_scenario_step]).then(function(json){
			$.extend(data,json);
			jstack.route('scenarios/all');
			$.notify("Votre scenario a bien été enregistré", "success");
		});

	}
	
	addStep(){
		var self = this;
		var data = this.data;
		
		data.scenario_step.push({length:1});
		
		this.dataBinder.ready(function(){
			self.intervalDaysCalculation();
		});
	}

	removeStep(e,el){
		var self = this;
		var data = this.data;
		e.preventDefault();
		e.stopPropagation();
		var tr = $(el).closest('[j-for-id]');
		var index = tr.attr('j-for-id');
		let id = data.scenario_step[index].id;
		if(data.scenario_step[index]){
			data.scenario_step.splice(index,1);
		}
		if(id){
			data.remove_scenario_step.push(id);
		}
		
		this.dataBinder.ready(function(){
			self.intervalDaysCalculation();
		});
		
	}
};
