import 'validate';
import 'notify-js';

import Module from 'module';
export default class extends Module {
	
	templateUrl(){ return 'users/update'; }
	
	getData(){
		return [
			$serviceJSON('users/update','load',[jstack.url.getParams(this.hash).id]),
		];
	}
	
	setData(userRead){
		var data = this.data;

		data.id = jstack.url.getParams(this.hash).id;
		$.extend(data,userRead);
		if(!data.user){
			jstack.route.load('errors/not-found');
			return false;
		}
	}
	
	domReady(){
		var data = this.data;
        console.log(data);
		var controller = this; 
		var form = $(controller.element).find('form');
		form.validate({
			submitHandler: function(){
				controller.element.css('opacity',0.7);
				$serviceJSON('users/update','store',[data.user],function(update){
					$('.profile-pic img').attr('src',data.avatar);
					$.extend(data,update);
					controller.element.css('opacity',1);
					$.notify('La fiche a bien été mise à jour', "success");
                    jstack.route('home/tab-home');
				});
			},
			rules:{
				email: {
					email:true,
					remote: {
						url:'users/update.json',
						type:'post',
						data: {
							method:'checkEmail',
							params:[
								function(){
									return data.user.email;
								},
								data.user.id
							]
						}
					}
				}
			},
			messages:{
				email: {
					remote: "Cette adresse email est déjà utilisée !"
				}
			}
		});
	}
};
