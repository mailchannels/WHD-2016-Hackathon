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
        $this->_forward('blocked');
    }

    public function blockedAction() {
    }

    public function configurationAction() {
    }
}
