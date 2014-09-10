/*
	Group: jquery plugins
*/

(function($){
	
	/**
	 * delay to dosplay actions
	 */
	var seconds_to_wait 		= 0;
	var display_action_timer	= null;
	var jqxhrAction				= null; 
	
	/**
	 * Method: saveorder
	 * Saves the order to the DB
	 * @memberOf $.fn.structureEditor
	 * @private saveorder
	 */
	function strSaveorder()
	{
		$('#ajaxbox').msgbox( {'message': 'Saving...', 'showtime':0,'modal':true} );
		var stru = new ANTIDOT.NestedArray('viewcontent');
		$.postJSON('/adminpages/services/updatestrorder/format/json', {
										'jsondata': $.toJSON( stru.aStructure )
									},
			function(data) {
				$('#ajaxbox').msgbox(data.ResultSet);				
				$('#sidebarSaveOrCancel').fadeOut();
			});
	};
	/**
	 * Method: sortStart
	 * Properties:
	 * e - {Object} e
	 * ui - {Object} ui
	 * @memberOf $.fn.structureEditor
	 */
	function sortStart(e, ui){
		var li = $(ui.helper).parents("li").get(0);
		var row = $(".row", li).get(0);
		$(".row", ui.helper).css("background-color", "#fff");
		$(".row", ui.helper).css("border-bottom", "1px solid #777");
		$(".row", ui.helper).css("border-top", "1px solid #777");
		// fix a size for the structure editor
		$(".row", ui.helper).css("width", $('#viewcontent').width()-50+"px");
		$(ui.helper).width($(e.currentTarget).width());
		$(ui.placeHolder).css("background", "#e8e8e8");
		$(ui.placeHolder).width($(e.currentTarget).width());
	};
	/**
	 * Method: sortEnd
	 * @param e - {Object} e
	 * @param ui - {Object} ui
	 * @memberOf $.fn.structureEditor
	 */
	function sortEnd(e, ui){
		$(".row", e.currentTarget).css("background", "transparent");
		var li = $(ui.helper).parents("li").get(0);
		var row = $(".row", li).get(0);
		$('#savebutton').show();
	};
	
	/**
	 * Empty the recycle bin
	 * @param event -
	 * @param node -
	 * @memberOf $.fn.structureEditor
	 */
	function emptyRecyclebin(event, node) {
		event.preventDefault();
		if (confirm('This will delete all nodes!\nAre you sure?')) {
			$('#ajaxbox').msgbox( {'message': 'Saving...', 'showtime':0,'modal':true} );
			$.postJSON(	'/adminpages/recyclebin/deletenode/format/json',null,
						function(data) {
							$('#ajaxbox').msgbox(data.ResultSet);
							if (data.ResultSet.status == 1) {
								$('.liRowRecyclebin').remove();
							}
						}
			);
		}
	};	
	
	/**
	 * Deletes a node after confirmation (ajax request)
	 * @param event -
	 * @param node -
	 * @memberOf $.fn.structureEditor
	 */
	function delnode(event, node)
	{
		event.preventDefault();
		var dbId = $(node).attr('dbid');
		if (confirm('This will delete this node and all its child nodes!\nAre you sure?')) {
			$('#ajaxbox').msgbox( {'message': 'Saving...', 'showtime':0,'modal':true} );
			
			datas = {'dbId': dbId};
			if ($(node).hasClass('deleterestorenodea')) {
				datas = {'dbId': dbId, 'src':'recyclebin'};
			}
			
			$.postJSON(	'/adminpages/services/deletenode/format/json', 
						{'jsondata': $.toJSON( datas )},
						function(data) {
							$('#ajaxbox').msgbox(data.ResultSet);
                                if (datas.hasOwnProperty('src')) {
                                // we are in recycle bin
                                if (data.ResultSet.status == 1) {
                                    $('#recyclebin_' + dbId).remove();
                                }
                                } else {
                                    var dynatree		= $("#viewcontent").dynatree("getTree");
                                    var dynatreenode 	= dynatree.getNodeByKey(dbId);

                                    dynatreenode.remove();
                                    // update the nbr nodes in the sidebar
                                    var nbnsb = $('.pod a[href=/adminpages/recyclebin]');
                                    var nbno = parseInt(nbnsb.text());
                                    if (nbnsb.length <= 0) nbnsb = $('.pod:first > div');
                                    if (isNaN(nbno)) {
                                            nbno=0;
                                            nbnsb.html('Contains <a href="/adminpages/recyclebin">'+(nbno+1)+' node(s)</a> deleted');
                                    } else nbnsb.text((nbno+1)+' node(s)');
                                    hideAction();
                                }
						}
			);
		}
	};

	/**
	 * Restore a node after confirmation (ajax request)
	 * @param event -
	 * @param node -
	 * @memberOf $.fn.structureEditor
	 */
	function restorenode(event, node)
	{
		event.preventDefault();
		var dbId 	= $(node).attr('dbid');
		var url 	= $(node).attr('href');
		if (confirm('This will restore this node and all its child nodes!\nAre you sure?')) {
			$('#ajaxbox').msgbox( {'message': 'Saving...', 'showtime':0,'modal':true} );
			$.postJSON(	url, 
						{'jsondata': $.toJSON( {'dbId': dbId} )},
						function(data) {
							$('#ajaxbox').msgbox(data.ResultSet);
							$('#recyclebin_'+dbId).remove();
						}
			);
		}
		return false;
	};
	
	/**
	 * Publish a node (ajax request)
	 * @param event -
	 * @param node -
	 * @memberOf $.fn.structureEditor
	 */
	function publishnode(event, node) {
		event.preventDefault();
		
                var dynatreenode = false;
                if (typeof(pagstructureid) == "undefined") {
                    var dynatree		= $("#viewcontent").dynatree("getTree");
                    dynatreenode 	= dynatree.getNodeByKey($(node).attr('dbid'));
                }
		
		var dbid = $(node).attr('dbid');
		if (dbid == undefined) {			
			document.location = $(node).attr('href');
		} else {
			//Contact us (Status: draft , View: 0)
			$('#ajaxbox').msgbox( {'message': 'Publishing...', 'showtime':0,'modal':true} );
			$.postJSON('/adminpages/services/publishpage/format/json', 	
					{'jsondata': $.toJSON( {'dbId': dbid} )},
					function(data) {
						$('#ajaxbox').msgbox(data);
						if (dynatreenode) {
							var  title = dynatreenode.data.title;
							title = title.replace("Status: draft","Status: published");
							title = title.replace("Status: restored","Status: published");
							dynatreenode.data.title = title;
							var  addClass = dynatreenode.data.addClass;
							addClass = addClass.replace("draft","published");
							addClass = addClass.replace("restored","published");
							dynatreenode.data.addClass = addClass;
							dynatreenode.render();
						}
						var btnPublish = $('#btn_publish_' + data.dbid).removeClass('publish').addClass('unpublish').text('Unpublish').unbind().click(function(event){unpublishnode(event, $('#btn_publish_' + data.dbid));});
						hideAction();
					}
			);
		}
	};	
	
	/**
	 * Unpublish a node (ajax request)
	 * @param event -
	 * @param node -
	 * @memberOf $.fn.structureEditor
	 */
	function unpublishnode(event, node) {
		event.preventDefault();
		
                var dynatreenode = false;
                if (typeof(pagstructureid) == "undefined") {
                    var dynatree		= $("#viewcontent").dynatree("getTree");
                    dynatreenode 	= dynatree.getNodeByKey($(node).attr('dbid'));
                }
		
		var dbid = $(node).attr('dbid');
		if (dbid == undefined) {			
			document.location = $(node).attr('href');
		} else {
			//Contact us (Status: draft , View: 0)
			$('#ajaxbox').msgbox( {'message': 'Unpublishing...', 'showtime':0,'modal':true} );
			$.postJSON('/adminpages/services/unpublishpage/format/json', 	
					{'jsondata': $.toJSON( {'dbId': dbid} )},
					function(data) {
						$('#ajaxbox').msgbox(data);
						if (dynatreenode) 
						{
							var  title = dynatreenode.data.title;
							title = title.replace("Status: published","Status: draft");
							dynatreenode.data.title = title;
							var  addClass = dynatreenode.data.addClass;
							addClass = addClass.replace("published","draft");
							dynatreenode.data.addClass = addClass;
							dynatreenode.render();
						}
						var btnPublish = $('#btn_publish_' + data.dbid);
						$(btnPublish).removeClass('unpublish').addClass('publish').text('Publish').unbind().click(function(event){publishnode(event, $('#btn_publish_' + data.dbid));});
						hideAction();
					}
			);
		}
	};
        
        /**
         * Duplicate a node (ajax request)
         * @param event -
         * @param node -
         * @memberOf $.fn.structureEditor
	*/
       function duplicatenode(event, node) {
           event.preventDefault();
           
           var dynatree = $('#viewcontent').dynatree("getTree");
           var dbid = $(node).attr('dbid');
           
           $.postJSON(
               '/adminpages/services/duplicatenode/format/json',
               {
                   'jsondata' : $.toJSON( {'dbId': dbid} )
               },
               function(data) {
                   $('#ajaxbox').msgbox(data);
               }
           );
       };
       
	/**
	 * Empty the cache for the structure
	 * @memberOf $.fn.structureEditor
	 */
	function emptyCache( mode )
	{
		$('#ajaxbox').msgbox( {'message': 'Processing...', 'showtime':0,'modal':true} );
		$.postJSON('/adminpages/services/emptycache/format/json', {
										'jsondata': $.toJSON( {'mode': mode} )
									},
			function(data) {
				$('#ajaxbox').msgbox(data.ResultSet);
			});
	};
	/**
	 * Empty the cache for the structure
	 * @param String Can be 'on' or 'off'
	 * @memberOf $.fn.structureEditor
	 */
	function cache4all( mode )
	{
		$('#ajaxbox').msgbox( {'message': 'Processing...', 'showtime':0,'modal':true} );
		$.postJSON('/adminpages/services/setcacheall/format/json', {
										'jsondata': $.toJSON( {'caching': mode} )
									},
			function(data) {
				$('#ajaxbox').msgbox(data.ResultSet);
			});
	};
	/**
	 * Exand and collapse all according to the param passed as arg.
	 * @param exp Boolean - true = expand | false = collapse
	 * @memberOf $.fn.structureEditor
	 */
	function expcol( exp )
	{
		if (exp) var vs = ['collapsed','expanded'];
		else var vs = ['expanded','collapsed'];
		$('ul', $('.tree') ).each(function(){
			var ul = $(this);
			var li = $(ul.parent("li").get(0));
			var row = $(">.row", li);
			row.children('.bullet').each(function(){
			    $(this).removeClass(vs[0]);
				$(this).addClass(vs[1]);
				var li2 = $(this).parents("li").get(0);
				var ul2 = $(">ul", li2);
				if (exp) ul2.slideDown("fast");
				else ul2.slideUp("fast");
			});
		});		
	};
	/**
	 * Collapse all the nodes of the tree
	 * @memberOf $.fn.structureEditor
	 */
	function collapseAll(e)
	{
		$('#viewcontent').dynatree("getRoot").visit(function(node){
	          node.expand(false);
	    });
	};
	/**
	 * Expand all the nodes of the tree
	 * @memberOf $.fn.structureEditor
	 */
	function expandAll(e)
	{
		$('#viewcontent').dynatree("getRoot").visit(function(node){
	          node.expand(true);
	    });
	};
	/**
	 * 
	 * @memberOf $.fn.structureEditor
	 */
	function cacheon4all(e)
	{
		cache4all('on');
	};
	/**
	 * 
	 * @memberOf $.fn.structureEditor
	 */
	function cacheoff4all(e)
	{
		cache4all('off');
	};
	/**
	 * 
	 * @memberOf $.fn.structureEditor
	 */
	function emptyCacheAll(e)
	{
		emptyCache('all');
	};
	/**
	 * 
	 * @memberOf $.fn.structureEditor
	 */
	function emptyCachePage(e)
	{
		emptyCache('page');
	};
	/**
	 * 
	 * @memberOf $.fn.structureEditor
	 */
	function displayAction(myobject,timeWait,event) {
		var node 	= $.ui.dynatree.getNode(myobject);

		// GDE - 20/08/2013 - Bugfix: erreur js au survol - ne trouve pas le node
		if (typeof node !== 'undefined') {
			var dbid	= node.data.key;
			var e		= event;
			var epos = $(myobject).position();

			epos.top = Math.round(epos.top) - 3;
			epos.left = Math.round(epos.left);
			epos.width = $('#viewcontent').width();

			// position
			var ptop = epos.top;		// (e.pageY-5);
			var pleft = epos.width-400;	// (e.pageX + 20);

			clearTimeout(display_action_timer);
			display_action_timer = setTimeout(function(){
				timeWait--;
				if(timeWait > 0){
					displayAction(myobject,timeWait);
				}else{
					hideAction();
					showAction(dbid,ptop,pleft);
				}
			},(timeWait * 250));
		}
	};
	/**
	 * 
	 * @memberOf $.fn.structureEditor
	 */
	function hideAction() {
		$('.adminpages_action_container').hide();
	};
	
	/**
	 * 
	 * @memberOf $.fn.structureEditor
	 */
	function showAction(dbid,ptop,pleft) {
		$('#adminpageaction-'+dbid).css({'top':ptop+'px', 'left': pleft+'px'}).show().mouseleave(function (e) {
			hideAction();
		});
		var olf = $('#adminpageaction-'+dbid).offset();
		var bbl = '#adminpageaction-'+dbid+' .jquerybubblepopup';
		$(bbl).css({'top':( olf.top-$(bbl).height()-$(document).scrollTop()+10 )+'px','position':'fixed'});
	};	
	/**
	 * 
	 * @memberOf $.fn.structureEditor
	 */
	function activeAction() {
		$("#sidebarButtonSaveOrder").unbind('click');
		$('.deletenodea').unbind('click');			
		$('.publish').unbind('click');
		$('.unpublish').unbind('click');
                $('.duplicate').unbind('click');
		$('.statsnodea').unbind('click');
		
		$('#sidebarButtonSaveOrder').click(strSaveorder);
		$('.deletenodea').click(function(event){delnode(event, this);});			
		$('.publish').click(function(event){publishnode(event, this);});
		$('.unpublish').click(function(event){unpublishnode(event, this);});
                $('.duplicate').click(function(event){duplicatenode(event, this);});

		$('.statsnodea').click(function(event) {
			//$(this).attr('dbid')
			$('#adminpagestats-'+$(this).attr('href')).toggle();
			return false;
		});
	}
	/**
	 * 
	 * @memberOf $.fn.structureEditor
	 */
	function initAction() {
		// unbind events
		$(".dynatree-node").unbind('click');
		$(".dynatree-node").unbind('mouseover');
		$(".dynatree-node").unbind('mouseout');
	    // click on node
		$(".dynatree-node").click(function(e){
	    	hideAction();
	    	clearTimeout(display_action_timer);	    	
		});	    
	    // on mouseover node
		$(".dynatree-node").mouseover(function(e){
			hideAction();
			clearTimeout(display_action_timer);
                        displayAction(this,0,e);
		});	    
		// on mouseout node
	    $(".dynatree-node").mouseout(function(e){
	    	clearTimeout(display_action_timer);
		});
	};
	
	/**
	 * jQuery module for the structure editor management.
	 * Constructor Initialization of the sortable feature for the structure editor.
	 * @constructor
	 */
	$.fn.structureEditor = function()
	{
        $(document).tooltip({
            items: ".dynatree-container li",
            position: { my: "right bottom", at: "right top", collision: "flipfit" },
            content: function(){
                var txt = $('.tooltip-infos',$(this)).html()
                return txt;
            }
        });

		// init the top menu
		$('#padfiledrmenu').sydlistmenu(
				{
					'title':'Advanced options',
					'layout': 'linear',
					'items':[
					         {'label': 'Collapse all', 'action':collapseAll},
					         {'label': 'Expand all', 'action':expandAll},
					         {'label': 'Empty cache', 'action':emptyCachePage, 'separator':true},
					         {'label': 'Set cache ON for all pages', 'action':cacheon4all},
					         {'label': 'Set cache OFF for all pages', 'action':cacheoff4all},
					         {'label': 'Clear all application cache', 'action':emptyCacheAll}
					         ]
		});
		
		$('#adminsidebarsSearchIndex').click(function (event) {
			event.preventDefault();
			url = $(this).attr('href');
			
			$('#adminsidebarsLoading').show();
			$('#ajaxbox').msgbox( {'message': 'Building...', 'showtime':0,'modal':true} );
			$.getJSON(url,function(data) {
				$('#ajaxbox').msgbox(data.ResultSet);
				$('#adminsidebarsLoading').hide();
			});
		});
		
		if (!$(this).hasClass('recyclebin')) {
			activeAction();
			
			if (typeof(pagstructureid) == "undefined") {// this var is defined on edit page 
				$.ui.dynatree.nodedatadefaults["icon"] = false; // Turn off icons by default
				$('#viewcontent').dynatree({
					fx: {height: "toggle", duration: 200},
	    	        autoCollapse: false,
	    	        persist: true,
		    		checkbox: false,
		    	    selectMode: 3,
                    showRoot : true,
		    	    onActivate: function(node) {
		    	    	if( node.data.url && !node.data.noLink ) {
		    	    		node.deactivate();
		    	        	$('#modalBackground').show();
		    	        	window.document.location = node.data.url;		    	            
		    	    	}		    	    	
		    	    },		    	    
		    	    onExpand: function(isReloading, isError) {
		    	    	hideAction();
		    	    	initAction();
		    			return true;
		    		},
		    	    onSelect: function(select, node) {},
		    	    onClick: function(node, event) {},		    	    
		    	    onDblClick: function(node, event) {},
		    	    onKeydown: function(node, event) {
		    	        if( event.which == 32 ) {
		    	          node.toggleSelect();
		    	          return false;
		    	        }
		    	    },
		    		dnd: {
						onDragStart: function(node) {
							return true;
						},
						onDragStop: function(node) {
							$('#sidebarSaveOrCancel').fadeIn();													
						},
						autoExpandMS: 1000,
						preventVoidMoves: true, // Prevent dropping nodes 'before self', etc.
						onDragEnter: function(node, sourceNode) {
							return true;
						},
						onDragOver: function(node, sourceNode, hitMode) {},
						onDrop: function(node, sourceNode, hitMode, ui, draggable) {
							sourceNode.move(node, hitMode);
						},
						onDragLeave: function(node, sourceNode) {}
					},
					debugLevel: 0							
		    	});// END - $("#tree",maind).dynatree	
				initAction();
			}
		} else {
			$('.restorenodea').click(function(event){restorenode(event, this);});
			$('.deleterestorenodea').click(function(event){delnode(event, this);});
			$('#emptyRecyclebin').click(function(event){emptyRecyclebin(event, this);});
		}


	};

})(jQuery);




