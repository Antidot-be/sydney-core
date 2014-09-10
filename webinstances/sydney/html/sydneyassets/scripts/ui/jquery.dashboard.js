/*
	Group: jquery plugins
*/

(function($){
	var opts = {'ajaxboxid': '#ajaxbox'};
	/**
	 * jQuery plugin for managing the contextual help menu.
	 * Initializing the help box container and buttons.
	 * @constructor
	 */
	$.fn.dashboard = function()
	{
		dashboardDiv = $(this);
		$('#selectDashboardListActivities').change(function(event){
			// dashboardDiv.prepend('Loading...');
			$(opts.ajaxboxid).msgbox({ 'showtime': 0, 'modal': true, 'message':'Loading...' });
			$.post("/admindashboard/services/getlistactivities", { user: $(this).val()}, function (data) {
				$(opts.ajaxboxid).msgbox({ 'showtime': 1, 'modal': false, 'message':'OK' });
				dashboardDiv.html(data);
			});
		});
		return $(this);
	};

})(jQuery);
