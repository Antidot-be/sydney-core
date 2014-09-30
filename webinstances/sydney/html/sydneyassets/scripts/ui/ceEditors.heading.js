if (!ceEditors) var ceEditors = {};
/**
 * Class: ceEditors.heading
 * Methods for heading content edition
 * @constructor
 */
ceEditors['heading-block'] = {
	/**
	 * Method: setupEditor
	 */
	setupEditor : function(){
		ceEditors.defaultedt.setupEditor.apply(this);
		var item = $(this);
		var editor = $(".editor", item);
		// Get value
		var value = $.trim($(".content", item).text());
		$("input.value", editor).val(value);
		// Get level
        var level = 1;
        if (typeof item.attr("type") != 'undefined') var level = item.attr("type").substring(1);
		if(level){
			var radio = $("input[name=level][value=" + level + "]", editor);
			radio.get(0).checked = true;
		}else{
			var radio = $("input[name=level][value=1]", editor);
			radio.get(0).checked = true;
			item.attr("type", "h1");
		}
		// Setup events
		$("input[name=level]", editor).click(function(e){
		    var item = $(this).parents("li");
			item.attr("type", "h" + $(this).val());
		});
		// Give focus
		$("input.value", editor).focus();
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
		var value = $.trim($("input.value", editor).val());
	 	if(!value || value == ""){
			//item.delete();
			return false;
		}
		var html = value;
		var level = $("input[name=level]:checked", editor).val();
		if(level <= 0){
			alert("Please choose a level (heading, sub heading or minor heading) for your heading.");
			return false;
		}
		var html = "<h" + level + ">" + value + "</h" + level + ">";
		$(".content", item).html(html);
		item.removeEditor();
		
		// view draft
		if (status == "draft") {
			item.addClass('draft');
		} else {
			item.removeClass('draft');
		}
		
		// post the data to the JSON service
		$.postJSON('/adminpages/services/savediv/format/json/emodule/'+emodule,
            {
                'id': dbid,
                'order': dborder,
                'content': value,
                'params': 'array( \'level\' => '+level+')',
                'content_type_label': $(this).data('content-type'),
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
