import 'jstack';
export default class extends jstack.Component {
	templateUrl(){
		return jstack.url.getPath(this.hash);
	}
	setData(json){
		$.extend(this.data,json);
	}
};
