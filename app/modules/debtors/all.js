import Module from 'module';
export default class extends Module {
	templateUrl(){ return 'debtors/all'; }
	getData(){
		return [
		];
	}
	getData(){
		return [
			$serviceJSON('debtors/all','load'),
		];
	}
	domReady(){

	}
};
