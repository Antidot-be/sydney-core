/**
 * @member $.fn.filemanager
 */
(function($){
	/**
	 * Dialog containing the tree for filters
	 * @memberOf $.fn.filemanager
	 * @private
	 */
	var diaaFilters;
	/**
	 * @memberOf $.fn.filemanager
	 * @private
	 */
	var filedialog;
	/**
	 * Property: fileArea
	 * {jQuery} Div which will contain the file lists
	 * @memberOf $.fn.filemanager
	 * @private
	 */
	var fileArea;
	/**
	 * Property: buttonsIds
	 * {String} IDs of buttons launching some actions in the file manager
	 * @memberOf $.fn.filemanager
	 * @private
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
	 * 	thumbcount - number of item to display on thumbnail mode
	 * 	count - current max number of images displayed (for pagination)
	 * 	offset - current offset (for pagination)
	 * 	pagenbr - current page number
	 * 	nbpages - total number of pages (instanciated in the HTML received in response)
	 * @memberOf $.fn.filemanager
	 * @private
	 */
	var opts={
		'embeded' : 'no',
		'context':'default',
		'mode':'thumb',
		'order':0,
		'desc':1,
		'filter':0,
		'listcount':200,
		'thumbcount':16, 
		'count':16,
		'offset':0,
		'pagenbr': 1,
		'nbpages':0,
		'tags': '',
		'q': '',
		'folder': false
	};
	/**
	 * Property: sortdd
	 * {object} List of options for sorting
	 * @memberOf $.fn.filemanager
	 * @private
	 */
	var sortdd = {
		0:'Date modified',
		1:'Name',
		2:'File weigth'
	};
	/**
	 * Property: filterdd
	 * {object} List of options for filters
	 * @memberOf $.fn.filemanager
	 * @private
	 */
	var filterdd = {
		0:'Show all',
		100:'-',
		1:'Pictures',
		2:'Video',
		3:'Audio',
		4:'Office documents',
		5:'PDF documents',
		6:'Flash',
		7:'Web documents',
		8:'Archives'
	};
	/**
	 * Method: paginator
	 * Resets the paginator (steps for browsing)
	 * @memberOf $.fn.filemanager
	 * @private
	 */
	function paginator()
	{
		var cnt = '';
		for (var i = 1; i <= opts.nbpages; i++) {
			if (i == 1 || i == opts.nbpages || opts.pagenbr == i || i == opts.pagenbr-1 || i == opts.pagenbr-2 || i == opts.pagenbr+1 || i == opts.pagenbr+2) {
				// point before last page
				if (opts.pagenbr <=  opts.nbpages - 4 && i == opts.nbpages) cnt += ' ... ';
				if (opts.pagenbr == i) cnt += '<li><input value="'+i+'" id="txtPagitem" class="pagitemtxt" /></li> ';
					else cnt += '<li><a href="'+i+'" id="pagitem'+i+'" class="pagitemcls">'+i+'</a></li> ';
				// point after "1"
				if (i == 1 && opts.pagenbr > 4)  cnt += ' ... ';
			} 
		}
		if (opts.nbpages == 0) cnt += '<li><input value="1" id="txtPagitem" class="pagitemtxt" /></li> ';
		$('#pagination-header').html(cnt);
		$('#pagination-footer').html(cnt);
		$('.pagitemcls').click(function(event) {
			event.preventDefault();
			var tid = parseInt( $(event.currentTarget).attr('href') );
			thumbviewAction({
				'pagenbr': tid,
				'offset': (tid-1)*opts.count
			});
		});
		$('.pagitemtxt').keypress(function(event) {
			if (event.keyCode != 13) return;
			event.preventDefault();
			var tid = parseInt( $(event.currentTarget).attr('value') );
			if (tid >= 1 && tid <= opts.nbpages) {
				thumbviewAction({
					'pagenbr': tid,
					'offset': (tid-1)*opts.count
				});
			}
		});		
	};
	/**
	 * Method: addspactions
	 * Updates the pagination system
	 * @memberOf $.fn.filemanager
	 * @private
	 */
	function addspactions()
	{	
		$('#ajaxbox').msgbox({'message':'OK !','showtime':1,'modal':false});
		$('.bselect.typefile', fileArea).click(function(event){onSelect(event);});
		$('.bselect.typefolder', fileArea).click(function(event){ event.preventDefault(); opts.pagenbr = 1; opts.offset = 0; opts.folder=($(this).attr('href')); thumbviewAction(); });
		$('.bselect.typefile', fileArea).dblclick(function(event){onEdit(event);});
		$('.trselect').click(function(event){onSelect(event);});
		$('.bedit.typefile').click(function(event){onEdit(event);});
		$('.bdelete.typefile').click(function(event){onDelete(event);});
		$('.brename.typefolder').click(function(event){onRenameFolder(event);});
		$('.bdelete.typefolder').click(function(event){onDeleteFolder(event);});

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
			paginator();

			filedialog = $('.filepropdialog', fileArea).dialog({
				'title':'File properties',
				'width':600,
				'height': 500,
				'closeOnEscape': false,
				autoOpen: false,
				close: function(){ filedialog.empty(); }
			});
			filedialog.html('Loading...');
		}
		
		$(document).tooltip({
            items: "li[fileprops]",
            position: { my: "center top+15", at: "center bottom", collision: "flipfit" },
            content: function(){
                return $(this).attr('fileprops');
            }
        });
		
	};
	/**
	 * Method: thumbviewAction
	 * Action displaying the list of files in thumbnails.
	 * @param opt - {Object} opts Options mode,order,count,offset
	 * @memberOf $.fn.filemanager
	 * @private
	 */
	function thumbviewAction( opt )
	{
		$.extend( opts, opt );
		//fileArea.html("Loading ...");
		if (opts.id > 0) {
			fileArea.load('/adminfiles/services/displayedit/id/'+opts.id,null, function(data) {$('#ajaxbox').msgbox({'message':'Loaded','showtime':1,'modal':false});});
		} else {
			$('#ajaxbox').msgbox( {'message': 'Loading...', 'showtime':0,'modal':false} );
			fileArea.load('/adminfiles/services/displayfiles',
					{
					'vmode': opts.mode,
					'order': opts.order,
					'count': opts.count,
					'offset': opts.offset,
					'desc': opts.desc,
					'filter': opts.filter,
					'tags': opts.tags,
					'embeded': opts.embeded,
					'context': opts.context,
					'q': opts.q,
					'folder' : opts.folder
					}
					, addspactions);
			$(buttonsIds).removeClass();
			if (opts.mode == 'thumb') $('#thumbviewb').addClass('active');
			if (opts.mode == 'list') $('#listviewb').addClass('active');
		}
	};
	/**
	 * Methode: onAssignKeywds
	 * Launched on click on the Assign keyword function (for selected items)
	 * @memberOf $.fn.filemanager
	 * @private
	 */
	function onAssignKeywds( e )
	{
		var nbsel = $('.itemselected', fileArea).size();
		if (nbsel <= 0) alert('No items selected...');
		else {
			var ctarget = $(e.currentTarget);
			if ($('.askeyslayer').length == 0) ctarget.prepend('<div class="askeyslayer"></div>');
			$('.askeyslayer').foldermanager({
					'title': 'Assign categories',
					'cortable': 'filfolders_filfiles',
					'labeltable': 'filfolders',
					'datatable': 'filfiles',
					'dialog': true,
					'onSaveMCallback': onSaveFolders,
					'closeOnEscape':true,
					'autoSaveOnSelect': false,
					'distroydiag': true
			});
		}
	};
	/**
	 * Event occuring when clicking save in the folders window.
	 * @memberOf $.fn.filemanager
	 */
	function onSaveFolders(filfolders_ids)
	{
		var filfiles_ids = [];
		$('.itemselected', fileArea).each(function() {filfiles_ids.push( parseInt($(this).attr('href')) );});
		$('#ajaxbox').msgbox( {'message': 'Processing...', 'showtime':0,'modal':true} );
		$.getJSON('/adminfiles/services/multitagging/format/json/', {'fileFilesIds':filfiles_ids, 'fileFoldersIds':filfolders_ids}, function(data){
			$('#ajaxbox').msgbox(data.ResultSet);
			if (data.ResultSet.status != 0) {
				// $(node).hide();
			}
		});
	};
	/**
	 * Toggle an image on/off (ex for sorting button or filter)
	 * @memberOf $.fn.filemanager
	 * @private
	 */
	function toggleImgOnOff(el, attrType, pos)
	{
		var url = el.attr(attrType);
		var nurl = url;
		var isOn = false;
		if (url.match(/_on\.png$/)) isOn = true;
		if (pos == 'on') isOn = false; 
		if (pos == 'off') isOn = true;
		if (isOn) nurl = url.replace(/_on\.png$/g, ".png"); 
			else {if (!url.match(/_on\.png$/)) nurl = url.replace(/\.png$/g, "_on.png");}
		el.attr(attrType, nurl);
	};
	/**
	 * Method: onClickBtn
	 * Launched when a show/hide button is clicked.
	 * @memberOf $.fn.filemanager
	 * @private
	 * @param event - {event} event
	 */
	function onClickBtn(event)
	{
		event.preventDefault();
		$('#subbutselected').hide();
		var tid = event.currentTarget.id;
		if (tid == 'thumbviewb') thumbviewAction( {'mode': 'thumb', 'count':opts.thumbcount} );
		if(tid == 'listviewb') thumbviewAction( {'mode': 'list', 'count':opts.listcount} );
		if(tid == 'prevbut-header' || tid == 'prevbut-footer') thumbviewAction( {'offset': opts.offset-opts.count, 'pagenbr': opts.pagenbr-1} );
		if(tid == 'nextbut-header' || tid == 'nextbut-footer') thumbviewAction( {'offset': opts.offset+opts.count, 'pagenbr': opts.pagenbr+1} );
		if (tid == 'descbut') {
			var mdesc=0;
			if (opts.desc == 0) mdesc=1;
			toggleImgOnOff($('#'+tid+' > img'), 'src', false);
			thumbviewAction({'desc': mdesc});
		}
	};
	/**
	 * Initialize the "selection" button
	 * @memberOf $.fn.filemanager
	 * @private
	 */
	function initSelectionMenu()
	{
		$('#butselection').sydlistmenu(
		{
			'layout':'linear',
			'title':'Selection',
			'items':[
	         {'label': 'Link to categories',	'action': onAssignKeywds},
	         {'label': 'Delete all selected', 	'action': onDelAllSelected},
	         {
				'label': 'Deselect all',
				'action': function(){
					$('.bselect', fileArea ).removeClass('itemselected');
					$('.file_selected').removeClass('file_selected');
				},
				'separator':true
			 },
	         {
				'label': 'Select all',
				'action': function(){
					$('.bselect', fileArea).addClass('itemselected').parents('li, tr').addClass('file_selected');
				}
			 },
	         {
				'label': 'Inverse selection',
				'action': function(){
					$('.bselect', fileArea).toggleClass('itemselected').parents('li, tr').toggleClass('file_selected');
				}
			 }
	         ]
		});

	};
	/**
	 * Method: onSelect
	 * Event executed on click on the image or link
	 * @param event - {Object} event
	 * @memberOf $.fn.filemanager
	 * @private
	 */
	function onSelect(event)
	{
		event.preventDefault();
		// JTO - 13/09/2013 - Ajout de la class "file_selected"
		if( event.currentTarget.tagName == 'TR' )
		    $('a.bselect', event.currentTarget).toggleClass('itemselected').parents('tr').toggleClass('file_selected');
		else
		    $(event.currentTarget).toggleClass('itemselected').parents('li').toggleClass('file_selected');
	};

	/**
	 * Method: onEdit
	 * Event executed on click on the image or pressing the edit button
	 * @param event - {Object} event
	 * @memberOf $.fn.filemanager
	 * @private
	 */
	function onEdit(event)
	{
		event.preventDefault();
		var elid = $(event.currentTarget).attr('href');
		$('#ajaxbox').msgbox( {'message': 'Loading...', 'showtime':0,'modal':true} );
		// normal mode
		//fileArea.load('/adminfiles/services/displayedit/id/'+elid,null, function(data) {$('#ajaxbox').msgbox({'message':'Loaded','showtime':1,'modal':false}); });
		// windowed mode
		filedialog.load('/adminfiles/services/displayedit/id/'+elid, null, function(data) {$('#ajaxbox').msgbox({'message':'Loaded','showtime':1,'modal':false});});
		filedialog.dialog('open');
	};
	/**
	 * Method: onDelete
	 * Event executed on press delete button
	 * @param event - {Object} event
	 * @memberOf $.fn.filemanager
	 * @private
	 */
	function onDelete(event)
	{
		event.preventDefault();
		if (confirm('Are you sure?')) {
			var elid = $(event.currentTarget).attr('href');
			var nodes = $(event.currentTarget);
			var node = nodes[0].parentNode.parentNode;
			$('#ajaxbox').msgbox( {'message': 'Processing...', 'showtime':0,'modal':true} );
			$.getJSON('/adminfiles/services/deletefile/format/json/id/'+elid, null, function(data){
				var el = $('#ajaxbox').clone().append($('#ajaxbox'));
				el.id = Math.random();				
				$(el).msgbox(data.ResultSet);
				if (data.ResultSet.status != 0) {
					$(node).hide();
				}
			});
		}
	};
	/**
	 * Function: onDelAllSelected
	 * Action executed on click the "delete all selected" item
	 * @memberOf $.fn.filemanager
	 * @private
	 */
	function onDelAllSelected()
	{
		var nbsel = $('.itemselected', fileArea).size();
		if (nbsel <= 0) alert('No items selected...');
		if (nbsel > 0 && confirm('Are you sure you want to delete '+nbsel+' files?')) {
			$('.itemselected', fileArea).each(function() {
				var elid = $(this).attr('href');
				if (opts.mode == 'thumb') {
					var node = $(this).parent();
				} else {
					var node = $(this).parent().parent().parent();
				}
				$.getJSON('/adminfiles/services/deletefile/format/json/id/'+elid, null, function(data){
					$('#ajaxbox').msgbox(data.ResultSet);
					
					if (data.ResultSet.status != 0) {
						$(node).hide();
					}
				});

			});
		}
	};
	/**
	 * Method: setUpDropDown
	 * Sets up the dropdowns for order and filters
	 * @param elid - {string} Element ID
	 * @param mlis - {object} List of data key pais
	 * @param opti - {string} Option could be order or filter (which drop down)
	 * @memberOf $.fn.filemanager
	 * @private
	 */
	function setUpDropDown(elid,mlis,opti)
	{
		$(elid).empty();
		for (s in mlis) {
			$(elid).append('<option value="'+s+'">'+mlis[s]+'</option>');
		}
		if (opti == 'order') $(elid).change(function(){
			thumbviewAction( {'order':$(this).val()} );
		});
		if (opti == 'filter') $(elid).change(function(){
			opts.offset 	= 0;
			opts.pagenbr 	= 1;
			thumbviewAction( {'filter':$(this).val()} );
		});
	};
	/**
	 * Method: gsearchTags
	 * Search for files with the tags found in sstr
	 * @memberOf $.fn.filemanager
	 * @private
	 */
	function gsearchTags( sstr )
	{
		// $.log('search tags for '+ sstr);
		thumbviewAction( {'filter':20, 'tags':sstr} );
	};
	/**
	 *
	 * @memberOf $.fn.filemanager
	 * @private
	 */
	function onSaveAdvfilter(e)
	{
		var tbtn = $('.ui-toolbar-item-filter .fladvfilter' );
		// if (e.length > 0) tbtn.css({'border':'1px dotted #EE0000'}); else tbtn.css({'border':'none'});
		if (e.length > 0) toggleImgOnOff($('.ui-toolbar-item-filter .fladvfilter > img' ), 'src', 'on');
		else toggleImgOnOff($('.ui-toolbar-item-filter .fladvfilter > img' ), 'src', 'off');
		thumbviewAction( {'tags':e, 'offset':0, 'pagenbr': 1} );
	};
	/**
	 *
	 * @memberOf $.fn.filemanager
	 * @private
	 */
	function setupAdvFilters()
	{
		var poo = $('.ui-toolbar-item-filter .fladvfilter' ).offset();
		diaaFilters = $('.ui-toolbar-item-filter .advfilfiltersbox').foldermanager({
			'title': 'Filter on categories',
			'cortable': 'filfolders_filfiles',
			'labeltable': 'filfolders',
			'datatable': 'filfiles',
			'dialog': true ,
			'modal': false,
			'onSaveMCallback': onSaveAdvfilter,
			'autoOpen': false,
			'showcancel': false,
			'position': [poo.left-180, poo.top+20],
			'distroydiag': false,
			'canadditem': false,
			'candelete':false,
			'autoSaveOnSelect': false
		});
		$('.ui-toolbar-item-filter .fladvfilter' ).click(function(e){
			e.preventDefault();
			diaaFilters.dialog('open');
		});
	};
	/**
	 * Happening when the folder button is changed
	 */
	function onFolderModeChange(e) {
		opts.pagenbr = 1; 
		opts.offset = 0;		
		if($('#folderview').attr('checked')) {
			opts.folder = 0;
			$('#createfoldbtn').show();
		} else { 
			opts.folder = false;
			$('#createfoldbtn').hide();
		}
		thumbviewAction();
	};
	/**
	 * Adds a folder in the current dir.
	 */
	function onNewFolder(){
		var mlabel=prompt("Enter the name of the new folder:","New Folder");
		if (mlabel) {
			$.getJSON('/adminfiles/services/addfolder/format/json/', 
				{ 'parentid' : opts.folder, 'label': mlabel },
				function(json){
					thumbviewAction();
				});
		}
	};
	/**
	 * Renaming a folder
	 */
	function onRenameFolder(event) {
		event.preventDefault();
		var elmo = $(event.target);
		var mlabel=prompt("Enter the new name of the folder:",elmo.attr('oldname'));
		if (mlabel) {
			$.getJSON('/adminfiles/services/renamefolder/format/json/', 
				{ 'id' : elmo.attr('href'), 'label': mlabel },
				function(json){
					thumbviewAction();
				});
		}
	};
	/**
	 * Delete a folder
	 */
	function onDeleteFolder(event) {
		event.preventDefault();
		var elmo = $(event.target);
		if (confirm('Are you sure?')) {
			$.getJSON('/adminfiles/services/deletefolder/format/json/', 
					{ 'id' : elmo.attr('href') },
					function(json){
						thumbviewAction();
					});
		}
	};
	/**
	 * Main initialization
	 * @memberOf $.fn.filemanager
	 * @private
	 */
	function init()
	{
		$("#keywordsinputing").dialog( {height: 150} );
		$("#keywordsinputing").dialog('close');
		$(buttonsIds).click(function(event){onClickBtn(event);});

        // Init view folder
        $('#folderview').click();
        $('#folderview').change(onFolderModeChange);
        $('#createfoldbtn').click(onNewFolder);

		$('#prevbuto,#nextbuto').hide();

        thumbviewAction({"folder":true});// Active folder view

        setUpDropDown('#sortBy1',sortdd,'order');
		setUpDropDown('#sortBy2',filterdd,'filter');
		setupAdvFilters();
		initSelectionMenu();
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
	$.fn.filemanager = function(adopts)
	{
			opts = $.extend(opts,adopts);
			fileArea = $(this);
			
			var oneLine = Math.ceil(fileArea.width()/160);
			var numlines = opts.context == "ckeditor"?2:3;
			opts.thumbcount = oneLine*numlines;
			opts.count = oneLine*numlines;
			
			init();
			this.searchTags = function( sstr ) {
				gsearchTags( sstr );
			};
			return this;
	};
})(jQuery);
