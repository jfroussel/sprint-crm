import Module from 'module';
export default class extends Module {
	templateUrl(){ return 'home/tab-actions'; }
    getData(){
        let id = jstack.url.getParams(this.hash).id;
        return [
        ];
    }
    setData(agenda){
        var data = this.data;
        var el = $(this.element);
        var tabAgenda = $('#agenda > div');
        data.currentDate = tabAgenda.data('jModel').currentDate;
    }
    domReady(){

    }
    
};
