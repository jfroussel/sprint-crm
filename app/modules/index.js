import Module from "module";
import 'adapters/fullcalendar';

export default class extends Module {
	templateUrl(){
		return 'index';
	}
	getData(){
		return [
			$serviceJSON('','load'),
		];
	}
	domReady(){
		let data = this.data;
        let componentUrl;
		switch(data.user.type){
			case 'lead':
				componentUrl = 'home/lead';
			break;
			case 'outsourcing':
				componentUrl = 'home/outsourcing';
			break;
			case 'marketplace':
				componentUrl = 'home/marketplace';
			break;
            case 'promise':
                componentUrl = 'home/promise';
                break;
			default:
				componentUrl = 'home/saas';
			break;
		}
		let target = $('<div/>');
		jstack.load(target.appendTo(this.element),{
			component: componentUrl,
		});
	}
};
