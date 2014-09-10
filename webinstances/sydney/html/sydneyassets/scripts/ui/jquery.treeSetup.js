/**
 * Group: jquery plugins
 * @fileoverview
 */

(function($) {
	/**
	 * set up of a branch
	 * @param ul {Object} ul
	 */
	function treeSetupBranch(ul){
		var ul = $(ul);
		var li = $(ul.parent("li").get(0));
		var row = $(">.row", li);
		row.children('.bullet').removeClass("expanded");
		row.children('.bullet').addClass("collapsed");
		row.children('.bullet').unbind("click");
		row.children('.bullet').toggle(treeItemExpand, treeItemCollapse);
	};
	/*
		Method: treeItemExpand

		Properties:
			e - {Object} e
	 */
	function treeItemExpand(e){
		e.preventDefault();
	    $(e.target).removeClass("collapsed");
		$(e.target).addClass("expanded");
		var li = $(e.target).parents("li").get(0);
		var ul = $(">ul", li);
		ul.slideDown("fast");
	};
	/*
		Method: treeItemCollapse

		Properties:
			e - {Object} e
	 */
	function treeItemCollapse(e){
		e.preventDefault();
	    $(e.target).removeClass("expanded");
		$(e.target).addClass("collapsed");
		var li = $(e.target).parents("li").get(0);
		var ul = $(">ul", li);
		ul.slideUp("fast");
	};
	/**
	 * Sets up the tree with show / hide deployment of nodes. This is used for the structure editor.
	 * set's up the process
	 * @constructor
	 * @base jQuery
	 */
	$.fn.treeSetup = function()
	{
		this.each(function(i){
			$("ul", this).each(function(i){
				treeSetupBranch(this);
			});
		});
	};
})(jQuery);
