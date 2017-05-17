import 'validate';

import Module from 'module';
export default class extends Module {
	templateUrl(){ return 'users/update_user'; }
	getData(){
		var id = jstack.url.getParams(this.hash).id;
		return [
			$serviceJSON('users/update', 'load',[id]),
		];
	}
	
	setData(json){
		var data = this.data;
		data.id = jstack.url.getParams(this.hash).id;
        $.extend(data,json);
		if(!data.user){
            jstack.route.load('errors/not-found');
            return false;
        }
    }
    
	domReady(){
		var data = this.data;
		var el = $(this.element);
		el.find('form').validate({
			submitHandler: function(){
				el.css('opacity',0.7);
				$serviceJSON('users/update','store', [data.user],function(json){
					$.extend(data,json);
					if(data.userIsHimself){
						$('.profile-pic img').attr('src',data.avatar);
					}
					el.css('opacity',1);
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
