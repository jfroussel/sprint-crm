import 'validate';

import Module from 'module';
export default class extends Module {
	templateUrl(){ return 'parameters/tab-files-import'; }
	getData(){
		return [
			$serviceJSON('parameters/tab-file-import','load'),
		];
	}
	domReady(){
		
		var data = this.data;
		var el = this.element;
		
		var pendingInterval = {};
		var pendingXhr = {};
		var handlePendingDisplay = function(type){
			var state = data.state[type];
			var importManual = el.find('.import-manual[data-table="'+type+'"]');
			var a = importManual.find('.launch');
			var iconRunning = importManual.find('.running');
			var iconQueued = importManual.find('.queued');
			if(pendingInterval[type]){
				clearInterval(pendingInterval[type]);
			}
			var launchInterval = function(){
				pendingInterval[type] = setInterval(function(){
					if(pendingXhr[type]&&pendingXhr[type].readyState!=4) pendingXhr[type].abort();
					pendingXhr[type] = $serviceJSON('parameters/tab-file-import','checkState',[type],function(state){
						if(data.state[type]!=state){
							data.state[type] = state;
							handlePendingDisplay(type);
						}
					});
				},2000);
			};
			if(state=='queued'){
				a.hide();
				iconRunning.addClass('hide');
				iconQueued.removeClass('hide');
				launchInterval();
			}
			else if(state=='running'){
				a.hide();
				iconQueued.addClass('hide');
				iconRunning.removeClass('hide');
				launchInterval();
			}
			else{
				a.show();
				iconQueued.addClass('hide');
				iconRunning.addClass('hide');
			}
		};
		
		var upload = function(form){
			
			var uploadData = jstack.dotGet(data,form.attr('j-name'));

			
			uploadData.type = form.attr('data-type');
			uploadData.method = 'upload';
			
			var loader = form.find('.import-loader');
			var success = form.find('.import-success');
			var failed = form.find('.import-failed');
			var submit = form.find('button[type=submit]');
			if(submit.hasClass('disabled')) return;
			submit.addClass('disabled');
			success.hide();
			failed.hide();
			loader.show();
			return jstack.ajax({
				url: 'parameters/tab-file-import.json',
				method: 'POST',
				data: uploadData,
				dataType: 'json',
				success: function(r){
					var input = form.find('input[type=file]');
					input.replaceWith(input.clone());
					loader.hide();
					if(r==true){
						success.show();
					}
					else{
						failed.show();
					}
					$serviceJSON('parameters/tab-file-import','getRenewDataUpload',function(json){
						$.extend(data,json);
					});
					submit.removeClass('disabled');
				},
				error:function(){
					loader.hide();
					failed.show();
					submit.removeClass('disabled');
				}
			});
		};
		
		handlePendingDisplay('debtor');
		handlePendingDisplay('paperwork');
        handlePendingDisplay('contact');
		
		var formDebtor = el.find('form.parameters-import-debtor');
		var formPaperwork = el.find('form.parameters-import-paperwork');
        var formContact = el.find('form.parameters-import-contact');
		var formsImport = $([formDebtor,formPaperwork,formContact]);
		
		el.on('j:change','input[name=file][type=file]',function(){
			var btn = $(this);
			//btn.prop('disabled',true);
			var form = btn.closest('form');
			upload( form ).then(function(){
				//btn.prop('disabled',false);
			});
		});
		
		el.find('[data-toggle="tooltip"]').each(function(){
			var getSelector = $(this).attr('data-get-html');
			var options = {
				container: 'body',
			};
			if(getSelector){
				var getDiv = el.find(getSelector);
				getDiv.hide();
				options.html = true;
				options.title = getDiv.html();
			}
			$(this).tooltip(options);
		});
		
		el.find('.import-manual').each(function(){
			var xhr;
			var table = $(this).attr('data-table');
			$(this).find('.launch').click(function(e){
				e.preventDefault();
				if(xhr&&xhr.readyState!=4) return false;
				
				var icon = $(this).find('i');
				icon.addClass('zmdi-hc-spin');
				xhr = $serviceJSON('parameters/tab-file-import','importManual',[table],function(json){
					icon.removeClass('zmdi-hc-spin');
					data.state[table] = json.state;
					handlePendingDisplay(table);
				});
				return false;
			});
		});
		
		el.find('form.importmap').each(function(){				
			var form = $(this);
			var scope = form.attr('j-name').split('.')[1];
			form.on('j:change',':input[name]',function(){
				var input = $(this);
				if(input.isValid()||!input.val()){
					var params = {};
					var name = input.attr('name');
					params[scope] = {};
					params[scope][name] = input.val();
					$serviceJSON('parameters/tab-file-import','storeImportMap',[params]);
				}
			});
			form.validate();
		});

	}
};
