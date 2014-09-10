/*
	Group: jquery plugins
*/

(function($){
	var el;
	var defaults = {
			'title':'undefined title',
			'layout':'layer'
				};
	var opts={};
	var actscls=[];
	/**
	 * Sets up the title shown on the button
	 */
	$.fn.setTitle = function( txt ) {
		$('.mtitle', el).html( txt );
	};
	/**
	 * 
	 */
	function setHtml()
	{
		var mhtml = '<div class="mtitle"></div><div class="formatpopup paragraphpopup sublmenu"><ul>';
		for (var i=0; i < opts.items.length; i++ ) 
		{
			if (opts.items[i].separator == true) var sstyle = 'style="border-bottom: 1px solid rgb(85, 85, 85);"'; else var sstyle = '';
			if (opts.items[i].isTitle == true) var addedClass=' istitle'; else var addedClass='';
			mhtml += '<li><div class="polinlink melei'+i+addedClass+'" '+sstyle+'>'+opts.items[i].label+'</div></li>';
			actscls.push('melei'+i);
		}
		mhtml += '</ul></div>';
		el.html( mhtml );
	};
	/**
	 * 
	 */
	function setHtml2()
	{
		var mhtml = '<label>'+opts.title+'</label><ul class="flatlistmenu">';
		for (var i=0; i < opts.items.length; i++ ) 
		{
			if (opts.items[i].separator == true) var sstyle = 'style="border-left: 1px dotted rgb(85, 85, 85);"'; else var sstyle = '';
			if (opts.items[i].isTitle == true) var addedClass=' istitle'; else var addedClass='';
			mhtml += '<li><div class="polinlinkflat melei'+i+addedClass+'" '+sstyle+'>'+opts.items[i].label+'</div></li>';
			actscls.push('melei'+i);
		}
		mhtml += '</ul>';
		el.html( mhtml );
	};
	/**
	 * Sets up the events on the menu elements
	 */
	function setActions()
	{
		for (var i=0; i < opts.items.length; i++ ) 
		{
			var act = opts.items[i].action;
			if (act != null && act != false && act != undefined) $('.'+actscls[i], el).click(act);
		}
	};
	/**
	 * Initialization
	 */
	function init()
	{
		if (opts.layout == 'layer') 
		{
			setHtml();
			$.fn.setTitle( opts.title );
			// setup main action
			var pos = $('.mtitle', el).offset();
			$('.sublmenu', el).css( {'top': (pos.top-104)+'px', 'left': (pos.left-40)+'px', 'position':'absolute' } );
		}
		if (opts.layout == 'linear') setHtml2();
		el.mouseover(function(){ $('.sublmenu', el).show(); });
		el.mouseout(function(){ $('.sublmenu', el).hide(); });
		setActions();
	};
	/**
	 * jQuery plugin for managing the drop down menu for 'advanced options' 
	 * or other functionalities in a module.
	 * @param {object litteral} The parameters overiding/extending the defaults. 
	 * @constructor
	 */
	$.fn.sydlistmenu = function(options) {
		opts = $.extend({}, $.fn.sydlistmenu.defaults, options);
		return this.each(function() {
			el = $(this);
			init();
		});
	};

})(jQuery);
