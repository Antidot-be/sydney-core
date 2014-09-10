/**
 * Generic jquery plugin for organizing and linking m2m tables and their labels.
 * @member $.fn.foldermanager
 */
(function($){
	/**
	 * Generic jquery plugin for organizing and linking m2m tables and their labels.
	 * This can be used for example in relationships between folders and another table or people and a table ...
	 * It is also used for filtering data on tags/folders/categories (whatever you want to call them)
	 *
	 * @param {object} prefs Object containing the config overriding the default ones
	 * @param [prefs.title="Folders"] 					The title within the dialog box.
	 * @param [prefs.ajaxboxid="#ajaxbox"] 				the ID of the box where to display message
	 * @param [prefs.selectedItems="[]"] 				selected items
	 * @param [prefs.cortable="filfolders_filfiles"]	correspondance table with m2m links
	 * @param [prefs.labeltable="filfolders"] 			table containing the labels
	 * @param [prefs.datatable="filfiles"] 				table containing the 'data'
	 * @param [prefs.parentrelation="true"] 			is there a parent/child relationship between labels items?
	 * @param [prefs.sortable="true"] 					are the label sortable?
	 * @param [prefs.canadditem="true"] 				can the user add label items?
	 * @param [prefs.candelete="true"] 					can the user delete an item?
	 * @param [prefs.canrename="true"] 					can the user rename an item?
	 * @param [prefs.selectedid="0"] 					id of the element we selected
	 * @param [prefs.dialog="true"] 					should we display the result in a dialog?
	 * @param [prefs.labelfield="1"] 					the kind of label field to use in the DB
	 * @param [prefs.lbladd=""] 						the label to be added (form the add text field if any)
	 * @param [prefs.onSaveMCallback="null"] 			function to call when clicking save
	 * @param [prefs.idtodel="null"] 					IDs to delete
	 * @param [prefs.autoOpen="true"] 					auto open the dialog box or not
	 * @param [prefs.closeOnEscape="false"] 			close the dialog on clicking on the X
	 * @param [prefs.showcancel="true"] 				show the cancel button which destroy the dialog
	 * @param [prefs.position="false"] 					pass a position for the dialog (or false for default = centered)
	 * @param [prefs.distroydiag="true"] 				should we destroy the dialog after saving
	 * @param [prefs.selectfuncts="true"] 				should we show the select function (checking the checkboxes automatically)
	 * @param [prefs.exendfuncts="true"] 				show the expend and collapse functions
	 * 
	 * @author Arnaud Selvais
	 * @since 16/04/10
	 * @class
	 * @constructor
	 */
	$.fn.foldermanager = function( prefs )
	{
		return $(this).each(function(){
			/**
			 * @memberOf $.fn.foldermanager
			 */
			var opts={};
			/**
			 * @memberOf $.fn.foldermanager
			 * @param {object} optsdefault Default options
			 */
			var optsdefault={
				'title': 'Folders',					// The title within the dialog box
				'ajaxboxid': '#ajaxbox',			// the ID of the box where to display message
				'selectedItems': [],				// selected items
				'cortable': 'filfolders_filfiles',	// correspondance table with m2m links
				'labeltable': 'filfolders',			// table containing the labels
				'datatable': 'filfiles',			// table containing the 'data'
				'sorttable': 'pagorder, label',		// table containing the 'data'
				'parentrelation': true,				// is there a parent/child relationship between labels items?
				'sortable': true,					// are the label sortable?
				'canadditem':true,					// can the user add label items?
				'candelete': true,					// can the user delete an item?
				'canrename': true,					// can the user rename an item?
				'selectedid': 0,					// id of the element we selected
				'dialog': true,						// should we display the result in a dialog?
				'labelfield':1,						// the kind of label field to use in the DB
				'lbladd':'',						// the label to be added (form the add text field if any)
				'onSaveMCallback': null,			// function to call when clicking save
				'idtodel': null,					// IDs to delete
				'autoOpen': true,					// auto open the dialog box or not
				'closeOnEscape': false,				// close the dialog on clicking on the X
				'showcancel': true,					// show the cancel button which destroy the dialog
				'position': false,					// pass a position for the dialog (or false for default = centered)
				'distroydiag': true,				// should we destroy the dialog after saving
				'selectfuncts' : true,				// should we show the select function (checking the checkboxes automatically)
				'exendfuncts': true,				// show the expend and collapse functions
				'autoSaveOnSelect': true,			// send save request to the server when selecting a node
				'selectedItemsIds': [],				// array of selected IDs
				'selectedItemsLabels': [],			// array of selected labels
				'callbackParamObj': false,			// Return the IDs in a simple array if false; or object combining labels and IDs
				'modal': true,
				'module': 'admin',					// module called when ajax required datas
				'dynatreeCheckbox': true,			// display checkbox
				'dynatreeSelectMode': 3,             // Checkbox mode selection
				'showsave' : true,
				'destroyreload': false
			};
			/**
			 * Main div where the whole magic will happen
			 * @private
			 * @memberOf $.fn.foldermanager
			 */
			var maind;			
			var cdnurl;
			var canSaveSelectedNode = false;
			var systemCat 			= [];
			var editTreeNodeItem	= '';
			/**
			 *
			 * @private
			 * @memberOf $.fn.foldermanager
			 */
			function init()
			{
				getsdata( 'getdata', buildGui );
			};
			/**
			 * Exand and collapse all according to the param passed as arg.
			 * @param {boolean} exp Boolean - true = expand | false = collapse
			 * @private
			 * @memberOf $.fn.foldermanager
			 */
			function expcol( exp )
			{
				if (exp) var vs = ['collapsed','expanded'];
				else var vs = ['expanded','collapsed'];
				$('ul', $('.tree', maind) ).each(function(){
					var ul = $(this);
					var li = $(ul.parent("li").get(0));
					var row = $(">.row", li);
					row.children('.bullet').each(function(){
					    $(this).removeClass(vs[0]);
						$(this).addClass(vs[1]);
						var li2 = $(this).parents("li").get(0);
						var ul2 = $(">ul", li2);
						if (exp) ul2.slideDown("fast");
						else ul2.slideUp("fast");
					});
				});
			};
			/**
			 * Sets the events on the select buttons (select all/none, ...)
			 * @private
			 * @member $.fn.foldermanager
			 */
			function setActionsOnSelects()
			{
				$('a[href=selectall]', maind).click(function(e){
					e.preventDefault();
					canSaveSelectedNode = false;
		            $("#tree", maind).dynatree("getRoot").visit(function(node){
		            	node.select(true);
		            });
		            canSaveSelectedNode = true;
		            saveNodeSelected();
	                return false;
				});
				$('a[href=selectnone]', maind).click(function(e){
					e.preventDefault();
					canSaveSelectedNode = false;
		            $("#tree", maind).dynatree("getRoot").visit(function(node){
		            	node.select(false);
		            });
					canSaveSelectedNode = true;
					saveNodeSelected();
	                return false;
				});
				$('a[href=selectinvert]', maind).click(function(e){
					e.preventDefault();
					canSaveSelectedNode = false;
		            $("#tree", maind).dynatree("getRoot").visit(function(node){
			              node.toggleSelect();
			        });
		            canSaveSelectedNode = true;
		            saveNodeSelected();
			        return false;

				});
				
				$('a[href=selectkids]', maind).click(function(e){
					e.preventDefault();					
				});
			};
			/**
			 * Sets the events on the exand/collapse all
			 * @private
			 * @member $.fn.foldermanager
			 */
			function setExandcol()
			{				
				$('a[href=expandall]', maind).click(function(e){
					e.preventDefault();
			        $("#tree", maind).dynatree("getRoot").visit(function(node){
				          node.expand(true);
				    });
				    return false;
				});
				$('a[href=collapseall]', maind).click(function(e){
					e.preventDefault();
			        $("#tree", maind).dynatree("getRoot").visit(function(node){
				          node.expand(false);
				    });
				    return false;
				});
			};
			/**
			 * Builds the GIU.
			 * Add the action buttons if any and the whole tree
			 * @private
			 * @member $.fn.foldermanager
			 */
			function buildGui(e)
			{
				var thtml	= '';
				
				if (opts.canadditem) thtml += '<input type="text" value="" class="addtxt" /> <input type="button" value="Add" class="addbtn" style="font-size:9px;"/><br>';				
				if (opts.selectfuncts && opts.dynatreeCheckbox) thtml += '| <a href="selectall">select all</a> | <a href="selectnone">select none</a> | <a href="selectinvert">invert selection</a> ';				
				if (opts.exendfuncts) thtml += '| <a href="expandall">Expand all</a> | <a href="collapseall">Collapse all</a> | <br>';
				thtml += '<div class="">';
				thtml += recHtmlNodes(0, e);
				thtml += '</div>';
				maind.html(thtml, maind);
				
				$.ui.dynatree.nodedatadefaults["icon"] = false; // Turn off icons by default
				$("#tree",maind).dynatree({
					'debugLevel': 0,
		    		'fx': { height: "toggle", duration: 200 },
	    	        'autoCollapse': false,
	    	        'persist': false,
		    		'checkbox': opts.dynatreeCheckbox,
		    		'keyboard': false,
		    	    'selectMode': opts.dynatreeSelectMode,
		    	    'onExpand': function(isReloading, isError) {
		    			initDeleteAction();
				    	return true;
		    		},
		    	    'onSelect': function(select, node) {
		    			if (canSaveSelectedNode && opts.autoSaveOnSelect) saveNodeSelected();		    			
		    	    },
		    	    'onActivate': function(node, event) {
		    	    	//$('.dynatree-title',node.span).append('<input type="text" value="'+node.data.title+'" style="z-index:999;" />');
		    	    	//$('.dynatree-title',node.span).hide();
		    	    	//node.render();
		    	    	var extract = node.data.title.substr(0,6);
						if (extract != '<input') {
							editTreeNodeItem	= node.data.title;
							node.data.noLink 	= true;
			    	        node.data.title 	= '<input id="editTreeNode" type="text" value="'+node.data.title+'" style="z-index:999;" />';
			    	        node.render();
			    	        $('#editTreeNode').focus();
			    	        $('#editTreeNode').focusout(function (event) {
			    	        	event.preventDefault();
			    	        	saveEditTreeNode(node);
			    	        });			    	        
			    	        $('#editTreeNode').keypress(function (event) {
			    	        	if (event.which == '13') {
			    	        		event.preventDefault();
			    	        		saveEditTreeNode(node);
			    	        	}
			    	        });
						}						
		    	    },
		    	    'onDeactivate': function(node, event) {
		    	    	node.data.title = $('#editTreeNode').val();
		    	    	node.render();
		    	        $('#editTreeNode').remove();
		    	        //saveEditTreeNode(node);
		    	    	//saveEditTreeNode(node);
		    	    	/*
		    	    	node.data.title = $('#editTreeNode').val();
		    	    	node.render();
		    	        $('#editTreeNode').remove();
		    	        */		    	        
		    	    },
		    	    'onDblClick': function(node, event) {
		    	        node.toggleSelect();
		    	    },
		    	    'onKeydown': function(node, event) {
		    	        if( event.which == 32 ) {
		    	          node.toggleSelect();
		    	          return false;
		    	        }
		    	    },
		    		'dnd': {
						onDragStart: function(node) { return true; },
						onDragStop: function(node) {
			    	        var pos	   		= 1;
							var result		= "[";							
							$("#tree",maind).dynatree("getRoot").visit(function(node){
								result += "{'key':'"+node.data.key+"','position': " + pos++ +",'parent':'" + node.parent.data.key + "'},";
							});
							result += "]";
							result = eval(result);							
				            $.postJSON('/'+opts.module+'/servicesfolder/reorderfolder/format/json/', {'datatable':opts.datatable,'fileid': $.toJSON( opts.fileid ),'jsonstr':  $.toJSON (result)  }, function(e,u){
								if(u == 'success') $(opts.ajaxboxid).msgbox(e);								
								else $(opts.ajaxboxid).msgbox({'message':'Error in the AJAX request, try again later...','showtime':5,'modal':true});
							});
						},
						autoExpandMS: 1000,
						preventVoidMoves: true, // Prevent dropping nodes 'before self', etc.
						onDragEnter: function(node, sourceNode) { return true; },
						onDragOver: function(node, sourceNode, hitMode) {},
						onDrop: function(node, sourceNode, hitMode, ui, draggable) { sourceNode.move(node, hitMode); },
						onDragLeave: function(node, sourceNode) {}
					}		
		    	});
		    	// pre-check nodes
				for (var u=0; u < opts.selectedItems.length; u++) {
					if (typeof(opts.selectedItems[u].val) != "undefined" && opts.selectedItems[u].val != "null") {
						$("#tree",maind).dynatree("getTree").getNodeByKey("structure_" + opts.selectedItems[u].val).select();
					} else {
					    $("#tree",maind).dynatree("getTree").getNodeByKey("structure_" + opts.selectedItems[u]).select();
					}
				}
				canSaveSelectedNode = true;
				
		    	$('.addbtn', maind).click(addElement);
		    	if (opts.selectfuncts) setActionsOnSelects();
		    	if (opts.exendfuncts) setExandcol();
		    	initDeleteAction();


				/*
				// add events
				$('.treeelemid-0', maind).treeSetup();
				$('.treeelemid-0', maind).structureEditor();
				$('.addbtn', maind).click(addElement);
				if(opts.candelete) $('.deletenodea', maind).unbind().click(delElement);
				if (opts.selectfuncts) setActionsOnSelects();
				if (opts.exendfuncts) setExandcol();
				*/

		    	
			}; // END - buildGui
			
			function saveEditTreeNode(node) {
				node.deactivate();
				//node.data.title = $('#editTreeNode').val();
    	    	//node.render();
    	    	//$('#editTreeNode').remove();
				
				if (editTreeNodeItem != node.data.title) {
		            $.postJSON('/'+opts.module+'/servicesfolder/editlabel/format/json/', {'id': node.data.key,'label': node.data.title, 'table':opts.labeltable }, function(e,u){
						if(u == 'success') {
							$(opts.ajaxboxid).msgbox(e);
						} else {
							$(opts.ajaxboxid).msgbox({'message':'Error in the AJAX request, try again later...','showtime':5,'modal':true});
						}
					});
				}
			}
			
			function initDeleteAction() {
		    	if(opts.candelete) {			
					$(".dynatree-node",maind).mouseover(function(){
						var node = $.ui.dynatree.getNode(this);
					    $("#tree span.actions").remove();
					    if(typeof node !== 'undefined' && !node.hasChildren() && $.inArray(node.data.key.substr(10),systemCat) == -1 ) {
					    	$(this).after('<span class="actions" style="position:absolute;"><a href="'+ node.data.key.substr(10) +'" class="button warning deletenodea" style="color:white !important;">Delete</a></span>');
							
					    	var position = $(this).position();
							$('#tree span.actions').css('top',(position.top)+'px');
							$('#tree span.actions').css('left',(position.left + $(this).width() - 70)+'px');
							//$('#tree span.actions').css('right','50px');
							$('#tree span.actions').fadeIn();
							
					    	$('.deletenodea').unbind().click(delElement);
						}
					});										
		    	}				
			}

			function saveNodeSelected() {
			    if (!opts.autoSaveOnSelect) {
			        return false;
			    }
			    
				var filfoldersVal = "[";
				var rootNode = $("#tree",maind).dynatree("getRoot");
    	        // Get a list of all selected nodes, and convert to a key array:
    	        var selKeys = $.map(rootNode.tree.getSelectedNodes(), function(node){
    	        	var id 	= node.data.key;
    	        	id 		= id.substr(10);
    	        	filfoldersVal += '{"label": "' + node.data.title + '", "val": "' + id + '"},';
    	        	return node.data.key;
    	        });
    	        filfoldersVal += "]";
    	        filfoldersVal = eval(filfoldersVal);
    	        
	            $.postJSON('/'+opts.module+'/servicesfolder/link-' + opts.datatable + '-to-folder/format/json/', {'fileid': $.toJSON( opts.fileid ),'jsonstr': $.toJSON( selKeys ) }, function(e,u){
					if(u == 'success') {
						$(opts.ajaxboxid).msgbox(e);
						//[{"label": "Press releases", "val": "771"}, {"label": "Press releases 2009", "val": "781"}, {"label": "Publications 2008", "val": "851"}, {"label": "adminnews", "val": "3027"}]
			            if(opts.onSaveMCallback != null) {
			            	opts.onSaveMCallback( filfoldersVal );
			            }
					} else {
						$(opts.ajaxboxid).msgbox({'message':'Error in the AJAX request, try again later...','showtime':5,'modal':true});
					}
				});
			}
			
			/**
			 * Add an element/tag in the tree (and on the server side too)
			 * @private
			 * @member $.fn.foldermanager
			 */
			function addElement(e)
			{
				var elv=$('.addtxt', maind).val();
				opts.lbladd = elv;
				if(elv == '') alert('The label can not be empty.');
				else getsdata('addelement', init);
			};
			/**
			 * Delete an element/tag from the tree (same thing on the server side)
			 * @private
			 * @member $.fn.foldermanager
			 */
			function delElement(e)
			{
				e.preventDefault();
				if (confirm('Are you sure you want to delete this item?')) {
					opts.idtodel = $(e.target).attr('href');
					getsdata('delelement', init);
				}
			};
			/**
			 * Creates the HTML for the treelist
			 * @private
			 * @member $.fn.foldermanager
			 * @return {string} The HTML string
			 */
			function _recHtmlNodes(parent_id, e, firstCall)
			{
				
				var thtml='';
				var nel=0;
				
				
				if (firstCall) {
					thtml += '<ul id="treeData">';
				} else {
					thtml += '<ul>';
				}
				
				for(var i=0; i < e.ResultSet.length; i++)
				{
					var ele = e.ResultSet[i];
					var mparent_id = ele.parent_id;
					if (mparent_id == null) mparent_id = 0;
					if (parent_id == mparent_id) {
						thtml += '<li dborder="0" dbid="'+ele.labeltable_id+'" id="structure_'+ele.labeltable_id+'">';
						//thtml += '<img border="0" src="http://free85sa.antidot.dev/sydneyassets/images/simpletree/spacer.gif" class="trigger" style="float: left;">';
						//thtml += '<span>';
						/*
						var che='';
						for (var u=0; u < opts.selectedItems.length; u++) {
							if (parseInt( opts.selectedItems[u].val ) == parseInt(ele.labeltable_id)) {
								che=' checked';
								break;
							}
						}
						thtml += '<input type="checkbox" value="'+ele.labeltable_id+'"'+che+'>'+ele.label+'</span>';
						*/
						thtml += ele.label + ' ';
						//thtml += '</span>';
						
						
						thtml += '';
						thtml += _recHtmlNodes(ele.labeltable_id, e, false);
						if (ele.isSystemFolder != undefined && ele.isSystemFolder != 0) {
							systemCat.push(ele.labeltable_id);
							//thtml += '<span class="actions button warning deletenodea" style="position:absolute;top:0;display:block;">Delete</a></span>';
						}						
						thtml += '</li>';
						
						nel++;
					}
				}
				thtml += '</ul>';
				if (nel == 0) thtml='';
				return thtml;
			};
			/**
			 * 
			 */
			function recHtmlNodes(parent_id, e)
			{
				var thtml	= '';
				systemCat 	= new Array;
				
				thtml += '<div id="tree">';
				thtml += _recHtmlNodes(parent_id, e, true);
				thtml += '</div>';
					
				return thtml;
			};
			/**
			 * launched when saving the data.
			 * Collects the checked data in an array and pass it to a callback function defined in the config.
			 * @private
			 * @member $.fn.foldermanager
			 */
			function onSave(e) {
				if (!opts.autoSaveOnSelect && opts.onSaveMCallback != null) {
					var selit = [];
					opts.selectedItemsIds=[];
					opts.selectedItemsLabels=[];
					var sd=$("#tree",maind).dynatree("getTree").serializeArray();
					for(var i=0; i < sd.length; i++) 
					{
						var el = sd[i].value.split("_");
						opts.selectedItemsIds.push(parseInt(el[1]));
						selit.push({'label':parseInt(el[1]), 'val': parseInt(el[1]) });
					}
					if (!opts.callbackParamObj) selit = opts.selectedItemsIds;
					if (opts.onSaveMCallback != null) opts.onSaveMCallback(selit);
				}
				if (opts.distroydiag) $(this).html('').dialog('destroy');
			};
			/**
			 * Sends a JSON request to the server (example for getting the tree list)
			 * @private
			 * @memberOf $.fn.foldermanager
			 */
			function getsdata( action, callback )
			{
				$(opts.ajaxboxid).msgbox( {'message': 'Loading...', 'showtime':0,'modal':true} );
				var optsa = {};	
				$.extend(optsa, opts, {'onSaveMCallback':null});
				$.postJSON('/'+opts.module+'/servicesfolder/'+action+'/format/json/', {'jsonstr': $.toJSON( optsa ) }, function(e,u){
					if(u == 'success') {
						$(opts.ajaxboxid).msgbox(e);
						cdnurl = e.cdnurl;
						callback(e);
					} else $(opts.ajaxboxid).msgbox({'message':'Error in the AJAX request, try again later...','showtime':5,'modal':true});
				});
				
			};
			// ---------------------------------------------------- //
			$.extend(opts, optsdefault, prefs);
			var dopts = {
			'title': opts.title,
			'height': 600,
			'width': 800,
			'autoOpen': opts.autoOpen,
			'modal': opts.modal,
			'closeOnEscape': opts.closeOnEscape,
			'buttons' : {}
			};
			if (opts.showsave) dopts.buttons.Ok = onSave;
			if (opts.showcancel) dopts.buttons.Cancel = function(){ $(this).html('').dialog('destroy'); };
			if (opts.position) dopts.position = opts.position;
			
			// destroy on close
			$(this).bind( "dialogclose", function(event, ui) {
				if (opts.distroydiag) $(this).html('').dialog('destroy');
				if (opts.destroyreload) location.reload();
			});

			if (opts.dialog) maind = $(this).dialog(dopts);
			else maind = $(this);
			maind.addClass('gentreeli');
			init();
		});
	};

})(jQuery);
