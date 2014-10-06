
# Create your own content type #

In order to add your own content type you need some knowledge in PHP.
The 2 examples are available in the webinstance example.

## A basic example ##

In this example we will create a basic content type : goto top  
A goto top helper could be usefull if you have page with a lot of content (and thus long scrollbar)

### Register our helpers ###

For each content type you want to add, it is needed to register 3 view helpers (one for public view, one for admin view and one for the editor) in your `/webinstances/acme/html/index.php` before running the app. It is also needed to create a javascript file for the admin.  

Like :

    // ...
    $app->registerContentTypeHelper('goto-top-block', 'Goto top', 'publicGotoTopView', 'privateGotoTopView', 'editorGotoTopView');
    $app->run();

`goto-top-block` : is the unique identifier for the content type you have created  
`Goto top` : is the human readable label for the admin  
`publicGotoTopView` : is the method that will be call in the public context (like a visitor)  
`privateGotoTopView` : method call in the private context (when you are logged as an admin) it is just an admin preview, it will also add automatically some buttons like : re-order, edit, delete, duplicate, ...  
`editorGotoTopView` : method call to render the editor, here the editor is not really revelant but in the next part, you will see that it can be more complex  


### Create the helpers ###

Now we know that we need 3 helpers and thus 3 classes. You can put those 3 classes in `/webinstances/acme/library/helpers/` (create the directory if it do not exist).  
Note that all of those classes must begin with `Helper_`.

Let's begin with the public view.  
First create a file `PublicGotoTopView.php` in `/webinstances/acme/library/helpers/` :

    <?php

    class Helper_PublicGotoTopView extends Zend_View_Helper_Abstract
    {
        public function publicGotoTopView()
        {
            return '<a href="#">Top</a>';
        }
    }


This file is quite simple. There is no need of additional things to show the public view.  

Now, let's create the private view `PrivateGotoTopView.php` in `/webinstances/acme/library/helpers/` :

    <?php

    class Helper_PrivateGotoTopView extends Zend_View_Helper_Abstract
    {
        public function privateGotoTopView($actionsHtml = '', $content = '', $dbId = 0, $order = 0, $params = array(), $moduleName = 'adminpages', $pagstructureId = 0, $sharedInIds = '')
        {
            return '<li class="' . $params['addClass'] . ' sydney_editor_li"
                            data-content-type="goto-top-block"
                            dbid="' . $dbId . '"
                            dborder="' . $order . '"
                            pagstructureid="' . $pagstructureId . '">
                ' . $actionsHtml . '
                <div class="content">
                    Goto top preview!
                </div>
            </li>';
        }
    }


Note that the `LI` tag is required for the preview like all the attributes.  
The `data-content-type` must match with the identifier you have defined before.

And finally the editor view `EditorGotoTopView.php` in `/webinstances/acme/library/helpers/` :  

    <?php

    class Helper_EditorGotoTopView extends Zend_View_Helper_Abstract
    {
        public function EditorGotoTopView()
        {
            $this->view->headScript()->appendFile('/assets/js/admin/ceEditors.goto-top.js');
            return '
                <div class="editor goto-top-block" data-content-type="goto-top-block">
                    <p class="sydney_editor_p">
                        Go to top helper!
                    </p>

                    <p class="buttons sydney_editor_p">
                        <a class="button sydney_editor_a" href="save">Save as actual content</a>
                        <a class="button sydney_editor_a" href="save-draft">Save as draft</a>
                        <a class="button muted sydney_editor_a" href="cancel">Cancel</a>
                    </p>
                </div>';
        }
    }

Each content type needs its own javascript editor file here : `/assets/js/admin/ceEditors.goto-top.js`

    if (!ceEditors) var ceEditors = {};
    /**
     * @constructor
     */
    ceEditors['goto-top-block'] = {

        setupEditor : function(){
            ceEditors.defaultedt.setupEditor.apply(this);
        },

        save : function(){
            ceEditors.defaultedt.save.apply(this);
            var item = $(this),
                dbid = item.attr('dbid')? item.attr('dbid') : 0,
                dborder = item.attr('dborder')? item.attr('dborder') : 0,
                editor = $(".editor", item);
            item.data("new", false);
            item.removeEditor();

            // post the data to the JSON service
            $.postJSON('/adminpages/services/savediv/format/json/emodule/'+emodule, {
                    'id': dbid,
                    'order': dborder,
                    'content': '',
                    'params': '',
                    'content_type_label': $(this).data('content-type'),
                    'status' : status,
                    'pagstructureid' : pagstructureid
                },
                function(data) {
                    ceEditors.defaultedt.saveorder(item, data);
                    // update the div content
                    $.get("/adminpages/services/getdivwitheditor/", {'dbid': data.ResultSet.dbid}, function(data){
                        item.replaceWith(data);
                        $("li[dbid="+item.attr('dbid')+"]").makeEditable();
                    });
                }
            );
        }
    };

This file will basically setup and save the content type by sending all the infos to `/adminpages/services/savediv/format/json/emodule/` service.  


And you are done!  
You can now see the `Goto top` label when creating or editing a page!  


## An advanced example : carousel content type ##

The way to add this content type is exactly the same as before except that we will work with data from the DB.
Keep in mind that the example is based on the bootstrap carousel but you can adapt this for other carousel.

### Register the new helpers ###


    // ...
    $app->registerContentTypeHelper('goto-top-block', 'Goto top', 'publicGotoTopView', 'privateGotoTopView', 'editorGotoTopView');
    $app->registerContentTypeHelper('carousel-block', 'Carousel', 'publicCarouselView', 'privateCarouselView', 'editorCarouselView');
    $app->run();


### Create the helpers ###


First the public part, again this view helper is for bootstrap, you can adapt it for any kind of carousel :

    <?php

    class Helper_PublicCarouselView extends Zend_View_Helper_Abstract
    {

        public function publicCarouselView($actionsHtml = '', $content = '', $dbId = 0, $order = 0, $params = array(), $pagstructureId = 0)
        {
            $picturedIds = explode(',', $content);

            $filesObject = new Filfiles();
            $files = $filesObject->getFileInfosByIdList($picturedIds); // Retrieve all fields save in DB

            $imgList = $carouselIndicator = array();
            /* We iterate throught all the files selected */
            foreach($files as $key => $file){
                $activeClass = ($key == 0)? ' active' : '';
                $carouselIndicator[] = '<li data-target="#carousel-example-generic" data-slide-to="'.$key.'"></li>';
                $imgList[] = '
                    <div class="item '.$activeClass.'">
                        <img src="/publicms/file/showimg/id/' . $file['id'] . '/dw/1140" alt="" />
                        <div class="carousel-caption"></div>
                    </div>';
            }

            $htmlIndicatorList = implode("\n", $carouselIndicator);
            $htmlImgList = implode("\n", $imgList);

            return '
                <div id="carousel-example-generic" class="carousel slide" data-ride="carousel">
                  <!-- Indicators -->
                  <ol class="carousel-indicators">' . $htmlIndicatorList . '</ol>
                  <div class="carousel-inner">' . $htmlImgList . '</div>

                  <!-- Controls -->
                  <a class="left carousel-control" href="#carousel-example-generic" role="button" data-slide="prev">
                    <span class="glyphicon glyphicon-chevron-left"></span>
                  </a>
                  <a class="right carousel-control" href="#carousel-example-generic" role="button" data-slide="next">
                    <span class="glyphicon glyphicon-chevron-right"></span>
                  </a>
                </div>
            ';
        }
    }

The private part is very simple, we will just show selected images (you can customize it as you want) :

    <?php

    class Helper_PrivateCarouselView extends Zend_View_Helper_Abstract
    {
        public function privateCarouselView($actionsHtml = '', $content = '', $dbId = 0, $order = 0, $params = array(), $moduleName = 'adminpages', $pagstructureId = 0, $sharedInIds = '')
        {
            $picturedIds = explode(',', $content);

            $filesObject = new Filfiles();
            $files = $filesObject->getFileInfosByIdList($picturedIds);

            $imgList = array();
            /* We iterate throught all the files selected */
            foreach($files as $key => $file){
                $imgList[] = '<img data-file-id="' . $file['id'] . '" class="preview-image" src="/publicms/file/showimg/id/' . $file['id'] . '/dw/1140" alt="" style="width:200px" />';
            }

            $htmlImgList = implode("\n", $imgList);

            return '<li class="' . $params['addClass'] . ' sydney_editor_li"
                            data-content-type="carousel-block"
                            dbid="' . $dbId . '"
                            dborder="' . $order . '"
                            pagstructureid="' . $pagstructureId . '">
                ' . $actionsHtml . '
                <div class="content">
                    '.$htmlImgList.'
                </div>
            </li>';
        }
    }

Nothing particular here we just retrieve selected files.

And now the Editor :

    <?php

    class Helper_EditorCarouselView extends Zend_View_Helper_Abstract
    {
        public function EditorCarouselView()
        {
            $this->view->headScript()->appendFile('/assets/js/admin/ceEditors.carousel.js');
            return '
                <div class="editor files edefiles carousel-block" data-content-type="carousel-block">
                    <p class="buttons sydney_editor_p">
                        <a class="button sydney_editor_a" href="save">Save as actual content</a>
                        <a class="button sydney_editor_a" href="save-draft">Save as draft</a>
                        <a class="button muted sydney_editor_a" href="cancel">Cancel</a>
                    </p>
                </div>';
        }
    }

The JS file located `in webinstances/acme/html/assets/js/ceEditors.carousel.js`:

    if (!ceEditors) var ceEditors = {};
    /**
     * @constructor
     */
    ceEditors['carousel-block'] = {
        /**
         * Method: save
         */
        save : function(e){
            ceEditors.defaultedt.save.apply(this);

            var item = $(this),
                dbid = item.attr('dbid') || 0,
                dborder = item.attr('dborder') || 0,
                editor = $(".editor", item),
                elementsIds = [];

            if( $("#folders-categories").length == 0 ) {
                $('.itemselected', editor).each(function(){
                    elementsIds.push( $(this).attr('href') );
                });
            }
            item.data('new', false);
            item.removeEditor();

            $.postJSON('/adminpages/services/savediv/format/json/emodule/'+emodule, {
                    'id': dbid,
                    'order': dborder,
                    'content': elementsIds.toString(),
                    'params': '',
                    'content_type_label': $(this).data('content-type'),
                    'status' : status,
                    'pagstructureid' : pagstructureid
                },
                function(data) {
                    ceEditors.defaultedt.saveorder( item, data);
                    $.get("/adminpages/services/getdivwitheditor/", {'dbid': data.ResultSet.dbid}, function(data){
                        item.replaceWith(data);
                        $("li[dbid="+item.attr('dbid')+"]").makeEditable();
                    });
                }
            );
        },
        /**
         * Method: setupEditor
         */
        setupEditor : function(){
            ceEditors.defaultedt.setupEditor.apply(this);
            var item = $(this),
                previousElements = $('.preview-image'),
                previousIds = [];

            previousElements.each(function(){
                previousIds.push($(this).data('file-id'));
            });
            $(".editor", item).load(
                "/adminfiles/index/index/",{
                    'embed':'yes',
                    'context': 'pageeditor',
                    'filter' : 1,
                    'mode' : 'thumb',
                    'selected_files': previousIds
                }, function(e) {
                    $('.buttons .button').click(function(e){
                        e.preventDefault();
                        var action = $(this).attr('href');

                        if (action == "save") {
                            status 	= 'published';
                        } else if (action == "save-draft") {
                            status 	= 'draft';
                            action 	= "save";
                        }
                        item[action]();
                    });
                }
            );

        }
    };

There are two functions here `save` and `setupEditor` which are both automatically called.
