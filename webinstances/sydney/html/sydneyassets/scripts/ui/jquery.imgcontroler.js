/*
	Group: jquery plugins
*/

(function($){
	var imgobj;
	var tempdiv = null;
	/**
	 * 
	 */
	function init() {
		imgobj.unbind('dblclick');
		imgobj.dblclick(ondblclick);
	};
	/**
	 * 
	 */
	function addtempdiv()
	{
		$('body').append('<div id="imgcontrolertempdiv" class="floatWin"/>');
		tempdiv = $('#imgcontrolertempdiv');
		tempdiv.width( 250 );
		tempdiv.height( 170 );
		var pos = $('li[class=editing]').offset();
		tempdiv.css('top', pos.top + 'px' );
		tempdiv.css('left', pos.left+$('li[class=editing]').width() + 'px' );
		tempdiv.html('Loading image editor...');
		loadEditor();
	};
	/**
	 * 
	 */
	function deltempdiv()
	{
		tempdiv.remove();
	};
	/**
	 * 
	 */
	function loadEditor()
	{
		var mhtml = '<table>';
		mhtml += '<tr><td>Align</td><td><div>';
			mhtml += '<a href="align-left" class="button muted">Left</a> ';
			mhtml += '<a href="align-none" class="button muted">None</a> ';
			mhtml += '<a href="align-right" class="button muted">Right</a> ';
		mhtml += '</div></td></tr>';
		mhtml += '<tr><td>Width</td><td><input type="text" value="'+imgobj.attr('width')+'" name="chwidth-val"><a class="button muted" href="chwidth">Set</a></td></tr>';
		mhtml += '<tr><td>Alternate text</td><td><input type="text" value="'+imgobj.attr('alt')+'" name="chalt-val"><a class="button muted" href="chalt">Set</a></td></tr>';
		mhtml += '<tr><td>Horizontal space</td><td><input type="text" value="'+imgobj.attr('hspace')+'" name="chhspace-val"><a class="button muted" href="chhspace">Set</a></td></tr>';
		mhtml += '<tr><td>Vertical space</td><td><input type="text" value="'+imgobj.attr('vspace')+'" name="chvspace-val"><a class="button muted" href="chvspace">Set</a></td></tr>';
		mhtml += '</table>';
		mhtml += '<center><a class="button" href="close">Close</a></center>';
		tempdiv.html( mhtml );
		setEvents();
	};
	/**
	 * 
	 */
	function execute( cmd ) {
		if (cmd == 'close') deltempdiv();
		if (cmd == 'align-right') imgobj.attr('align', 'right');
		if (cmd == 'align-left') imgobj.attr('align', 'left');
		if (cmd == 'align-none') imgobj.attr('align', '');
		if (cmd == 'chwidth') {
			var oldsrc = imgobj.attr('src');
			var uels = oldsrc.split('/');
			var nwidth = $('input[name=chwidth-val]', tempdiv).val();
			var nurl = '';
			var gotit = false;
			for (var i=1; i < uels.length; i++)
			{
				if (uels[i] == 'dw') {
					uels[i + 1] = nwidth;
					gotit = true;
				}
				nurl += '/'+uels[i];
			}
			if (!gotit) nurl += '/dw/'+nwidth;
			imgobj.attr('src', nurl);
			imgobj.attr('width', nwidth);
		}
		if (cmd == 'chalt') imgobj.attr('alt', $('input[name=chalt-val]', tempdiv).val() );
		if (cmd == 'chvspace') imgobj.attr('vspace', $('input[name=chvspace-val]', tempdiv).val() );
		if (cmd == 'chhspace') imgobj.attr('hspace', $('input[name=chhspace-val]', tempdiv).val() );
	}
	/**
	 * 
	 */
	function setEvents()
	{
		$('a', tempdiv).click(function(e){
			e.preventDefault();
			execute( $(this).attr('href') );
		});
	};
	/**
	 * 
	 */
	function ondblclick(e)
	{
		addtempdiv();
	};
	/**
	 * jQuery plugin for controling the image property when embeded in the WYSIWYG editor.
	 * Initializing the help box container and buttons.
	 * @constructor
	 */
	$.fn.imgcontroler = function()
	{
		imgobj = $(this);
		init();
		return imgobj;
	};
})(jQuery);
