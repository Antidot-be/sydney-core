if (!ceEditors) var ceEditors = {};

/**
 * Class: ceEditors.shopfiles
 * @constructor
 */
ceEditors.ckeditor = {
	/**
	 * Method: setupEditor
	 */
	load : function(thiselement, toolbar, fctInitContent){
        /* ceEditor TEXT */
        ceEditors.defaultedt.setupEditor.apply(thiselement);
        var item            = $(thiselement);
        var newCssStyles    = addStyleCss ? addStyleCss : null;
        var toolbarFont     = addStyleCss ? ['Styles','Format','FontSize'] : ['Format','FontSize'];
        
        var fctInitContent = typeof fctInitContent == 'undefined' ? instanceReadyInitContent : fctInitContent;

		CKEDITOR.on( 'dialogDefinition', function( ev ) {
			var dialogName = ev.data.name;
			var dialogDefinition = ev.data.definition;
			var editor = ev.editor;

			if ( dialogName == 'image' ) {
				/*
				 * Lorsqu'on insère l'image, on checke la valeur du champs
				 * width qu'on remplace dans l'url de l'image afin de générer
				 * une image de la taille désirée
				 */
				 var infoTab         = dialogDefinition.getContents( 'info' );
				 var urlField        = infoTab.get( 'txtUrl' );
				 var urlButton       = infoTab.get( 'browse' );

				 // when click ok, the the url must be validated
				 urlField.validate = function () {
					 var url     = this.getValue();
					 var width   = this.getDialog().getValueOf('info', 'txtWidth');

					 if (width != '' && width > 0) {
						 var new_url = url.replace(new RegExp("/dw/[0-9]{1,}/id/", "g"),"/dw/"+width+"/id/");
						 this.setValue(new_url);
						 if (new_url.length > 0) {
							return true;
						 }
						 return false;
					 }
				 };

			} else if ( dialogName == 'link' ) {
				 var infoTab         = dialogDefinition.getContents( 'info' );
				 var urlField        = infoTab.get( 'url' );

				 urlField.onChange = function () {
					 var url     = this.getValue();
					 var new_url = url.replace( /%23/g, '#' );
					 if (new_url != url) {
						this.setValue(new_url);
					 }
					 return true;
				 };

			};
		});

		/**
		 * Configuration du correcteur orthographique (scayt - http://www.webspellchecker.net/samples/scayt-ckeditor-plugin.html)
		 */
		// evaluate SCAYT on startup
		CKEDITOR.config.scayt_autoStartup = true;
		// set up SCAYT default language
		CKEDITOR.config.scayt_sLang = (typeof applicationLanguage == 'undefined')? 'en' : applicationLanguage || 'en';
        // prevent editor to mess with content
        CKEDITOR.config.allowedContent = true;

		$(".texteditor", item).ckeditor( function() {

		},{
			/*skin : 'antidot',*/
			language : (typeof applicationLanguage == 'undefined')? 'en' : applicationLanguage || 'en',// >> com.antidot.sydney/layout/layout-sydney.phtml
			/*uiColor : '#CFCFCF',*/
			contentsCss : publicCss,
			bodyClass : 'content',
			//filebrowserBrowseUrl : '/adminpages/index/select/context/ckeditor-structure/?embed=yes&context=ckeditor&filter=0',
			filebrowserImageBrowseUrl : '/adminfiles/index/index/?embed=yes&context=ckeditor&filter=1',
			filebrowserFlashBrowseUrl : '/adminfiles/index/index/?embed=yes&context=ckeditor&filter=6',
			filebrowserWindowWidth : '950',
			filebrowserWindowHeight : '780',
			disableObjectResizing : 'false',
			emailProtection : 'encode',
			enterMode : CKEDITOR.ENTER_P,/*ENTER_BR ou ENTER_P */
			entities : false,
			stylesSet : addStyleCss,
			resize_dir : 'vertical',
			resize_enabled : false,
			toolbar : (typeof toolbar == 'undefined')?'Basic':toolbar,
			toolbar_Full :
				[
					['Source','-','Save','NewPage','Preview','-','Templates'],
					['Cut','Copy','Paste','PasteText','PasteFromWord','-','Print', 'SpellChecker', 'Scayt'],
					['Undo','Redo','-','Find','Replace','-','SelectAll'],
					['Form', 'Checkbox', 'Radio', 'TextField', 'Textarea', 'Select', 'Button', 'ImageButton', 'HiddenField'],
					'/',
					['Bold','Italic','Underline','Strike','-','Subscript','Superscript','-','RemoveFormat'],
					['NumberedList','BulletedList','-','Outdent','Indent','Blockquote','CreateDiv'],
					['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
					['Link','Unlink','Anchor'],
					['Image','Flash','Table','HorizontalRule','Smiley','SpecialChar','PageBreak'],
					'/',
					['Styles','Format','Font','FontSize'],
					['TextColor','BGColor'],
					['Maximize', 'ShowBlocks','-','About','MyButton'],
					[ 'pdfstatbox' ]
				],
			toolbar_Basic :
				[
					['Source','Templates'],
					['Cut','Copy','Paste','PasteText','PasteFromWord','-','Print'],
					['Undo','Redo','-','Find','Replace','-','SelectAll','Scayt'],
					['antidotBrowseImage','Image','Flash','antidotBrowseFile','antidotBrowsePage','Link','Unlink','Anchor'],
					'/',
					['Bold','Italic','Underline','Strike','-','Subscript','Superscript','-','RemoveFormat'],
					['NumberedList','BulletedList','-','Outdent','Indent','Blockquote'],
					['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
					'/',
					['Table','HorizontalRule','SpecialChar','PageBreak'],
					toolbarFont,
					['TextColor','BGColor'],
					['Maximize', 'ShowBlocks','-','About'],
					[ 'pdfstatbox' ]
				],
			toolbar_Light :
				[
					['Bold','Italic','Underline'],['Table'],
					['antidotBrowseImage','Image','Flash','antidotBrowseFile','antidotBrowsePage','Link','Unlink','Anchor'],
					['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock']
				],

			on : {
				instanceReady : fctInitContent,
				pluginsLoaded : function(ev) {

					// If our custom dialog has not been registered, do that now.
					if ( !CKEDITOR.dialog.exists( 'antidotBrowseImage' ) )
					{
						// Finally, register the dialog.
						CKEDITOR.dialog.add( 'antidotBrowseImage', '/admin/jscripts/ckeditor/vfname/ckeditor_api_dialog_browser.js' );
					}

					// Register the command used to open the dialog.
					this.addCommand( 'antidotBrowseImageCmd', new CKEDITOR.dialogCommand( 'antidotBrowseImage' ) );

					// Add the a custom toolbar buttons, which fires the above
					// command..
					this.ui.addButton( 'antidotBrowseImage',
						{
							label : 'Insert Image',
							command : 'antidotBrowseImageCmd',
							icon: 'skins/antidot/icons_antidot_pictures.png'
						} );

					// If our custom dialog has not been registered, do that now.
					if ( !CKEDITOR.dialog.exists( 'antidotBrowseFile' ) )
					{
						// Finally, register the dialog.
						CKEDITOR.dialog.add( 'antidotBrowseFile', '/admin/jscripts/ckeditor/vfname/ckeditor_api_dialog_browser.js' );
					}

					// Register the command used to open the dialog.
					this.addCommand( 'antidotBrowseFileCmd', new CKEDITOR.dialogCommand( 'antidotBrowseFile' ) );

					// Add the a custom toolbar buttons, which fires the above
					// command..
					this.ui.addButton( 'antidotBrowseFile',
						{
							label : 'Insert File Link',
							command : 'antidotBrowseFileCmd',
							icon: 'skins/antidot/icons_antidot_link_files.png'
						} );

					// If our custom dialog has not been registered, do that now.
					if ( !CKEDITOR.dialog.exists( 'antidotBrowsePage' ) )
					{
						// Finally, register the dialog.
						CKEDITOR.dialog.add( 'antidotBrowsePage', '/admin/jscripts/ckeditor/vfname/ckeditor_api_dialog_browser.js' );
					}

					// Register the command used to open the dialog.
					this.addCommand( 'antidotBrowsePageCmd', new CKEDITOR.dialogCommand( 'antidotBrowsePage' ) );

					// Add the a custom toolbar buttons, which fires the above
					// command..
					this.ui.addButton( 'antidotBrowsePage',
						{
							label : 'Insert Page Link',
							command : 'antidotBrowsePageCmd',
							icon: 'skins/antidot/icons_antidot_link_pages.png'
						} );
					if (typeof current_instance != 'undefined' && 183 == current_instance) {
						this.addCommand('insertPdfstatbox', new CKEDITOR.command(this,
							{
								exec: function(editor) {

									var divhtml = '<div class="statBoxMonth clearer">'
										+ '<h5>Month goes here</h5>'
										+ '<p>A short description goes here</p>'
										+ '<a href="#" class="downloadPdfCTA">Download PDF</a></div>';

									editor.insertHtml(divhtml);
								}
							}
						));
						this.ui.addButton('pdfstatbox',
							{
								label: 'Insert Montlhy PDF stat box',
								command: 'insertPdfstatbox',
								icon: 'plugins/pdfstatbox/images/pdfstatbox.png'
							});
					}
				}
			}

		});

		function instanceReadyInitContent(ev) {
            var editor = $(".texteditor", item).ckeditorGet();
            editor.setData($('.content', item).html());            
        }        
        /* END cdEditor TEXT */
		
	}
};