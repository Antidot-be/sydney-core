/*
	Group: jquery plugins
*/

/*
	Class: jquery.smartEditor
	Description see constructor
	
	Requires:
		- <jquery.makeEditable>
		- <jquery. msgbox>
 */
(function($) {
	/**
	 * Private method ran when a node is droped (after a drag)
	 * @private
	 */
	function dropMe(o, ui)
	{
		var curZone = 0,
			pagid = $('#pageContent').attr('pagstructureid');
		// Ajout de cette requete pour modifier la zone d'un pagdiv
		if(ui.item.parents('.zone').length){
			var itemId = ui.item.attr('dbid');
			curZone = ui.item.parents('.zone').attr('class').split(' ')[1];
			$.postJSON(
				'/adminpages/services/updatezoneforpagdiv/format/json',{
					'pagstructureid' : pagid,
					'zone' : curZone,
					'pagdivid' : itemId
				},
				function(data) {
					$('#ajaxbox').msgbox(data.ResultSet);
				}
			);
		}

	  	o.buildAddHere();

		$.postJSON('/adminpages/services/updatepagerorder/format/json', {
				'jsondata': $.toJSON( $('#pageContent > li, .placeholder_zone > li').lidbids() ),
				'pagstructureid' : pagid
			},
			function(data) {
				$('#ajaxbox').msgbox(data.ResultSet);
			});
	}
	/**
	 * Smart editor plugin for jquery.
	 * This plugin manages the display of the 'add here buttons' and the events
	 * fired when clicking on the button; and also the drag and prop system for the
	 * paragraphs.
	 * @param options - {Object} options
	 * @return {Array} ?
	 * @constructor
	 */
	$.fn.smartEditor = function(options) {
		var defaults = {};
		var options = $.extend(defaults, options);


        $('.add_first').click(function(e){
            e.preventDefault();
            var tempElm = $('<div class="tmpElm"/>');
            $(this).parents(".zone_name").next('.contentEditor').prepend(tempElm);
            $(this).buildAddContent({target: tempElm});
        });


		return this.each(function(){

			$('.placeholder_zone').sortable({
				connectWith: '.placeholder_zone'
			});

	        var o = $(this);


			// Make list sortable
			o.sortable({
				cursor: "move",
				items: "> li",
				handle: ".move",
				opacity: 0.85,
				scrollSensitivity: 100,
				scrollSpeed: 50,
				zIndex: 150,
		        tolerance: "pointer",
				helper: "clone",
				placeholder: "placeholder",
				connectWith: '.placeholder_zone',
				forcePlaceholderSize: true,
				start: function(e, ui){
				  	ui.helper.addClass("sorting");
					o.removeAddHere();
				},
				stop: function(e, ui){
					dropMe(o, ui);
				}
			});
			$("> li", o).makeEditable();
			o.buildAddHere();
			// Add actions for static "Add content" at bottom
	        var staticAddContent = $(".addContentStatic", o);
			staticAddContent.find("a").click(function(e){
				e.preventDefault();
				// Hide the tip
		        $(".contentEditor .tip").hide();
				// Call action
			   	o.addItem({
                    contentType: $(this).data('content-type')
				});
		    });

			o.enable();

		});
	};
	/**
	 * Function: $.fn.buildAddHere
	 * @constructor
	 */
	$.fn.buildAddHere = function() {
		return this.each(function(){
			
	        var o = $(this);
	        o.removeAddHere();
			// Clear ghost margin
			var items = o.children("li:not(.addContentStatic)");
			if(items.length > 0){
				// Add "Add here"
				// var items = o.children("li");
				var addHere = $(".ceUILibrary .addHere").clone();
				items.after(addHere);
				o.prepend(addHere);
				// Setup new Add Here events
				$(".addHere a").click(function(e){
					e.preventDefault();
					o.buildAddContent({target: $(this).parents(".addHere")});
				});
			}
		});
	};
	/**
	 * Function: $.fn.removeAddHere
	 * @constructor
	 */
	$.fn.removeAddHere = function() {
		return this.each(function(){
	        var o = $(this);
			// Remove previous ones
			var items = o.find("div.addHere");
			items.remove();
		});
	};
	/**
	 * Function: $.fn.buildAddContent
	 * @constructor
	 */
	$.fn.buildAddContent = function(options) {
		var defaults = {
			target: null
		};
		var options = $.extend(defaults, options);

		return this.each(function(){
	        var o = $(this);
			o.disable();
			var addContent = $(".ceUILibrary .addContent").clone();
		    options.target.replaceWith(addContent);
			// Setup newly created ADD CONTENT
			addContent.find(".close a").click(function(e){
				e.preventDefault();
				var target = $(this).parents(".addContent");
				o.removeAddContent({
					target: target
				});
			});
			addContent.find(".items a").click(function(e){
				e.preventDefault();
				var target = $(this).parents(".addContent");

			   	o.addItem({
					target: target,
                    contentType: $(this).data('content-type')
				});
		    });
		});
	};
	/**
	 * Function: $.fn.removeAddContent
	 * @constructor
	 */
	$.fn.removeAddContent = function(options) {
		var defaults = {
			target: null
		};
		var options = $.extend(defaults, options);

		return this.each(function(){
			
	        var o = $(this);
			var target = $(options.target);
			if(!target) return false;

		   	target.remove();
		    o.buildAddHere();
			o.enable();
		});
	};
	/**
	 * Function: $.fn.addItem
	 * @constructor
	 */
	$.fn.addItem = function(options) {
		var defaults = {
			contentType: null,
            target: null
		};
		var options = $.extend(defaults, options);
		return this.each(function(){
			var o = $(this);
			target = options.target;
		   	var newItem = $(".ceUILibrary .blankitem").clone(true);
            newItem.data('content-type', options.contentType).data("new", true);

			if(target != null){
				target.replaceWith(newItem);
			} else{
				var staticAddContent = $(".addContentStatic");
                if($('.placeholder_zone').length > 0){
                    $('#sydney_editor').find('.contentEditor').append(newItem);
                } else {
                    staticAddContent.before(newItem);
                }
			}
            newItem.makeEditable();
			newItem.edit();
			o.buildAddHere();
		});
	};
	/**
	 * Function: $.fn.enable
	 * @constructor
	 */
	$.fn.enable = function() {
		return this.each(function(){
			var o = $(this);
			o.attr("enabled", "true");
		});
	};
	/**
	 * Function: $.fn.disable
	 * @constructor
	 */
	$.fn.disable = function(options) {
		return this.each(function(){
			var o = $(this);
			o.attr("enabled", "false");
		});
	};
})(jQuery);