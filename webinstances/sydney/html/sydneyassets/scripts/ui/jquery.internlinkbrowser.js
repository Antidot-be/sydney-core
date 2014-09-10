jQuery.fn.internLinkBrowser = function(options) {

    var defaults = {
        label : '&nbsp;<a id="redirecttotarget" title="Redirect to a page or file" href="#">Choose a Page or File</a>',
        template : '<div id="internLinkBrowserDiv" title="Internal Link Browser">'
                 + '<ul id="nav"><li><a id="structure" href="#">Pages</a></li><li><a id="files" href="#">Files</a></li></ul>'
                 + '<div id="sectionbar" class="clearfix" style="padding:5px;width:100%;min-height:15px;">'
                 + '<p class="buttons">'
                 + '<a id="btnSave" class="button" href="save">Save</a>'
                 + '<a id="btnCancel" class="button muted" href="cancel">Cancel</a>'
                 + '</p></div>'
                 + '<div id="internLinkBrowserContent">Loading...</div>'
                 + '</div>',
        tree : '#contentBrowser'
    };

    var o = jQuery.extend(defaults, options);
    var $this = null;
    var $modal = null;
    var $div = null;
    var currentSelection = 'page';

    function bindEvents() {
        // bind events
        $('#structure').click(function(e){
            e.preventDefault();
            loadPages();
        });
        $('#files').click(function(e){
            e.preventDefault();
            loadFiles();
        });
        $('#btnSave').click(function(e){
            e.preventDefault();

            // which type of selection do we have?
            if (currentSelection === 'page') {
                var anchor = '';
                // for page, object selected?
                var liSelected = $('.liSelected');
                if (liSelected.length > 0) {
                    anchor = '#' + liSelected.attr('id');
                }
                var nodeSelected = $(o.tree).dynatree("getActiveNode");
                if (!nodeSelected) {
                    alert('Please select a page.');
                    return false;
                }

				// JTO - 19/02/2014 - On appel un service pour nous générer la bonne url
				$.ajax({
					dataType: "json",
					url: '/adminpages/services/getcleanurlpagebyidnode/',
					data: {id: nodeSelected.data.key},
					success: function(data){
						$this.val(data.url + anchor);
					}
				});

            } else {
                // for files
                var selected = $('.itemselected');
                if (selected.length > 1) {
                    alert('Please select only one file.');
                    return false;
                }
                $this.val('/FILE-' + selected.first().attr('href'));
            }

            $modal.dialog('close');
        });
        $('#btnCancel').click(function(e){
            e.preventDefault();
            $modal.dialog('close');
        });
    }

    function loadPages() {
        $div.html('Loading...');
        // open dialog box for choosing page/file to redirect to
        $.get('/adminpages/services/internlinkbrowser', function(html) {
            $div.html(html);
            currentSelection = 'page';
        });
    }

    function loadFiles() {
        $div.html('Loading...');
        $.get('/adminfiles/services/internlinkbrowser', function(html) {
            $div.html(html);
            currentSelection = 'file';
        });
    }

    function sanitizeString(value) {
        var reg = new RegExp(" <div.*$", "g");
        var rega = new RegExp("[àâä]", "gi");
        var rege = new RegExp("[éèê]", "gi");
        var regi = new RegExp("[îï]", "gi");
        var rego = new RegExp("[ôö]", "gi");
        var regu = new RegExp("[ùü]", "gi");
        var regspace = new RegExp(" ", "g");
        var regothers = new RegExp("[^a-z 0-9]", "gi");
        return value.replace(reg, '')
                    .replace(rega, 'a')
                    .replace(rege, 'e')
                    .replace(regi, 'i')
                    .replace(rego, 'o')
                    .replace(regu, 'u')
                    .replace(regothers, '')
                    .replace(regspace, '-');
    }

    return this.each(function(){
        // code
        $this = $(this);
        $this.after(o.label);
        $this.after(o.template);

        $modal = $('#internLinkBrowserDiv');
        $div = $('#internLinkBrowserContent');

        $('#redirecttotarget').click(function (evt) {
            evt.preventDefault();

            $modal.dialog({
                modal : true,
                height : 525,
                width : 950
            });
            bindEvents();
            loadPages();

            //var win = window.open("/adminpages/index/select/context/news/?embed=yes", "redirectto", "height=780, width=950");
            //'/adminfiles/index/index/?embed=yes';


        });

    });
};