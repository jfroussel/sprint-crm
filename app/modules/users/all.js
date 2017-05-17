import Module from 'module';
export default class extends Module {
	templateUrl(){ return 'users/all'; }
	getData(){
		return [];	
	}
	domReady(){

	}
};
