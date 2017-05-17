import 'validate';

import Module from 'module';
export default class extends Module {
	templateUrl(){ return 'parameters/tab-billings'; }
    getData(){
        return [
            $serviceJSON('parameters/tab-billings', 'load'),
        ];
    }
    setData(json){
		$.extend(this.data,json);
    }
    domReady(){
 
    }
};
