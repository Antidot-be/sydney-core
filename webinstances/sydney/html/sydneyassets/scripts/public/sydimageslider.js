/**
 * @member $.fn.sydimageslider
 */
(function($){
	/**
	 * jquery plugin for displaying a set of images as a slide show.
	 * We expect this plugin to use the sydney /publicms/file/showimg/ tool for getting and displaying the actual PNG images
	 *
	 * @param {object} prefs Object litteral overriding the default params
	 * @param [prefs.imgar="[ {'id':6191,'label':'','link':''} ]"]	Array of images we want to display
	 * @param [prefs.imgwidth="200"]	Width of the images
	 * @param [prefs.imgurl="/publicms/file/showimg/dw/200/id/+++/fn/+++.png"]	URL of the image where #id# is the ID of the image (or a processed var)
	 *
	 * @constructor
	 */
	$.fn.sydimageslider = function(prefs)
	{
		var ttimeout1;
		var ttimeout2;
		clearTimeout(ttimeout1);
		clearTimeout(ttimeout2);
		/**
		 * Processes the URL with the vars for the ID and the
		 * @param id
		 * @returns {string} complete URL of the image
		 */
		var urlcnvrtr = function( id ) {
			var expr = new RegExp('\#id\#');
			var expr2 = new RegExp('\#imgname\#');
			var expr3 = new RegExp('\#size\#');
			var r1=opts.imgurl.replace(expr, id);
			var r2=r1.replace(expr2, id);
			return r2.replace(expr3, opts.imgwidth);
		};
		/**
		 * 
		 */
		var opts={};
		/**
		 * 
		 */
		var defaults={
				'hasdesc': true,
				'shouldhide': true,
				'converturl': true,
				'imgar': [],
				'imgwidth': 440,
				'imgheigth': 315,
				'slidepause': 4,
				'effects': ['slideUp','slideDown'],
				'imgurl': '/publicms/file/showimg/dw/#size#/id/#id#/fn/#imgname#.png'
		};
		opts = $.extend(defaults, prefs);
		
		// console.log(  $(this)  );
		
		$(this).each(function(e){
			opts.ctnr = $(this);
			if (opts.shouldhide) opts.ctnr.hide();
			var i=0;
			/**
			 * @memberOf $.fn.sydimageslider
			 */
			function nextImgStep() {
				if (opts.hasdesc) ttimeout1 = setTimeout(sdlUpNext, (opts.slidepause*1000) );
				else ttimeout2 = setTimeout(function(){
					opts.ctnr[opts.effects[0]]('slow', function(){
						showImgFl();
					});
				}, (opts.slidepause*1000) );
			};
			/**
			 * @memberOf $.fn.sydimageslider
			 */
			function sdlUpNext() {
				$('.sydimsttile.dsee2', opts.ctnr)[opts.effects[0]]('fast');
				$('.sydimsttile.dsee1', opts.ctnr)[opts.effects[0]]('fast', function(){
					opts.ctnr[opts.effects[0]]('slow', function(){
						showImgFl();
					});
				});
			};
			/**
			 * @memberOf $.fn.sydimageslider
			 */
			function showImgFl(){
				var optss = {
						'width': opts.imgwidth,
						'heigth': opts.imgheigth,
						'border': 0,
						'class':'sydimstimg'
					};
				if (opts.converturl) optss.src = urlcnvrtr(opts.imgar[i].id);
				else optss.src = opts.imgar[i].url;
				if (opts.label != undefined) optss.alt= opts.imgar[i].label;
				if (opts.imgar[i].link != undefined && opts.imgar[i].link != '') optss.onclick = 'window.location=\''+opts.imgar[i].link+'\';';
					$('<img />').attr(optss).load(function(){
						opts.ctnr.html( $(this) )[opts.effects[1]]('slow', function(){
							var tltl = opts.imgar[i].label;
							var dsee = opts.imgar[i].desc;
							var mo=$('.sydimstimg', opts.ctnr).offset();
							$('.sydimsttile', opts.ctnr).remove();
							if (tltl != undefined && tltl != '')
							{
								opts.ctnr.append('<div class="sydimsttile dsee1">'+tltl+'</div>');
								$('.sydimsttile.dsee1', opts.ctnr).css({'top':mo.top+2,'left':mo.left+2})[opts.effects[1]]('fast');
							}
							if (dsee != undefined && dsee != '')
							{
								opts.ctnr.append('<div class="sydimsttile dsee2">'+dsee+'</div>');
								var ellk=$('.sydimsttile.dsee2', opts.ctnr);
								ellk.css({'top':mo.top+opts.imgheigth+4,'left':mo.left, 'width':opts.imgwidth-28+'px' })[opts.effects[1]]('fast');
							}
							i++;
							if (opts.imgar.length == i) i=0;
							nextImgStep();
						});
				});
			};
			showImgFl();
		});
	};
})(jQuery);
