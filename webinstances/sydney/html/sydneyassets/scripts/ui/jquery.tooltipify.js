
(function($){
	/**
	 * Adds a tooltip jqueryUI to elements.
	 * Actually replaces elements by a tooltip on their parents
	 * @constructor
	 */
	$.fn.tooltipify = function()
	{
		return $(this).each(function(){
			var pnode = $(this).parent();
			var txt = $(this).html();
			$(this).remove();
			if (txt != '' && txt.length > 5) {
                pnode.attr('data-tooltip', txt);
			}
            $(document).tooltip({
                items: "[data-tooltip]",
                position: { my: "right bottom", at: "right top", collision: "flipfit" },
                content: function(){
                    return $(this).attr('data-tooltip');
                }
            });
		});
	};

})(jQuery);
