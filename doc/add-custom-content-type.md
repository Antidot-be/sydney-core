
# Create your own content type #

To be able to add your own content type you will need some knowledge in PHP.

## A basic example ##

In this example we will create a basic content type : goto top  
A goto top helper could be usefull if you have page with a lot of content (and thus long scrollbar)

### Register our helpers ###

For each content type you will add, you will need to register 3 view helpers (one for public view, one for admin view and one for the editor) in your `/webinstances/acme/html/index.php` before run the app and also a javascript file for the admin.  

Like :

    // ...
    $app->registerContentTypeHelper('goto-top-block', 'Goto top', 'publicGotoTopView', 'privateGotoTopView', 'editorGotoTopView');
    $app->run();

`goto-top-block` : is an unique identifier for to content type  
`Goto top` : is the human readable label for the admin  
`publicGotoTopView` : is the method that will be call in the public context (like a visitor)  
`privateGotoTopView` : method call in the private context (when you are logged as an admin) it is just a admin preview, it will also add automatically some buttons like : re-order, edit, delete, duplicate, ...  
`editorGotoTopView` : method call to render the editor, here the editor is not really revelant but in the next you will see that it can be more complex  


### Create the helpers ###

Now we know that we need 3 helpers and thus 3 classes. You can put those 3 classes in `/webinstances/acme/library/helpers/` (create the directory if not exist).  
Note that all of those class must begin with `Helper_`.

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


This file is quite simple but you don't need more thing in the public view to show.  

Now let's create the private view `PrivateGotoTopView.php` in `/webinstances/acme/library/helpers/` :

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
The `data-content-type` must match with identifier you have defined before.

And finally the editor view `EditorGotoTopView.php` in `/webinstances/acme/library/helpers/` :  

    <?php

    class Helper_EditorGotoTopView extends Zend_View_Helper_Abstract
    {
        public function EditorGotoTopView()
        {
            $this->view->headScript()->appendFile('/assets/js/admin/ceEditor.goto-top.js');
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

Each content type will need its own javascript editor file here `/assets/js/admin/ceEditor.goto-top.js`  

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
After that you see the `Goto top` label when creating or editing a page!  
