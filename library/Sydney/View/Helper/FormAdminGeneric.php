<?php
require_once('Zend/View/Helper/Abstract.php');

class Sydney_View_Helper_FormAdminGeneric extends Zend_View_Helper_Abstract
{
    /**
     * Returns the HTML for creating a form powered by JavaScript (or straight one).
     * The CSS, JS and other is included.
     *
     * @param Zend_Form $form The form object
     * @param Boolean $jsStayOnPage Should we post as a service and stay on the same page
     * @param String $srvurl The URL to post the data to or NONE will build the URL automatically
     */
    public function FormAdminGeneric(Zend_Form $form, $jsStayOnPage = true, $srvurl = '')
    {

        $formname = $form->getName();
        if ($srvurl == '') {
            $srvurl = '/' . $this->view->moduleName . '/services/edit' . $formname . '/format/json';
        }
        $form->setAction($srvurl);

        $html = '';
        if ($jsStayOnPage) {
            $html .= '
		<script>
		$(function(){
			$(\'#' . $formname . '\').formvalidator({\'url\':\'' . $srvurl . '\'});
		});
		</script>
		';
        }
        $html .= '
		<div class="box">
			<fieldset>
				' . $form . '
			</fieldset>
		</div>
		';

        return $html;
    }
}
