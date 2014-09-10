if (!ceEditors) var ceEditors = {};
/**
 * Class: ceEditors.breadcrumb
 * Methods for breadcrumb content edition
 * @constructor
 */
ceEditors.breadcrumb = {
	/**
	 * Method: setupEditor
	 */
	setupEditor : function(){
		ceEditors.defaultedt.setupEditor.apply(this);
		var item = $(this);
		var editor = $(".editor", item);
		var value = $.trim($(".content", item).text());
	},
	/**
	 * Method: save
	 */
	save : function(){
		ceEditors.defaultedt.save.apply(this);
		var item = $(this);
		var thisp = this[0];
		if (this[0].attributes['dbid']) var dbid = this[0].attributes['dbid'].nodeValue; else var dbid = 0;
		if (this[0].attributes['dborder']) var dborder = this[0].attributes['dborder'].nodeValue; else var dborder=0;
		var editor = $(".editor", item);
		item.data("new", false);
		item.removeEditor();
		// view draft
		if (status == "draft") item.addClass('draft'); else item.removeClass('draft');
		// post the data to the JSON service
		$.postJSON('/adminpages/services/savediv/format/json/emodule/'+emodule, {
										'id': dbid,
										'order': dborder,
										'content': '',
										'params': '',
										'pagdivtypesid' : 24,
										'status' : status,
										'pagstructureid' : pagstructureid
									},
			function(data) {
				ceEditors.defaultedt.saveorder( item, data);
			    // update the div content
			    $.get("/adminpages/services/getdivwitheditor/", {'dbid': data.ResultSet.dbid}, function(data){
			    	item.replaceWith(data);
			    	$("li[dbid="+item.attr('dbid')+"]").makeEditable();
			    });				
			}
		);
	}
};
