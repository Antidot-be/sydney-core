if (!ceEditors) var ceEditors = {};
/**
 * Class: ceEditors.snippet
 * Methods for snippets editing
 * @constructor
 */
ceEditors.snippet = {
	/**
	 * Method: setupEditor
	 */
	setupEditor : function(){
		ceEditors.defaultedt.setupEditor.apply(this);
		var item = $(this);
		var editor = $(".editor", item);
		var rows = $(".ui-table tbody tr", editor).click(function(e){
			e.preventDefault();
		    item.save();
		});
	},
	/**
	 * Method: save
	 */
	save : function(){
		ceEditors.defaultedt.save.apply(this);
		var item = $(this);
		var editor = $(".editor", item);
		item.data("new", false);
		// Add content for demo
		$(".content", item).html('<h1>Contactez-nous</h1><p>Ecrivez-nous à <strong><a href="mailto:">infos@sandwichesdusablon.com</a></strong> ou appelez-nous au <strong>02 123 45 67</strong>.</p>');
		item.removeEditor();
		
		// view draft
		if (status == "draft") {
			item.addClass('draft');
		} else {
			item.removeClass('draft');
		}		
	}
};

