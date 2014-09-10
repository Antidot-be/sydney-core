/**
 * 
 * @member $.fn.fileproperties
 */
(function($){
	/**
	 * file name jQuery element
	 */
	var name;

	var filfolders;
	/**
	 * description jQuery element
	 */
	var desc;
	/**
	 * file id
	 */
	var fid;
	/**
	 *
	 */
	var serviceUrl = '/adminfiles/services/savefileprops/format/json';
	/**
	 * Method: save
	 * Save the changes for the file properties
	 */
	function save(e)
	{
		e.preventDefault();
		if (!$('#butfpropsave').hasClass('muted')) {
			var d = {
				'filename': name.val(),
				// 'tags': tags.flatval(),
				'filfolders' : filfolders.val(),
				'desc': desc.val(),
				'fid': fid.val(),
				'istagged': $('#istagged').prop('checked')
			};
			//$.log( d );
			$('#butfpropsave').addClass('muted');
			$.postJSON(serviceUrl, d, function(e, t){
				if (t == 'success') {
					$('#butfpropsave').removeClass('muted');
					$.showmsg('Data saved');
					$('.ui-dialog-titlebar-close').click();
				}
			});
		}
	};
	/**
	 * Jquery plugin for managing the file properties form
	 * @constructor
	 */
	$.fn.fileproperties = function(opt)
	{
		name = $('#filename');
		filfolders = $('#filfolders');
		desc =  $('#filedescription');
		fid = $('#propfileid');
		$('.butfpropsave').click(save);
		return $(this);
	};

})(jQuery);
