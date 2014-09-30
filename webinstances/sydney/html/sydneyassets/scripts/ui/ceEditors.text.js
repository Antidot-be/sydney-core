if (!ceEditors) var ceEditors = {};

/**
 * Class: ceEditors.text
 * Methods for wysiwyg text editor
 * @constructor
 */
ceEditors['text-block'] = {
	/**
	 * Method: setupEditor
	 */
	setupEditor : function(){
	    /* ceEditor TEXT */
        ceEditors.ckeditor.load(this, 'Basic');
	},
	/**
	 * Method: save
	 * Save the content of the text (wysiwyg) element inline and in the DB.
	 */
	save : function(){
		ceEditors.defaultedt.save.apply(this);
		var editor = $(".editor", item);
		if (this[0].attributes['dbid']) var dbid = this[0].attributes['dbid'].nodeValue; else var dbid = 0;
		if (this[0].attributes['dborder']) var dborder = this[0].attributes['dborder'].nodeValue; else var dborder=0;
		var item = $(this);
		
		var ckeditor = $(".texteditor", item).ckeditorGet();
		var myvalue = ckeditor.getData();
		
		// view draft
		if (status == "draft") {
			item.addClass('draft');
		} else {
			item.removeClass('draft');
		}		
		
		$(".content", item).html( myvalue );
		$.postJSON('/adminpages/services/savediv/format/json/emodule/'+emodule, {
										'id': dbid,
										'order': dborder,
										'content': myvalue,
										'params': '',
                                        'content_type_label': $(this).data('content-type'),
										'status' : status,
										'pagstructureid' : pagstructureid
									},
			function(data) {
    			$('#ajaxbox').msgbox(data.ResultSet);
				ceEditors.defaultedt.saveorder(item, data);
			    // update the div content
			    $.get("/adminpages/services/getdivwitheditor/", {'dbid': data.ResultSet.dbid}, function(data){
			    	item.replaceWith(data);
			    	$("li[dbid="+item.attr('dbid')+"]").makeEditable();
			    });				
			}
		);
		item.removeEditor();
	}
};