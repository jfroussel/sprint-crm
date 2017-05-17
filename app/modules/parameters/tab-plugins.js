import 'validate';

import Module from 'module';
export default class extends Module {
	templateUrl(){ return 'parameters/tab-plugins'; }
    getData(){
        return [
        ];
    }
    domReady(){
        var self = this;
        var data = self.data;
        var element = self.element;

        var button = $('#plugin-email');

        button.on('click', function(e){
           if($(this).hasClass('btn-success')){
               $(this).removeClass('btn-success').addClass('btn-primary').text('desactivez le plugin');

           }else{
               $(this).removeClass('btn-primary').addClass('btn-success').text('activez le plugin');
           }
        });

    }
};
