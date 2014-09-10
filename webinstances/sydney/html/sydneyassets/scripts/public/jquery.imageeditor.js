/**
 * 
 * @member $.fn.imageeditor
 */
(function($){
	/**
	 * 
	 * @author: Arnaud Selvais
	 * @constructor
	 */
	$.fn.imageeditor = function(prefs)
	{
		var optsdefault = {
				'ajaxboxid': '#ajaxbox',
				'toolbar':'.toolbar',
				'stage':'.stage'
		};
		var opts = {};
		$.extend(opts, prefs, optsdefault);

		var cntr = $(this);
		var toolbar = $(opts.toolbar , cntr);
		var stage = $(opts.stage , cntr);
		var filid = opts.filfiles_id;
	    var Dom = YAHOO.util.Dom;
	    var Event = YAHOO.util.Event;
	    var conn = null;
	    var results = null;
	    var imgsrc=null;
	    /**
	     * Initialize the image cropper
	     */
		function initCropper() {
		    var crop = new YAHOO.widget.ImageCropper('yui_img', {
		        initialXY: [20, 20],
		        keyTick: 5,
		        shiftKeyTick: 50
		    });
		    $('.yui-crop-resize-mask', stage).dblclick(function(){
		        var coords = crop.getCropCoords();
		        coords.id = filid;
		        coords.imgwidth = $('#yui_img').width();
		        coords.imgheight = $('#yui_img').height();
		        delete coords.image;
		        $('.crop', toolbar).removeClass('iconhover');
		        imageReset('cropimage',coords);
		    	crop.destroy();
		    });
		};
	    /**
	     * 
	     */
		function initToolbar() {
			$('.crop', toolbar).click(function(){
		    	$('.crop', toolbar).addClass('iconhover');
		    	initCropper();
		    });
			$('.rotatel', toolbar).click(function(){ imageReset('rotate', {'id': filid, 'val': '-90'} ); });
			$('.rotater', toolbar).click(function(){ imageReset('rotate', {'id': filid, 'val': '90'} ); });
			$('.revert', toolbar).click(function(){ imageReset('revert', {'id': filid} ); });
			$('.fliph', toolbar).click(function(){ imageReset('flip', {'id': filid, 'val': 'h'} ); });
			$('.flipv', toolbar).click(function(){ imageReset('flip', {'id': filid, 'val': 'v'} ); });
			$('.reflection', toolbar).click(function(){ imageReset('reflection', {'id': filid} ); });
			$('.contrast', toolbar).click(function(){ imageReset('contrast', {'id': filid} ); });
			$('.sharpen', toolbar).click(function(){ imageReset('sharpen', {'id': filid} ); });
			$('.blacknwhite', toolbar).click(function(){ imageReset('blacknwhite', {'id': filid} ); });
			$('.zoomin', toolbar).click(function(){ imageReset('scale', {'id': filid, 'val': '10'} ); });
			$('.zoomout', toolbar).click(function(){ imageReset('scale', {'id': filid, 'val': '-10'} ); });
			
			// init position and drag and drop
			var ofo = cntr.offset();
			toolbar.css({'top':(ofo.top+40)+'px', 'left':(ofo.left+5)+'px', }).draggable({'handle':'.movebar'});
		};
		/**
		 * 
		 */
		function imageReset(cmd, params)
		{
			if (imgsrc == null) imgsrc = '/adminimageeditor/services/showimg/id/'+filid+'/';
			else if (cmd != undefined) imgsrc = '/adminimageeditor/services/'+cmd;
			if (params != undefined) for(var p in params) imgsrc += '/' + p + '/' + params[p];
			imgsrc += '/tms/'+Number(new Date())+'/';
		    stage.html('<img src="'+imgsrc+'" id="yui_img">');
		};
		/**
		 * 
		 */
		function init()
		{
			initToolbar();
			imageReset();
			$('.savebutton', cntr).click(function(){
				$.getJSON('/adminimageeditor/services/saveimage/format/json/id/'+filid, function(json){
					alert(json.message);
					window.location = '/adminfiles/index/index/';
				});
			});
		}; 
		init();
	};

})(jQuery);
