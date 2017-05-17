import 'validate';

import Module from 'module';
export default class extends Module {
	
	templateUrl(){ return 'parameters/billing/update'; }
	
    getData(){
        var id = jstack.url.getParams(this.hash).id;
		this.data.id = id;
        return [
            $serviceJSON('billing/update','load',[id]),
        ];
    }
    domReady(){

        var self = this;
        var data = this.data;
        var el = $(self.element);

        $('#html2pdf').on('click', function(e){
           e.preventDefault();
           $(document.body).append('<iframe style="display:none;" src="parameters/billing/update?method=html2pdf&id='+ data.id +'"></iframe>');
        });

    }
};
