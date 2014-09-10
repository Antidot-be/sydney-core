/*
	Group: jquery plugins
*/

(function($){
	/**
	 * Container for the jQuery object which is the element when the help is displayed
	 * @memberOf $.fn.helpBox
	 */
	var helpDiv;
	/**
	 * List of IDs of elements which are toggled button for showing/hiding the help
	 * @memberOf $.fn.helpBox
	 */
	var buttonsIds = "#helpMenu,#helpboxhide";
	/**
	 * class of the div which will contain the help content
	 * @memberOf $.fn.helpBox
	 */
	var helpCntCss = '.helpContentIn';
	/**
	 * Defines if the help has already taken the content with it's ajax request
	 * @memberOf $.fn.helpBox
	 */
	var hasContent = false;
	/**
	 * jQuery plugin for managing the contextual help menu.
	 * Initializing the help box container and buttons.
	 * @constructor
	 */
	$.fn.helpBox = function()
	{
		helpDiv = $(this);
		$(buttonsIds).click(function(event){ onClickBtn(event); });
		return $(this);
	};
	/**
	 * Launched when a show/hide button is clicked
	 * @memberOf $.fn.helpBox
	 */
	function onClickBtn(event)
	{
		event.preventDefault();
		helpBoxToggle();
		if (!hasContent)
		{
			var ldiv = $(helpCntCss, helpDiv);
			ldiv.html("Loading ...");
			ldiv.load(helpDiv.attr('helpUrl'));
			hasContent = true;
		}
	};
	/**
	 * Show / hide the help content
	 * @memberOf $.fn.helpBox
	 */	
	function helpBoxToggle() {
		helpDiv.slideToggle("fast");
	};
})(jQuery);
