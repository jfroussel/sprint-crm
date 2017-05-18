import moment from 'moment';

import "jquery";
import "jstack";
//import "datatables";
import "datatables.net";
import "datatables.net-bs";
import "datatables.net-buttons";
import "datatables.net-buttons-bs";
import "datatables-buttons/js/buttons.colVis";
import "datatables-buttons/js/buttons.flash";
import "datatables-buttons/js/buttons.html5";
import "datatables-buttons/js/buttons.print";
import "datatables-buttons/js/buttons.bootstrap";
import "bootstrap3-dialog";

export default jstack.directive( 'datatables' ,class extends jstack.Component{

	domReady(){
	
		let self = this;
		
		let $el = this.element;
		

		
		let config = this.options;
		
		var table;
		var jstackDatatablesAddons = function(){
			return $el.data('jstackDatatablesAddons') || {};
		};
		
		var formats = {
			logo: {
				render:	function(value){
					return '<div class="img-circle-48"><img src="'+value+'"></div>';
				}
			},
			dateformat: function(value) {
				return moment(new Date(value)).format('DD/MM/YYYY');
			},
			dateTimeFormat: function(value) {
				return moment(new Date(value)).format('DD/MM/YYYY à h:mm');

			},
			timestampformat: function(value){
				return moment(value).format('DD/MM/YYYY à h:mm');
			},
			select:{
				render: function(value, column) {
					var options = '';
					var opts = jstackDatatablesAddons().selectOptions[column];
					if(opts instanceof Array){
						var tmp = opts;
						opts = {};
						for(var i = 0; i < tmp.length; i++){
							opts[tmp[i]] = tmp[i];
						}
					}
					for(var k in opts){
						var label;
						var opt = opts[k];
						var option = $('<option></option>')
						option.attr('value',k);
						if(k==value){
							option.attr('selected','selected');
						}
						if(typeof(opt)=='string'){
							option.html(opt);
						}
						else{
							for(var attr in opt){
								if(attr=='label'){
									option.html(opt[attr]);
								}
								else{
									option.attr(attr,opt[attr]);
								}
							}
						}
						options += option[0].outerHTML;
					}

					return '<select class="form-control" data-init-value="'+value+'">'+options+'</select>';

				},
				handler: function(value,column,data,row){
					var select = $(this).find('select');
					var change, init;
					if(jstackDatatablesAddons().selectChange){
						change = jstackDatatablesAddons().selectChange[column];
					}
					if(jstackDatatablesAddons().selectInit){
						init = jstackDatatablesAddons().selectInit[column];
					}
					if(init){
						init.apply(select[0]);
					}
					if(change){
						select.change(function(){
							change.call(this,data);
						});
					}
				},
			},
			active: {
				render:	function(value) {
					let active = value=='1';
					return '<div class="toggle-switch"></label><input type="checkbox" autocomplete="off" hidden="hidden" '+(active?'checked="checked"':'')+'><label class="ts-helper"></label></div>';
				},
				handler: function(value,column,data,row,th){
					
					let url = th.attr('data-url');
					
					let active = value=='1';
					
					let colorActive = 'green';
					let colorInactive = 'red';
					
					let toggle = $(this).find('.toggle-switch');
					let checkbox = toggle.find('input[type=checkbox]');
					let toggleActiveClass = function(){
						if(active){
							toggle.attr('data-ts-color',colorActive);
							checkbox.prop('checked',true);
						}
						else{
							toggle.attr('data-ts-color',colorInactive);
							checkbox.prop('checked',false);
						}
					};
					toggleActiveClass();
					
					
					$(this).click(function(e){
						e.stopPropagation();
						
						let params = {};
						params[column] = active?0:1;
						params.id = data.id;
						$.post(url,{params: [params]},function(){
							active = !active;
							toggleActiveClass();
						});
						
					});
				}
			},
			comment: {
				render: function(value){
					var max = 140;
					var text = $('<div>'+value+'</div>').text();
					if(text.length>max){
						text = text.substr(0,140)+'...(cliquez ici pour lire la suite)';
					}
					return text+'<div style="display:none;">'+value+'</div>';

				},
				handler: function(){
					var title = $(this).find('>div').html();
					$(this).popover({
						html:true,
						title:title,
						animation:true,
						placement: "left",
						trigger: "click",
						container:document.body,
					});
					$(this).hover(function(){
						 $(this).css('cursor','pointer');
					});
				},
			},
			commentOrLink: {
				render: function(value){
					var el = $('<div>'+ value +'</div>');
					if(!el.children('.comment-link').length){
						return formats.comment.render(value);
					}
					return value;

				},
				handler: function(){
					if(!$(this).hasClass('comment-link')){
						return formats.comment.handler.call(this);
					}

				},
			},
		};
		
		var actions = {
			edit: {
				render: function(data,th){
					return '<button type="button" class="btn btn-brown" data-action="edit"><span class="fa fa-pencil"></span></button>';
					
				},
				handler: function(data,th){
					var type = th.attr('data-type') || 0;
					let id = data.id;
					switch(type){
						case 'modal':
							let button = $(this);
							var elId = $el.attr('id');
							var modalId = th.attr('data-target') || elId+'-edit-modal';
							var modalContainer = $('#'+modalId);
							if(!modalContainer.length){
								modalContainer = $('<div class="modal fade" id="'+modalId+'" tabindex="-1" role="dialog" aria-hidden="true"></div>').appendTo($el.closest(':data(jController)'));
							}
							//modalContainer.off('show.bs.modal');
							modalContainer.one('show.bs.modal', function(e){
								var template = th.attr('data-view');
								var inherit = th.attr('data-view-inherit') || th.hasAttr('data-view-inherit');
								var view = '<div class="modal-content"';
								if(inherit===true){
									view += ' j-view-inherit';
								}
								else if(inherit){
									view += ' j-view-inherit="'+inherit+'"';
								}
								view += '></div>';
								view = $(view);
								var div = '<div class="modal-dialog modal-lg"></div>';
								div = $(div);
								div.append(view);
								view.data('datatable-edit-id',id);
								modalContainer.empty();
								div.appendTo(modalContainer);
								jstack.load(div.find('.modal-content'),{
									component: template,
								});
							});
							modalContainer.modal('show');
						break;
						case 'route':
						default:
							var route = th.attr('data-route');
							jstack.route(route+id);
						break;
					}
				},
			},
			remove:{
				render: function(data,th){
					return '<button type="button" class="btn btn-trash" data-action="remove"><span class="fa fa-trash"></span></button>';
				},
				handler: function(data,th){
					var column = th.attr('data-remove-column')||'id';
					var title = th.attr('data-remove-title')||'Supression de « %s »';
					var body = th.attr('data-remove-body')||"Êtes-vous sûr de vouloir supprimer « %s » ?";
					var ok = th.attr('data-remove-ok')||'Supprimer';
					let url = th.attr('data-url') || config.ajax.url;
					var name = data[column];
					BootstrapDialog.show({
						title: title.replace('%s',name),
						message: body.replace('%s',name),
						closable: true,
						buttons: [{
							label: 'Annuler',
							action: function(dialogRef){
								dialogRef.close();
							}
						},{
							label: ok,
							action: function(dialogRef){
								$.ajax({
									method: 'POST',
									url: url,
									data: {
										params: [data.id],
									},
									success: function(response){
										table.ajax.reload();
										dialogRef.close();
									}
								});
							}
						}]
					});
				},
			},
		};

		var columnDefs = [];
		var order = [];
		var visibleTh = [];
		var checkboxSelector = false;
		
		var rows_selected = [];
		var updateDataTableSelectAllCtrl = function(table){
			var $table				 = table.table().node();
			var $chkbox_all		  = $('tbody input[type="checkbox"]', $table);
			var $chkbox_checked	 = $('tbody input[type="checkbox"]:checked', $table);
			var chkbox_select_all  = $('thead input.select_all', $table).get(0);

			// If none of the checkboxes are checked
			if($chkbox_checked.length === 0){
				chkbox_select_all.checked = false;
				if('indeterminate' in chkbox_select_all){
					chkbox_select_all.indeterminate = false;
				}

			// If all of the checkboxes are checked
			} else if ($chkbox_checked.length === $chkbox_all.length){
				chkbox_select_all.checked = true;
				if('indeterminate' in chkbox_select_all){
					chkbox_select_all.indeterminate = false;
				}

			// If some of the checkboxes are checked
			} else {
				chkbox_select_all.checked = true;
				if('indeterminate' in chkbox_select_all){
					chkbox_select_all.indeterminate = true;
				}
			}
		};
		
		$el.find('>thead>tr>th').each(function(i){
			
			var th = $(this);

			if(th.attr('data-visible')!='false'){
				visibleTh.push(th);
			}
			
			var column = th.attr('data-column');
			var action = th.attr('data-action');
			var format = th.attr('data-format');
			var width = th.attr('data-width');
			
			var orderDir = th.attr('data-order');
			if(orderDir){
				order.push([ i, orderDir ]);
			}
			
			var columnDef;
			
			if(format){
				columnDef = {
					targets: [i],
					data: column,
					render: function(data){
						var formatter = formats[format];
						if(typeof(formatter)=='object'){
							formatter = formatter.render;
						}
						return formatter.call(null,data,column,th);
					},
				};
			}
			else if(column){
				columnDef = {
					targets:i,
					data: column
				};
			}
			else if(action){
				columnDef = {
					targets: [i],
					data: null,
					searchable: false,
					orderable: false,
					render: function(data){
						return actions[action].render(data,th);
					},
				};
			}
			else if(i==0){
				//legacy but work like as well as bullshit
				//var columnDef = {
					//orderable: false,
					//className: 'select-checkbox',
					//targets:   i
				//};
				//columnDefs.push(columnDef);
				//select = true;
				//select = {
					//style:    'os',
					//selector: 'td:first-child'
				//};
				
				//http://www.gyrocode.com/articles/jquery-datatables-checkboxes/
				checkboxSelector = true;
				th.append('<input class="select_all" value="1" type="checkbox">');
				columnDef = {
					targets: i,
					searchable: false,
					orderable: false,
					width: '1%',
					className: 'dt-body-center',
					data: null,
					render: function (data, type, full, meta){
						return '<input class="select_row" type="checkbox" name="'+config.checkboxName+'[]" value="'+full.id+'">';
					}
				};
				$el.addClass('datatables-checkbox-selectable');
			}
			
			if(th.attr('data-visible-in-selection')=='false'){
				columnDef = {
					targets: [ i ],
					visible: false,
					searchable: false
				};
			}
			
			if(columnDef){
				columnDef.width = width;
				columnDefs.push(columnDef);
			}
			
		});
		
		if(config.order){
			var order = config.order.trim();
			if(order.indexOf('[')!==-1){
				order = JSON.parse(order);
			}
			else{
				var sort = config.sort||'asc';
				if(isNaN(order)){
					order = $el.find('>thead>tr>th[data-column="'+order+'"]').index();
				}
				else{
					order = Number(order);
				}
				order = [[order,sort]];
			}
			config.order = order;
		}
		
		config = $.extend(true,{
			
			ajax: {
				//type: "POST",
			},

			columnDefs: columnDefs,
			bProcessing: true,
			bServerSide: true,
			language:{
				"sProcessing":     "Traitement en cours...",
				//"sSearch":         "Rechercher&nbsp;:",
				"sSearch":         "",
				"sLengthMenu":     "Afficher _MENU_ &eacute;l&eacute;ments",
				"sInfo":           "Affichage de l'&eacute;l&eacute;ment _START_ &agrave; _END_ sur _TOTAL_ &eacute;l&eacute;ments",
				"sInfoEmpty":      "Affichage de l'&eacute;l&eacute;ment 0 &agrave; 0 sur 0 &eacute;l&eacute;ment",
				"sInfoFiltered":   "(filtr&eacute; de _MAX_ &eacute;l&eacute;ments au total)",
				"sInfoPostFix":    "",
				"sLoadingRecords": "Chargement en cours...",
				"sZeroRecords":    "Aucun &eacute;l&eacute;ment &agrave; afficher",
				"sEmptyTable":     "Aucune donn&eacute;e disponible dans le tableau",
				"oPaginate": {
					//"sFirst":      "Premier",
					//"sPrevious":   "Pr&eacute;c&eacute;dent",
					//"sNext":       "Suivant",
					//"sLast":       "Dernier"
					"sFirst":      "<<",
					"sPrevious":   "<",
					"sNext":       ">",
					"sLast":       ">>"
				},
				"oAria": {
					"sSortAscending":  ": activer pour trier la colonne par ordre croissant",
					"sSortDescending": ": activer pour trier la colonne par ordre d&eacute;croissant"
				},
				searchPlaceholder: 'Rechercher'
			},
			autoWidth: false,
			scrollY: "350px",
			scrollX: false,
			lengthChange: false,
			scrollCollapse: true,
			pageLength: 25,
			paging: false,
			order: order,
			createdRow: function ( row, data, index ) {
				$(row).find('> td').each(function(i){
					var th = visibleTh[i];
					var format = th.attr('data-format');
					var column = th.attr('data-column');
					var td = $(this);
					if(format){
						var formatter = formats[format];
						if(typeof(formatter)=='object'){
							formatter = formatter.handler;
						}
						if(formatter){
							formatter.call(td,data[column],column,data,row,th);
						}
					}
					var cssClass = th.attr('data-css-class');
					if(cssClass){
						td.addClass(cssClass);
					}
				});
			},
			rowCallback: function(row, data, dataIndex){
				if(checkboxSelector){
					var rowId = data[0];
					if($.inArray(rowId, rows_selected) !== -1){
						$(row).find('input[type="checkbox"]').prop('checked', true);
						$(row).addClass('selected');
					}
				}
			},
			checkboxName:'id', //custom option - select implementation
			

			dom: 'Bfrtip',
			buttons: [
				{
					extend:    'copy',
					text:      '<i class="fa fa-files-o"></i>',
					titleAttr: 'Copy'
				},
				{
					extend:    'excel',
					text:      '<i class="fa fa-file-excel-o"></i>',
					titleAttr: 'Excel'
				},
				{
					extend:    'csv',
					text:      '<i class="fa fa-file-text-o"></i>',
					titleAttr: 'CSV'
				},
				{
					extend:    'pdf',
					text:      '<i class="fa fa-file-pdf-o"></i>',
					titleAttr: 'PDF'
				},
				{
					extend:    'print',
					text:      '<i class="fa fa-print"></i>',
					titleAttr: 'PRINT'
				},
				{
					extend: 'colvis',
					text:      '<i class="fa fa-sort-amount-asc"></i>',
					columns: ':not(:first-child)'
				}
			]


			
		},config);
		
		console.log(config);
		table = $el.DataTable(config);
		
		
		$el.on('click','>tbody>tr input.select_row',function(e){
			e.stopPropagation();
		});
		$el.on('click','>tbody>tr',function(){
			var tr = $(this);
			var check = tr.find('.select_row');
			if(check.length){
				check.prop('checked',!check.prop('checked'));
				check.trigger('change');
			}
		});
		$el.on('dblclick','>tbody>tr',function(){
			var tr = $(this);
			var data = table.row( tr[0] ).data();
			var btn = tr.find('[data-action="edit"]:first');
			var index = btn.closest('td').index();
			var th = $el.find('>thead>tr>th:nth-child('+(index+1)+')');
			if(btn.length){
				actions.edit.handler.call( btn, data, th );
			}
		});
		$el.on('click','[data-action]',function(e){
			e.stopPropagation();
			var btn = $(this);
			var action = btn.attr('data-action');
			var tr = btn.closest('tr');
			var data = table.row( tr[0] ).data();
			var index = btn.closest('td').index();
			var th = $el.find('>thead>tr>th:nth-child('+(index+1)+')');
			actions[action].handler.call( btn, data, th );
		});
		
		if(checkboxSelector){
			var head = $( table.table().header() );
			table.on('draw', function(){
				// Update state of "Select all" control
				updateDataTableSelectAllCtrl(table);
			});
			head.find('input.select_all').on('click', function(e){
				var checkboxs = $el.find('tbody input.select_row');
				checkboxs.prop('checked', this.checked);
				checkboxs.trigger('change');
				
				rows_selected = [];
				if (this.checked) {
					$el.find('tbody tr').addClass('selected');
					var data = table.rows().data();
					var len = data.length;
					var i;
					for (i = 0; i < len; i++) {
						rows_selected.push(data[i][0]);
					}		 
				}
				else {
					$el.find('tbody tr').removeClass('selected');
				}
				// Prevent click event from propagating to parent
				e.stopPropagation();
			});
			
		}

	}
	
});
