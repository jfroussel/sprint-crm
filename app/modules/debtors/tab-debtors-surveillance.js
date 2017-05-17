import 'validate';

import Module from 'module';
export default class extends Module {
	templateUrl(){ return 'debtors/tab-debtors-surveillance'; }
    getData(){
        var id = jstack.url.getParams(this.hash).id;
        return [
            $serviceJSON('debtors/update','load', [id])
        ];
    }
    domReady(){
        
    }
};
