/**
 * Group: jquery plugins
 */

(function($){
	var filedialog;
	/**
	 * Property: loadingArea
	 * {jQuery} Div which will contain the file lists
	 */
	var loadingArea;
	/**
	 * Property: buttonsIds
	 * {String} IDs of buttons launching some actions in the file manager
	 */
	var buttonsIds = "#thumbviewb,#listviewb,#prevbut-header,#nextbut-header,#prevbut-footer,#nextbut-footer,#descbut,#bselect,#butdeselall,#butselall,#butdeselinv,#butdelselected,#butaskeywordselected";
	/**
	 * Property: opts
	 * {object} Object litteral containing the app options
	 * Properties:
	 * 	context - Defines the context in which the file manager is launched
	 * 	mode - the display mode we are in (thumb or list)
	 * 	order - order #
	 * 	desc - order asc or desc
	 * 	filter - filter #
	 * 	listcount - number of item to display in list mode
	 * 	resultcount - number of item to display on thumbnail mode
	 * 	count - current max number of images displayed (for pagination-header)
	 * 	offset - current offset (for pagination-header)
	 * 	pagenbr - current page number
	 * 	nbpages - total number of pages (instanciated in the HTML received in response)
	 */
	var opts={
		'embeded' : 'no',
		'context':'default',
		'mode':'thumb',
		'order':0,
		'desc':1,
		'filter':0,
		'listcount':200,
		'resultcount':10,
		'count':10,
		'offset':0,
		'pagenbr': 1,
		'nbpages':0,
		'tags': ''
	};

	/**
	 * Method: paginator
	 * Resets the paginator (steps for browsing)
	 */
	$.fn.refresh = function ()
	{
		var cnt = '';
		for (var i = 1; i <= opts.nbpages; i++) {
			
			if (i == 1 || i == opts.nbpages || opts.pagenbr == i || i == opts.pagenbr-1 || i == opts.pagenbr-2 || i == opts.pagenbr+1 || i == opts.pagenbr+2) {


				// point before last page
				if (opts.pagenbr <=  opts.nbpages - 4 && i == opts.nbpages) {
					cnt += ' ... ';
				}					
				
				if (opts.pagenbr == i) {
					cnt += '<li><input value="'+i+'" id="txtPagitem" class="pagitemtxt" /></li> ';
				} else {
					cnt += '<li><a href="'+i+'" id="pagitem'+i+'" class="pagitemcls">'+i+'</a></li> ';
				}
				
				// point after "1"
				if (i == 1 && opts.pagenbr > 4) {
					cnt += ' ... ';
				}				
				
			}
		}
		$('#pagination-header').html(cnt);
		$('#pagination-footer').html(cnt);
		$('.pagitemcls').click(function(event){
			event.preventDefault();
			var tid = parseInt( $(event.currentTarget).attr('href') );
			resultviewAction({
				'pagenbr': tid,
				'offset': (tid-1)*opts.count
			});
		});
		$('.pagitemtxt').keypress(function(event) {
			if (event.keyCode != 13) {
				return;
			}
			event.preventDefault();
			var tid = parseInt( $(event.currentTarget).attr('value') );
			if (tid >= 1 && tid <= opts.nbpages) {
				resultviewAction({
					'pagenbr': tid,
					'offset': (tid-1)*opts.count
				});
			}
		});
	};
	/**
	 * Method: refreshOpts
	 * Updates the pagination-header system
	 * @private
	 */
	$.fn.refreshOpts = function(opt)
	{
		opts.offset 	= 0;
		opts.pagenbr 	= 1;
		return opts = $.extend(opts,opt);
	};
	$.fn.refreshPaginator = function()
	{
		addspactions();
	};
	/**
	 * Method: addspactions
	 * Updates the pagination-header system
	 * @private
	 */	
	function addspactions()
	{		
		$('#ajaxbox').msgbox({'message':'OK !','showtime':1,'modal':false});
		$('.bselect').click(function(event){ onSelect(event);  });
		$('.bselect').dblclick(function(event){ onEdit(event); });
		$('.bedit').click(function(event){ onEdit(event); });
		$('.bdelete').click(function(event){ onDelete(event); });

		// update the page numbers if any
		if (typeof gblnumpages != 'undefined') {
			opts.nbpages = gblnumpages;
			$('#prevbut-header,#nextbut-header,#prevbut-footer,#nextbut-footer').show();
			$('#prevbuto-header,#nextbuto-header,#prevbuto-footer,#nextbuto-footer').hide();
			if (opts.pagenbr <= 1) {
				$('#prevbut-header,#prevbut-footer').hide();
				$('#prevbuto-header,#prevbuto-footer').show();
			}
			if (opts.pagenbr >= opts.nbpages) {
				$('#nextbut-footer,#nextbut-header').hide();
				$('#nextbuto-footer,#nextbuto-header').show();
			}
			$(this).refresh();

			filedialog = $('.filepropdialog', loadingArea).dialog({
				'title':'File properties',
				'width':600,
				'height': 500,
				'closeOnEscape': false,
				autoOpen: false,
				close: function(){ filedialog.html('...'); }
			});
			filedialog.html('Loading...');

		}
	};
	/**
	 * Method: resultviewAction
	 * Action displaying the list of files in thumbnails.
	 * @param opt - {Object} opts Options mode,order,count,offset
	 * @private
	 */
	function resultviewAction( opt )
	{
		$.extend( opts, opt );
		loadingArea.html("Loading ...");
		$('#ajaxbox').msgbox( {'message': 'Loading...', 'showtime':0,'modal':false} );
		loadingArea.load(opts.ajaxurl_displayresult,opts, addspactions);
		$(buttonsIds).removeClass();
		if (opts.mode == 'thumb') $('#thumbviewb').addClass('active');
		if (opts.mode == 'list') $('#listviewb').addClass('active');
	};

	/**
	 * Method: onClickBtn
	 * Launched when a show/hide button is clicked.
	 * @private
	 * @param event - {event} event
	 */
	function onClickBtn(event)
	{
		event.preventDefault();
		$('#subbutselected').hide();
		var tid = event.currentTarget.id;
		if (tid == 'thumbviewb') resultviewAction( {'mode': 'thumb', 'count':opts.resultcount } );
		if(tid == 'listviewb') resultviewAction( {'mode': 'list', 'count':opts.listcount} );
		if(tid == 'prevbut-header' || tid == 'prevbut-footer') resultviewAction( {'offset': opts.offset-opts.count, 'pagenbr': opts.pagenbr-1  } );
		if(tid == 'nextbut-header' || tid == 'nextbut-footer') resultviewAction( {'offset': opts.offset+opts.count, 'pagenbr': opts.pagenbr+1 } );
		if (tid == 'descbut') {
			var mdesc=0;
			if (opts.desc == 0) mdesc=1;
			resultviewAction({
				'desc': mdesc
			});
		}
	};


	/**
	 * Method: gsearchTags
	 * Search for files with the tags found in sstr
	 * @private
	 */
	function gsearchTags( sstr )
	{
		// $.log('search tags for '+ sstr);
		resultviewAction( {'filter':20, 'tags':sstr} );
	};

	/**
	 * Main initialization
	 */
	function init()
	{
		$(buttonsIds).click(function(event){ onClickBtn(event); });
		$('#prevbuto-header,#nextbuto-footer').hide();
		resultviewAction();
	};
	/**
	 * jQuery plugin for the file manager.
	 * This will manage all the aspects and funcionalities of the file manager.
	 * As it is like a stand alone soft we can integrate it and use it in several contexts (page, popup, ...).
	 * Constructor, Initializing the help box container and buttons.
	 * @author Arnaud Selvais
	 * @param adopts - {Object} Options Options to be added to the global private object opts
	 * @constructor
	 */
	$.fn.paginator = function(adopts)
	{
			opts = $.extend(opts,adopts);
			loadingArea = $(this);
			init();
			
			this.searchTags = function( sstr ) {
				gsearchTags( sstr );
			};
			this.opts = opts;
			
			return this;
	};
})(jQuery);
