import Module from 'module';
export default class extends Module {
	templateUrl(){ return 'lead/action/litige/all'; }
	getData(){
		this.data.id = jstack.url.getParams(this.hash).id;
		return [
		];
	}
	domReady(){	
		
	}
};
