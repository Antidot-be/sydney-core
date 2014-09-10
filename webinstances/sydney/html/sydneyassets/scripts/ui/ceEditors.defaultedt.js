if (!ceEditors) var ceEditors = {};
/**
 * Class: ceEditors.defaultedt
 * Default method for inline content edition
 * @constructor
 */
ceEditors.defaultedt = {
	/**
	 * Method: setupEditor
	 */
	setupEditor : function() {
		var item = $(this);
		var editor = $(".editor", item);
	    // Setup Cancel and Save buttons
		$("p.buttons a[href='save']", editor).unbind('click');
		$("p.buttons a[href='save']", editor).click(function(e){
			e.preventDefault();
			status = 'published';
		    item.save();
		});
		$("p.buttons a[href='save-draft']", editor).unbind('click');
		$("p.buttons a[href='save-draft']", editor).click(function(e){
			e.preventDefault();
			status = 'draft';
		    item.save();
		});
		$("p.buttons a[href='cancel']", editor).unbind('click');
		$("p.buttons a[href='cancel']", editor).click(function(e){
			e.preventDefault();
			status = '';
			item.cancel();
		});
	},
	/**
	 * Method: save
	 */
	save : function(){
	    $.log("ceEditors.defaultedt save");
	},
	/**
	 * Method: saveorder
	 */
	saveorder : function(item, data)
	{
		$('#ajaxbox').msgbox( {'message':'saving...'} );
		$(".content", item).parent().attr('dbid', data.ResultSet.dbid);
		$.postJSON('/adminpages/services/updatepagerorder/format/json/emodule/'+emodule, {
				'jsondata': $.toJSON( $('#pageContent > li, .placeholder_zone > li').lidbids() ),
				'pagstructureid' : $('#pageContent').attr('pagstructureid')
		},
		function(data) {
			$('#ajaxbox').msgbox(data.ResultSet);
		});

		var curZone = 0,
			pagid = $('#pageContent').attr('pagstructureid');

		// JTO - 13/02/2014
		// Ajout de cette requete pour modifier la zone d'un pagdiv
		if(item.parents('.zone').length){
			var itemId = item.attr('dbid');
			curZone = item.parents('.zone').attr('class').split(' ')[1];
			$.postJSON(
				'/adminpages/services/updatezoneforpagdiv/format/json',{
					'pagstructureid' : pagid,
					'zone' : curZone,
					'pagdivid' : itemId
				},
				function(data) {
					$('#ajaxbox').msgbox(data.ResultSet);
				}
			);
		}

	}
};
