if (!ceEditors) var ceEditors = {};
/**
 * Class: ceEditors.files
 * Methods for file editor
 * @constructor
 */
ceEditors.files = {
	/**
	 * Method: save
	 */
	save : function(e){
		ceEditors.defaultedt.save.apply(this);
		var item = $(this);
		var thisp = this[0];
		if (this[0].attributes['dbid']) var dbid = this[0].attributes['dbid'].nodeValue; else var dbid = 0;
		if (this[0].attributes['dborder']) var dborder = this[0].attributes['dborder'].nodeValue; else var dborder=0;

		var editor = $(".editor", item);
		item.data("new", false);
		var params = '';
		var elids = [];
		if( $("#folders-categories").length == 0 ) {
		    $('.itemselected', editor).each(function(){
			    var elid = $(this).attr('href');
			    elids.push( elid );
		    });
		} else {
                    var t = $("#tree").dynatree("getRoot");
                    $.map(t.tree.getSelectedNodes(), function(node) {
                        var id = node.data.key;
                        elids.push(id.substr(10));
                    });
		    params = "array('type' => 'categories')";
		}
                
		var value = elids.toString();
		//var html = value;
		//$(".content", item).html(html);
		item.removeEditor();
		
		$.postJSON('/adminpages/services/savediv/format/json/emodule/'+emodule, {
										'id': dbid,
										'order': dborder,
										'content': value,
										'params': params,
										'pagdivtypesid' : 5,
										'status' : status,
										'pagstructureid' : pagstructureid
									},
			function(data) {
			    ceEditors.defaultedt.saveorder( item, data);
			    $.get("/adminpages/services/getdivwitheditor/", {'dbid': data.ResultSet.dbid}, function(data){
				item.replaceWith(data);
				$("li[dbid="+item.attr('dbid')+"]").makeEditable();
			    });
			}
		);
	},
	/**
	 * Method: setupEditor
	 */
	setupEditor : function(){
		ceEditors.defaultedt.setupEditor.apply(this);
		var item = $(this);
		var editor = $(".editor", item);
		$('li', editor).click(function(e){
			eval( "var parafd = "+$(this).attr('fileparams') );

			if(parafd.filter != 7) {
			    $(this).parents(".editor").load(
				    "/adminfiles/index/index/",
				    {
					    'embed':'yes',
					    'context': 'pageeditor',
					    'filter' : parafd.filter,
					    'mode' : parafd.mode
				    },
				    function(e, a) {
					    $('.buttons .button').click(function(e){
						    e.preventDefault();
						    var act = $(this).attr('href');
							
							if (act == "save") {
								status 	= 'published';
							} else if (act == "save-draft") {
								status 	= 'draft';
								act 	= "save";
							}							
							
						    item[act]();
					    });
				    }
			    );
			} else {
			    // Category
			    $(this).parents(".editor").html('<p class="buttons"><a class="button" href="save">Save as actual content</a><a href="save-draft" class="button">Save as draft</a><a class="button muted" href="cancel">Cancel</a></p><div id="folders-categories"></div>');
			    $("#folders-categories").foldermanager({
				'title': 'Categories',
				'cortable': 'filfolders_filfiles',
				'labeltable': 'filfolders',
				'datatable': 'filfiles',
				'dialog': false,
				'canadditem': false,
                                'autoSaveOnSelect' : false
			        //'onSaveMCallback': ceEditors.files.save
			    });
			    $('.buttons .button').click(function(e){
				    e.preventDefault();
				    var act = $(this).attr('href');
					
					if (act == "save") {
						status 	= 'published';
					} else if (act == "save-draft") {
						status 	= 'draft';
						act 	= "save";
					}
					
				    item[act]();
			    });
			}

		});
	}
};