/*
	Group: jquery plugins
*/

(function($){
	var opts={
			'exceptmodules': ['adminpeople'],
			'txts': {
				'adminfiles':'Search tags in Files'
			}
	};
	var field; // the search field
	function runSearch()
	{
		$('#viewcontent').load('/'+$('#searchcontext').val()+'/index/search/sydneylayout/no/', {'q':field.val()});
	};
	/**
	 * jquery plugin for initializing the search bow on top of the content
	 * @constructor
	 */
	$.fn.searchForm = function(opt)
	{
		var doit = true;
		for (var i = 0; i < opts.exceptmodules.length; i++) 
		{
			if ($('#searchcontext').val() == opts.exceptmodules[i])
			{
				doit = false;
				break;
			}
		}
		if (doit) {
			field = $(this);
			$(this).keydown(function(e){
				if (e.which == 13) runSearch();
			});
		}
		return this;
	};
})(jQuery);
