(function(a){a.fn.imageeditor=function(q){var i={ajaxboxid:"#ajaxbox",toolbar:".toolbar",stage:".stage"};var b={};a.extend(b,q,i);var k=a(this);var l=a(b.toolbar,k);var j=a(b.stage,k);var f=b.filfiles_id;var h=YAHOO.util.Dom;var n=YAHOO.util.Event;var e=null;var g=null;var o=null;function d(){var r=new YAHOO.widget.ImageCropper("yui_img",{initialXY:[20,20],keyTick:5,shiftKeyTick:50});a(".yui-crop-resize-mask",j).dblclick(function(){var s=r.getCropCoords();s.id=f;s.imgwidth=a("#yui_img").width();s.imgheight=a("#yui_img").height();delete s.image;a(".crop",l).removeClass("iconhover");c("cropimage",s);r.destroy()})}function m(){a(".crop",l).click(function(){a(".crop",l).addClass("iconhover");d()});a(".rotatel",l).click(function(){c("rotate",{id:f,val:"-90"})});a(".rotater",l).click(function(){c("rotate",{id:f,val:"90"})});a(".revert",l).click(function(){c("revert",{id:f})});a(".fliph",l).click(function(){c("flip",{id:f,val:"h"})});a(".flipv",l).click(function(){c("flip",{id:f,val:"v"})});a(".reflection",l).click(function(){c("reflection",{id:f})});a(".contrast",l).click(function(){c("contrast",{id:f})});a(".sharpen",l).click(function(){c("sharpen",{id:f})});a(".blacknwhite",l).click(function(){c("blacknwhite",{id:f})});a(".zoomin",l).click(function(){c("scale",{id:f,val:"10"})});a(".zoomout",l).click(function(){c("scale",{id:f,val:"-10"})});var r=k.offset();l.css({top:(r.top+40)+"px",left:(r.left+5)+"px",}).draggable({handle:".movebar"})}function c(r,t){if(o==null){o="/adminimageeditor/services/showimg/id/"+f+"/"}else{if(r!=undefined){o="/adminimageeditor/services/"+r}}if(t!=undefined){for(var s in t){o+="/"+s+"/"+t[s]}}o+="/tms/"+Number(new Date())+"/";j.html('<img src="'+o+'" id="yui_img">')}function p(){m();c();a(".savebutton",k).click(function(){a.getJSON("/adminimageeditor/services/saveimage/format/json/id/"+f,function(r){alert(r.message);window.location="/adminfiles/index/index/"})})}p()}})(jQuery);