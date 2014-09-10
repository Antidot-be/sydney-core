/**
 * Group: jquery plugins
 * @fileoverview
 */

(
/** @param {jQuery} $ jQuery Object */
function($){
	/**
	 * default params
	 * @private
	 */
	var defaults = {
		'bgcolor' : '#1455AA', 	// background color
		'showtime': 2,			// timeout time for the message to be shown
		'modal': true,			// is this message modal or not (usefull for ajax request pending)
		'modaldivid' : '#modalBackground'
	};
	/**
	 * options which is a combinason of default params and data passed to the constructor
	 * @private
	 */
	var opts={};
	
	var zindex = 999;
	var xindex = 10;

	/**
	 * Init the params and launch the process
	 * This plugin manages the apearition of a message in a box.
	 * The message will show up and disapear, it is just for info after
	 * an ajax request.
	 * 
	 * @constructor
	 */
	$.fn.msgbox = function(options) {

		opts = $.extend({}, defaults, options);
		
		if (opts.showtime == 0 || $('#ajaxbox').is(':visible')) {
			var el = $(this);
			el.css('top','0px');
		} else {
			
			var nbrElements 		= $('.ajaxbox-stack').length;
			var topOfFirstElements 	= getPosition('first');// first element is the last created but the first on the list
			var topOfLastElements 	= getPosition('last');// last element is the first created but the last on the list
			
			var myPosition = 0;
			myPosition = topOfFirstElements;
						
			if (topOfFirstElements > 10*30) {
				if (topOfLastElements > 60) {
					myPosition 	= 0;					
				}
			}
			
			if (myPosition == 0) {
				xindex		= 10;
			}
			
			var el = $(this).clone();
			el.attr('id',Math.random());
			el.attr('class','ajaxbox-stack');
			el.css('top',( ( myPosition + 30 ) + 'px'));
			el.css('left',( ( xindex += 5 ) + 'px'));
			
			el.css('zIndex',zindex++);
			el.css('MozBoxShadow','1px 1px 12px #555');
			
			$(this).after(el);
		}
		return load(el,options.message);

	};
	
	function getPosition(selector) {

		var topOfElements 	= $('.ajaxbox-stack:'+selector).css('top');
		if (topOfElements == undefined) {
			topOfElements = 0;
		} else {				
			topOfElements 		= parseInt(topOfElements.substr(0,topOfElements.lastIndexOf('px')));
		}		
		return topOfElements;
	}
	
	/**
	 * @private
	 */
	function load(el,message) {
		if (opts.modal == false) $(opts.modaldivid).hide();
		init(el,message);
		return el;
	}
	
	/**
	 * 
	 * @private
	 */
	function init(el,message)
	{
		if (opts.modal) $(opts.modaldivid).show();
		el.hide();
		// set message
		el.html(message);
		// set background color according to the message type
		if(opts.status == 0) el.css({ 'backgroundColor': '#DD2222' });
			else el.css({ 'backgroundColor': opts.bgcolor });
		// slide down and hide it after a while
		el.slideDown( 200, $.fn.msgbox.hideIt(el) );
	};
	/**
	 * Private method for hiding the message
	 * @private
	 */
	function _hideIt(el)
	{
		if (el.attr('id') == "ajaxbox") {
			el.hide();
		} else {
			el.remove();
		}
		if (opts.modal) {
			var nbrElements 		= $('.ajaxbox-stack').length;
			if (nbrElements <= 1) {
				$(opts.modaldivid).hide();
			}
		}
	};
	/**
	 * hide the message after a set timeout
	 * @public
	 */
	$.fn.msgbox.hideIt = function(el) {
		if (opts.showtime > 0 ) {
			setTimeout( function () {_hideIt (el);}, (opts.showtime*1000) );
		}
	};
})(jQuery);
