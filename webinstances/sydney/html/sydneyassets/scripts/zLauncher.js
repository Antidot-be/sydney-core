/**
 * File: zLauncher.js
 * File containing the 'launcher' scripts for the UI plugins and the FB console inhibition.
 * On document ready calls <jquery.sydneyutils.adminInit>
 */


/**
 * on document ready run the $.sydneyInit
 */
$(function(){
	$.sydneyInit();
	$('#modalBackground').hide();
	window.onunload = function() { $('#modalBackground').show(); };
});

var status = 'published';
var contentEditor = '';
var CKEDITOR_BASEPATH = '/sydneyassets/jslibs/ckeditor/';
