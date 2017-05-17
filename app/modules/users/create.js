import 'validate';
import 'notifyjs';

import Module from 'module';
export default class extends Module {
	templateUrl(){ return 'users/create'; }
	getData(){
		return [
		
		];
	}
	setData(){
		let data = this.data;
		data.user = {
			type:'saas',
		};
	}
	domReady(){
		var data = this.data;
		var form = $(this.element).find('form');
		form.validate({
			submitHandler: function(){
				$serviceJSON('users/create','store',[data.user],function(r){
					// if(r.id){
					// 	jstack.route('users/update',{id:r.id});
					// }
                    jstack.route('users/all');
                    $.notify('Votre utilisateur a bien été créé', "success");
				});

			},
			rules:{
				email: {
					email:true,
					remote: {
						url:'users/create.json',
						type:'post',
						data: {
							method:'checkEmail',
							params:function(){
								return [form.find('input[name=email]').val()];
							}
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
