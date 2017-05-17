import Module from 'module';
export default class extends Module {
	templateUrl(){ return 'home/tab-folders'; }
    getData(){
        var id = jstack.url.getParams(this.hash).id;
        return [
            $serviceJSON('home/tab-folders','load'),
        ];
    }
    domReady(){
		
	}
};
