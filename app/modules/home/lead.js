import moment from 'moment';

import Module from 'module';
export default class extends Module {
	templateUrl(){ return 'home/lead'; }
	getData(){
		return [
			$serviceJSON('home/lead','load'),
		];
	}
	domReady(){
        let self = this;
        let data = self.data;
	}
};
