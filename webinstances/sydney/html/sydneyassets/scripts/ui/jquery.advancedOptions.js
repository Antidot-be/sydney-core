/**
 * 
 * @member $.fn.advancedOptions
 */
(function($){
	/**
	 * jQuery plugin for managing the advanced options link and pannel
	 * @author: Arnaud Selvais
	 * @constructor
	 */
	$.fn.advancedOptions = function()
	{
		return this.each(function() {
			$('.advancedOptionsToggle a').toggle(
				function(e){ show(e,$(this)); },
				function(e){ hide(e,$(this)); }
			);
		} );
	};
	/**
	 * Method: show
	 * @param e - {Object}
	 * @param link - {Object}
	 */
	function show(e, link){
		e.preventDefault();
		$("span.label", link).html('Hide avanced options');
		var imgsrc = link.parent(".advancedOptionsToggle").find("img.up").get(0).src;
		$("span.button img", link).get(0).src = imgsrc;
		var ao = link.parent(".advancedOptionsToggle").nextAll(".advancedOptions").eq(0);
		ao.slideDown("fast");
	};
	/**
	 * Method: hide
	 * @param e - {Object}
	 * @param link - {Object}
	 */
	function hide(e, link){
		e.preventDefault();
		$("span.label", link).html('Show avanced options');
		var imgsrc = link.parent(".advancedOptionsToggle").find("img.down").get(0).src;
		$("span.button img", link).get(0).src = imgsrc;
		var ao = link.parent(".advancedOptionsToggle").nextAll(".advancedOptions").eq(0);
		ao.slideUp("fast");
	};
})(jQuery);
