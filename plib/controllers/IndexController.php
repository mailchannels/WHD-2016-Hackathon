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
        $this->view->token = pm_Settings::get('authToken');
        $this->view->actionConfig = new Modules_Harvard_ActionConfig();
    }

    public function genkeyAction() {
        $key = Modules_Harvard_Helper::genRandHash();
        pm_Settings::set('authToken', $key);
        $this->_forward('configuration');
    }

    public function addactionAction() {

        $config = new Modules_Harvard_ActionConfig;
        if (isset($_POST['event']) && isset($_POST['action'])) {
            $config->addAction($_POST['event'], $_POST['action']);
        }

        $this->_forward('configuration');
    }
}
