/**
 * @member $.fn.sydimagefader
 */
(function($){
	/**
	 * @constructor
	 */
	$.fn.sydimagefader = function(prefs)
	{
		var opts={};
		var defaults={
				'imgwidth': 900,
				'imgheigth': 300,
				'slidepause': 4,
				'imgar': [
					 {'url':'sydneyassets/images/banner_img01.jpg'},
					 {'url':'sydneyassets/images/banner_img02.jpg'},
					 {'url':'sydneyassets/images/banner_img03.jpg'},
					 {'url':'sydneyassets/images/banner_img04.jpg'},
					 {'url':'sydneyassets/images/banner_img05.jpg'}
				 ] 
		};
		var step=0;
		function getDiv()
		{
			var ht = '<div style="width:'+opts.imgwidth+'px;height:'+opts.imgheigth+'px;overflow:hidden;">';
			
			for (var i=0; i < opts.imgar.length; i++ )
			{
				ht += '<div class="mmimgg'+i+'"><img src="'+opts.imgar[i].url+'" width="'+opts.imgwidth+'" height="'+opts.imgheigth+'"></div>';
			}
			return ht+'</div>';
		};
		var sup = false;
		function slider()
		{
			if (sup) {
				$('.mmimgg'+step).slideDown('slow');
				step--;
				setTimeout(slider, (opts.slidepause*1000) );
				if (step == -1) sup = false;
			} else {
				$('.mmimgg'+step).slideUp('slow');
				step++;
				setTimeout(slider, (opts.slidepause*1000) );
				if (step == opts.imgar.length-1) sup = true;
			}
		};
		
		opts = $.extend(defaults, prefs);
		$(this).each(function(e){
			var cntr = $(this);
			cntr.html(getDiv());
			setTimeout(slider, (opts.slidepause*1000) );
		});
	};
})(jQuery);
