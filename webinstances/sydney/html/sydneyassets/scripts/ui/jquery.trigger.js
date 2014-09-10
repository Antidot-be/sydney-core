/*
	Group: jquery plugins
*/

(function($){
	/**
	 * jQuery module for triggering ajax call and launch automatic actions
	 * @constructor
	 */
	$.fn.Antidot_Trigger = function() {		
		/*
		 * Add automatic action when ajax request completed
		 */
		$('#content').ajaxComplete(function(event,request, settings){
			var ajax_url = settings.url;
			// when delete page node
			$("[jq-trigger]").each(function (data,i) {					
				var trigger = $(this).attr('jq-trigger');
				var triggerList = trigger.split(' ');
				for (i=0; i < triggerList.length;i++) {
					if (ajax_url.indexOf(triggerList[i]) != -1) {
						if ($(this).attr('jq-load') != undefined) {
							$(this).load($(this).attr('jq-load'));
						}
					}
				}

			});
			//}  
		});
		
		return this;
	};
})(jQuery);