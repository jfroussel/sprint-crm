import 'validate';
import 'chart.js';

import Module from 'module';
export default class extends Module {
	templateUrl(){ return 'debtors/tab-scenario'; }
	getData(){
		var id = jstack.url.getParams(this.hash).id;
		this.data.id = id;
		return [
            $serviceJSON('debtors/tab-scenario', 'load',[id]),
		];
	}
	domReady(){
		var self = this;
		var data = self.data;
		var element = self.element;	
	}
	
	updateUseInterval(){
		var data = this.data;
		$serviceJSON('debtors/tab-scenario','updateUseInterval',[data.id,data.use_interval]);
	}
	stopScenario(){
		var self = this;
		var data = self.data;
		$serviceJSON('debtors/tab-scenario','stopRunning',[data.id],function(r){
			if(r){
				data.scenarioIsRunning = false;
				data.scenarioStep = [];
				$('.p-timeline',self.element).remove();
			}
			else{
				throw new Error('unable to stop running scenario');
			}
		});
	}
	scenarioOverride(e,el){
		var self = this;
		var data = self.data;
		$serviceJSON('debtors/tab-scenario','scenarioOverride',[data.id, $(el).val()]);
	}
};
