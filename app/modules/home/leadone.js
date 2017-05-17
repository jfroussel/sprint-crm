import 'validate';

import Module from 'module';
export default class extends Module {
	templateUrl(){ return 'home/leadone'; }
    getData(){
        return [
            $serviceJSON('home/leadone','load',[jstack.url.getParams(this.hash).id]),
        ];
    }
    domReady(){
		
    }
};
