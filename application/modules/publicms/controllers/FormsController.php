<?php
/**
 * Controller Publicms Index
 */

/**
 * This will display the content from the CMS for the public part of the website
 *
 * @package Publicms
 * @subpackage Controller
 * @author Arnaud Selvais
 * @since
 * @copyright Antidot Inc. / S.A.
 */
class Publicms_FormsController extends Sydney_Controller_Actionpublic
{
    /**
     * Init of the helpers for this controller. We are calling the parent init() first
     * @return
     */
    public function init()
    {
        parent::init();
        $this->loadInstanceViewHelpers();
    }

    /**
     * Redirects to the right action
     */
    public function indexAction()
    {
    }

    /**
     * Standard email form for all webinstances
     * @since 23 aug 2013
     */
    public function emailAction()
    {
        $r = $this->getRequest();
        $form = new PubcontactFormOp(null, $r->getParams());
        $form->setMethod('post');
        $purl = $this->_buildUrlFromParams($r->getParams());
        if ($purl === '') {
            $purl = '/publicms/forms/email/';
        }
        $currentLangCode = $this->getCurrentLangCode();
        $purl .= (!empty($currentLangCode)) ? '?slang=' . $currentLangCode : '';
        $purl .= '#pubcontact';
        $form->setAction($purl);

        if ($r->isPost()) {
            $p = $this->_flattenForemailForm($r->getParams());
            $form->populate($p);

            if ($form->isValid($p)) {
                $db = new Pubcontact();
                $dbRow = $db->createRow();
                // $dbRow->id = '';
                $dbRow->safinstances_id = Sydney_Tools_Sydneyglobals::getSafinstancesId();
                $dbRow->datetime = date('Y-m-d H:i:s');
                // $dbRow->timestamp = '';
                if (isset($p['fname'])) {
                    $dbRow->fname = $p['fname'];
                }
                if (isset($p['lname'])) {
                    $dbRow->lname = $p['lname'];
                }
                if (isset($p['fullname'])) {
                    $dbRow->fullname = $p['fullname'];
                }
                if (isset($p['email'])) {
                    $dbRow->email = $p['email'];
                }
                if (isset($p['phonenr'])) {
                    $dbRow->phonenr = $p['phonenr'];
                }
                if (isset($p['subject'])) {
                    $dbRow->subject = $p['subject'];
                }
                if (isset($p['message'])) {
                    $dbRow->message = $p['message'];
                }
                if (isset($p['subsnewsletter'])) {
                    $dbRow->subsnewsletter = $p['subsnewsletter'];
                }

                // upload the file if any
                $dbRow->filfiles_id = 0;
                if (isset($p['MAX_FILE_SIZE'])) {
                    if ($form->uploadfile->receive()) {
                        $filefilesDb = new FilfilesOp();
                        $fileData = $filefilesDb->fileToFileManager(
                            $form->uploadfile->getFileName(),
                            date('d/m/Y H:i:s') . ' file uploaded from FORMMAIL - Author : ' . $dbRow->fname . ' ' . $dbRow->lname . ' ' . $dbRow->fullname,
                            'Formmail'
                        );
                        $dbRow->filfiles_id = $fileData;
                    } else {
                        Zend_Debug::dump('ERROR: The file could not be uploaded...');
                    }
                }

                $entId = $dbRow->save();
                if ($entId !== false) {
                    $this->view->confirmSent = true;
                    $this->view->form = Sydney_Tools_Localization::_('Thanks, your message has been sent.');
                    // send the mail
                    if ($this->_sendEmailForms($p['emails'], $dbRow)) {
                        Sydney_Db_Trace::add('trace.event.emailsent_emailform' . ' [' . $dbRow->email . ']', 'publicms', 'pubcontact', 'email', $entId);
                    } else {
                        Sydney_Db_Trace::add('trace.event.emailerror_emailform' . ' [' . $dbRow->email . ']', 'publicms', 'pubcontact', 'email', $entId);
                    }
                } else {
                    $this->view->form = $form;
                }
            } else {
                $this->view->form = $form;
            }
        } else {
            $this->view->form = $form;
        }
    }

    /**
     * Send the email
     *
     * @param string $emails List of recepients separated by a space
     * @param $dbRow
     * @return Zend_Mail | false
     */
    protected function _sendEmailForms($emails = '', $dbRow)
    {
        $mail = new Zend_Mail();
        // define the recipients
        $eValidator = new Zend_Validate_EmailAddress();
        $sendTheMail = false;
        if (trim($emails) == '') {
            $email = Sydney_Tools_Sydneyglobals::getConf('general')->siteEmail;
            if ($eValidator->isValid($email)) {
                $mail->addTo($email, Sydney_Tools_Sydneyglobals::getConf('general')->siteTitle . ' website');
                $sendTheMail = true;
            }
        } else {
            foreach (preg_split('/,/', $emails) as $email) {
                if ($eValidator->isValid(trim($email))) {
                    $mail->addTo($email, Sydney_Tools_Sydneyglobals::getConf('general')->siteTitle . ' website');
                    $sendTheMail = true;
                }
            }
        }
        if ($sendTheMail) {
            $mail->setBodyText('This email is in HTML format');
            $msg = '<br/><br/>You have received an email from the "email form" available on ' . Sydney_Tools_Sydneyglobals::getConf('general')->siteTitle . ' :<br/><br/><br/>';
            if ($dbRow->fname != '') {
                $msg .= '<b>First Name</b> : ' . $dbRow->fname . '<br/>';
            }
            if ($dbRow->lname != '') {
                $msg .= '<b>Last Name</b> : ' . $dbRow->lname . '<br/>';
            }
            if ($dbRow->fullname != '') {
                $msg .= '<b>Full name</b> : ' . $dbRow->fullname . '<br/>';
            }
            if ($dbRow->email != '') {
                $msg .= '<b>Email</b> : ' . $dbRow->email . '<br/>';
            }
            if ($dbRow->phonenr != '') {
                $msg .= '<b>Phone Number</b> : ' . $dbRow->phonenr . '<br/>';
            }
            if ($dbRow->subject != '') {
                $msg .= '<b>Subject</b> : ' . $dbRow->subject . '<br/>';
            }
            if ($dbRow->message != '') {
                $msg .= '<b>Message</b> :<br/> ' . nl2br($dbRow->message) . '<br/>';
            }
            $msg .= '<br/><br/>';
            $mail->setBodyHtml($msg);
            $mail->setFrom($dbRow->email, $dbRow->fname . ' ' . $dbRow->lname . ' ' . $dbRow->fullname);
            $mail->setSubject('Email form - ' . $dbRow->subject);

            return $mail->send();
        } else {
            return false;
        }
    }

    /**
     * Flattent this array as I had the bad idea of using the same form varnames for
     * the config params. This is used when we want to populate the request to the form
     * ie we can have something like that :
     *  ["fullname"]=> array(3) { [0]=> bool(true) [1]=> bool(true) [2]=> string(3) "John Connor" }
     * In this case we take the third value (John Connor)
     * @param $a
     * @return array
     */
    protected function _flattenForemailForm($a)
    {
        $toret = array();
        foreach ($a as $k => $v) {
            if (count($v) == 3) {
                $toret[$k] = $v[2];
            } elseif (count($v) == 2) {
                $toret[$k] = false;
            } else {
                $toret[$k] = $v;
            }
        }

        return $toret;
    }

}
