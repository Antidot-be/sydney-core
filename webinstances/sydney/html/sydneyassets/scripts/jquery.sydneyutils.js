/**
 * Namespace: jquery.sydneyutils
 * Extensions and functions for jQuery used by Sydney.
 */
if (!ANTIDOT) var ANTIDOT = {};
/*
 * Class: ANTIDOT.Registry
 * This is used as a global registry to store all our global objects and vars
 * within the sydney context.
 */
if (!ANTIDOT.Registry) ANTIDOT.Registry = {};



(function($){
	/**
	 * Property: debugmode
	 * Are we in debug mode or not. So should we show the messages to the console?
	 */
	var debugmode = true;
	/**
	 * Function: adminInit
	 * Private method which will initialize all the elements we need in the admin GUI.
	 * This function will call the followings (if possible):
	 * - <jquery.smartEditor> (if element ".contentEditor" is present)
	 * - <jquery.advancedOptions> (if element ".advancedOptionsToggle a" is present)
	 * - <jquery.wysiwygSydney> (if element ".sydneyeditor" is present)
	 * - <jquery.helpBox> (if element "#content > .helpbox" is present)
	 * - <jquery.searchForm> (if element "#searchinput" is present)
	 * - <jquery.treeSetup> (if element ".tree" is present)
	 * - <jquery.structureEditor> (if element "#sitemap" is present)
	 * - <jquery.filemanager> (if element "#filelisting" is present)
	 * - <jquery.fileUpload> (if element "#fileUploadBox" is present)
	 * @constructor
	 */
	function adminInit() {
		if($(".contentEditor").length > 0) 				ANTIDOT.Registry.smartEditor = $(".contentEditor").smartEditor();
		if($(".advancedOptionsToggle a").length > 0) 	ANTIDOT.Registry.advancedOptions = $('.advancedOptionsToggle a').advancedOptions();
		if($(".helpbox").length > 0) 					ANTIDOT.Registry.helpBox = $(".helpbox").helpBox();
		if($("#searchinput").length > 0) 				ANTIDOT.Registry.searchForm = $('#searchinput').searchForm();
		if($(".tree").length > 0) 						ANTIDOT.Registry.treeSetup = $(".tree").treeSetup();
		if($("#sitemap").length > 0) 					ANTIDOT.Registry.structureEditor = $("#sitemap").structureEditor();
		if($("#fileUploadBox").length > 0) 				ANTIDOT.Registry.fileUpload = $("#fileUploadBox").fileUpload();
		if ($("#dashboardListActivities").length > 0) ANTIDOT.Registry.dashboard = $("#dashboardListActivities").dashboard();
		if ($('p.lastUpdatedContent').length > 0) 		ANTIDOT.Registry.tooltips = $('p.lastUpdatedContent').tooltipify();
		ANTIDOT.Registry.trigger = $(this).Antidot_Trigger();
	};
	/**
	 * post data to a service and get the respponse a JSON
	 * (I don't know why this is missing from jQuery)
	 *
	 * @param url - {String} url
	 * @param data - {Object} data
	 * @param callback - {Object} callback
	 * @returns Object
	 * @constructor
	 */
	$.postJSON = function( url, data, callback ) {
			return jQuery.post(url, data, callback, "json");
	};
	/**
	 * Log data to the console if available
	 * @param msg - {Mixed} msg
	 */
	$.log = function( msg )
	{
		if (window.console && debugmode) console.log(msg);
	};
	/**
	 * This method is launched on document ready by the launcher script.
	 * @constructor
	 */
	$.sydneyInit = function()
	{
		adminInit();
	};
	/**
	 * This function will show a message in the default sydney messagebox.
	 * @constructor
	 */
	$.showmsg = function(msg, modal) {
		if (modal == undefined) modal = false;
		$('#ajaxbox').msgbox({
			'message': msg,
			'modal': modal
		});
	};
})(jQuery);
