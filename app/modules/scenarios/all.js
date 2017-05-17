import Module from 'module';
export default class extends Module {
	templateUrl(){ return 'scenarios/all'; }
	getData(){
		return [
		];
	}
	domReady(){
		let data = this.data;
		let el = this.element;
		el.find('.new-scenario').click(function(e){
			e.preventDefault();
			$serviceJSON('scenarios/all','create',function(scenario){
				jstack.route('scenarios/update?id='+scenario.id);
			});
			return false;
		});

	}
};
