/*
	Group: jquery plugins
*/

(function($){
	/**
	 * This plugin returns the DB ids found in an array in the order they were found in the DOM
	 * Returns the DBid attributes values
	 * @returns Array DBIDs attributes value
	 * @constructor
	 */
	$.fn.lidbids = function() {
		var pagelis = [];
		this.each(function(){
            if($(this).is('[dbid]')){
                pagelis.push( $(this).attr('dbid') );
            }
        });
		return pagelis;
	};

})(jQuery);
