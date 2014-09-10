/*
	Group: jquery plugins
*/

(function($){
	/**
	 * jQuery module for triggering ajax call and launch automatic actions
	 * @constructor
	 */
	$.fn.Antidot_Contenttabs = function() {
		$('.tabs li').unbind();
		$('.tabs li').each(function(index,element) {
			$(this).click(function () {
				var myid 	= $(this).attr('id');
				var dbid	= myid.substring(myid.lastIndexOf('-')+1,myid.length);
				var status	= myid.substring(0,myid.lastIndexOf('-'));			
				///adminpages/services/getdivwitheditor/?status=draft&dbid=
				
				if (status == 'published') {
					$('#draft-' + dbid).removeClass('active');
					$('#draft-' + dbid).addClass('notactive');
					$('#published-' + dbid).removeClass('notactive');								
					$('#published-' + dbid).addClass('active');
				} else {
					$('#draft-' + dbid).addClass('active');
					$('#draft-' + dbid).removeClass('notactive');
					$('#published-' + dbid).removeClass('active');
					$('#published-' + dbid).addClass('notactive');
				}

				var item = $("li[dbid="+dbid+"]");
				$.get("/adminpages/services/getdivwitheditor/", {'dbid': dbid,'status':status}, function(data){
			    	item.replaceWith(data);
					$("li[dbid="+item.attr('dbid')+"]").makeEditable();
					if (status == 'draft') {
						$("li[dbid="+item.attr('dbid')+"]").addClass('draft');
					} else {
						$("li[dbid="+item.attr('dbid')+"]").removeClass('draft');
					}		    	
		    	});	
				return false;
			});		
		}); 
		return this;
	};
})(jQuery);