import Module from 'module';
export default class extends Module {
	templateUrl(){ return 'lead/all'; }
	getData(){
		return [
            $serviceJSON('lead/all','load'),
		];
	}
	domReady(){

        let self = this;
        let data = self.data;

		let handleStatusColor = function(el){
			el = $(el);
			var val = el.val();
			var color = el.find('option[value="'+val+'"]').attr('data-color');
			var colorize = el.closest('tr');
			colorize.removeClassPrefix('m-bg-');
			colorize.addClass('m-bg-'+color);
		};

		$('#leads-table').data('jstackDatatablesAddons',{
			selectOptions:{
				status: {
					'':{
						label:'attente',
						'data-color':'white-light',
						'class':'m-bg-white',
					},
					'checked':{
						label:'verifié',
						'data-color':'orange-light',
						'class':'m-bg-white-light',
					},
					'affected':{
						label:'affecté',
						'data-color':'blue-light',
						'class':'m-bg-white-light',
					},
                    'running':{
                        label:'en cours',
						disabled:true,
                        'data-color':'green-light',
                        'class':'m-bg-white-light',
                    },
					'completed':{
						label:'terminé',
						'data-color':'green-light',
						'class':'m-bg-white-light',
					}
				}
			},
			selectInit:{

				status: function(){
					handleStatusColor(this);
					console.log($(this).val());
					// if($(this).val()=='running'){
					if($(this).attr('data-init-value')=='running'){

						$(this).find('option').each(function(){
							switch($(this).attr('value')){
								case 'completed':
								case 'running':
								break;
								default:
									$(this).prop('disabled', true);
								break;
							}
						});
					}
				}
			},
			selectChange:{
				status: function(data){
					handleStatusColor(this);
					var val = $(this).val();
					$serviceJSON('lead/all','store',[{id:data.id,status:val}]);
				}
			}
		});
		


	}
};
