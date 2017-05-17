window.CKEDITOR_BASEPATH = $('base').attr('href')+'node_modules/ckeditor/';

require( 'ckeditor' );
require( 'ckeditor/adapters/jquery' );
require( 'directive/ckeditor/placeholder_select' );

export default jstack.directive( 'ckeditor', class extends jstack.Component{

	domReady(){
		
		
		let textarea = this.element;
		let options = this.options;

		var id = textarea.requiredId();
		var self = this;
		
		let placeholder_select = [
			{
				'format': '{{%placeholder%}}',
				'label' : 'Creancier',
				'title' : 'test',
				'placeholders':[
					'DATE_JOUR',
					'LOGO',
					'BAS_DE_PAGE',
					'C_RAISON_SOCIALE',
					'C_ID',
					'C_ADRESSE',
					'C_VILLE',
					'C_CODE_POSTAL',
					'C_FORME_JURIDIQUE',
					'C_SIRET',
					'C_NR_TVA',
					'GEST_TITRE',
					'GEST_NOM',
					'GEST_PRENOM',
					'GEST_TELEPHONE',
					'GEST_MOBILE',
					'GEST_TELEPHONE',
					'GEST_EMAIL',
					'GEST_FONCTION',
					'GEST_ADRESSE',
					'GEST_CODE_POSTAL',
					'GEST_VILLE'
				]
			},
			{
				'format': '{{%placeholder%}}',
				'label' : 'Debiteur',
				'title' : 'test',
				'placeholders':[
					'D_ID',
					'D_CIVILITE',
					'D_RAISON_SOCIALE',
					'D_NOM',
					'D_PRENOM',
					'D_BLOC_ADRESSE',
					'D_VILLE',
					'D_CODE_POSTAL',
					'D_FORME_JURIDIQUE',
					'D_SIRET',
					'D_NR_TVA',
					'D_EMAIL',
					'D_TELEPHONE',
					'D_FAX',
					'D_CAPITAL',
					'D_PROCEDURE_COLLECTIVE',
					'D_DATE_PROCEDURE_COLLECTIVE',
					'D_PRIVILEGE_TRESOR_PUBLIC',
					'D_PRIVILEGE_URSSAF',
					'D_NOTE_SCORING',
					'D_LETTRE_SCORING',
					'D_LIMITE_DE_CREDIT'
				]
			},
			{
				'format': '{{%placeholder%}}',
				'label' : 'Factures',
				'title' : 'test',
				'placeholders':[
					'ENCOURS',
					'ENCOURS ECHU',
					'ENCOURS NON ECHU',
					'INTERET DE RETARD',
					'CLAUSE PENALE',
					'PENALITES DE RETARD',
					'IFR',
					'FR',
					'TOTAL FRAIS DE RETARD',
					'TABLEAU',
					'BOUTON_PROMESSE'
				]
			}
		];
		
		let optionsDefault = {
			extraPlugins : 'autogrow,placeholder,richcombo,placeholder_select,pagebreak',
			autoGrow_onStartup : false,
			// autoGrow_minHeight : 200,
			// autoGrow_maxHeight : 200,
			autoGrow_bottomSpace : 50,
			enterMode:CKEDITOR.ENTER_BR,
			placeholder_select: placeholder_select,

		};
		
		
		if(!options.removePlugins){
			options.removePlugins = '';
		}
		
		if(options.customEditor){
			let customEditor = options.customEditor;
			delete options.customEditor;
			
			switch(customEditor){
				case 'text':
					
					optionsDefault.enterMode = CKEDITOR.ENTER_BR;
					optionsDefault.forcePasteAsPlainText = true; 
					
					let placeholder_buttons = [];
					for(let i = 0, l = placeholder_select.length; i<l; i++){
						placeholder_buttons.push('placeholder_select'+i);
					}
					
					options.toolbar = [
						[ 'Cut', 'Copy', 'Paste', '-', 'Undo', 'Redo' ],			// Defines toolbar group without name.
						placeholder_buttons
					];
					options.removePlugins += ',pastefromword';
					
				break;
			}
		}
		
		
		options = $.extend(true, optionsDefault, options);


		//if(CKEDITOR.instances[id]){
			//CKEDITOR.instances[id].destroy();
		//}
		
		if(CKEDITOR.instances[id]){
			CKEDITOR.instances[id].updateElement();
		}
		
		textarea.ckeditor(options);

		textarea.closest('form').on('reset',function(){
			CKEDITOR.instances[id].setData('');
		});
		
		/*
		CKEDITOR.instances[id].on('instanceReady', function(){
			textarea.on('j:val',function(e,value){
				let ckinstance = CKEDITOR.instances[id];
				if(!ckinstance || !document.body.contains(textarea[0])) return;
				ckinstance.setData(value);
			});
		});
		*/
		CKEDITOR.instances[id].on('change', function(e) {
			textarea.trigger('j:update',[ e.editor.getData() ]);
		});
	}
});
