<?php

class IndexController extends pm_Controller_Action
{
    public function init() {
        parent::init();
        // Init title for all actions
        $this->view->pageTitle = "Project Harvard";
        // Init tabs for all actions
        $this->view->tabs = array(
            array(
                'title' => pm_Locale::lmsg('blocked'),
                'action' => 'blocked'
            ),
            array(
                'title' => pm_Locale::lmsg('configuration'),
                'action' => 'configuration',
            )
        );
    }

    public function indexAction()
    {
        //echo Modules_Harvard_MailSettings::disableMailDomain('mailchannels.com');

        $this->_forward('blocked');
    }

    public function blockedAction() {

    }

    public function configurationAction() {
        $this->view->token = pm_Settings::get('authToken');
    }

    public function genkeyAction() {
        $key = Modules_Harvard_Helper::genRandHash();
        pm_Settings::set('authToken', $key);
        $this->_forward('configuration');
    }
}
