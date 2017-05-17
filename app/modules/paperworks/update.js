import 'validate';

import Module from 'module';
export default class extends Module {
	templateUrl(){ return 'paperworks/update'; }
	getData(){
		let params = jstack.url.getParams(this.hash);
		let id = params.id;
		let breadcrumb = params.breadcrumb;
		this.data.breadcrumb = breadcrumb;
		return [
			$serviceJSON('paperwork/update','load',[id,breadcrumb]),
		];
	}
	domReady(){
		

	}
};
