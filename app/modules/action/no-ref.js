import 'validate';

import Module from 'module';
export default class extends Module {
	templateUrl(){ return 'action/no-ref'; }
    getData(){
        this.data.action = jstack.url.getParams(this.hash).action;
        return [

        ];
    }
    domReady(){

    }
};
