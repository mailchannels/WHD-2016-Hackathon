<?php

/**
 * Main controller class for routing URLs.
 *
 * @since  1.0
 */
class IndexController extends pm_Controller_Action
{
    /**
     * Initialization common for all views.
     *
     * @return void
     */
    public function init()
    {
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

    /**
     * Index URL, redirects to list of blocked sites.
     *
     * @return void
     */
    public function indexAction()
    {
        $this->_forward('blocked');
    }

    /**
     * List of blocked sites.
     *
     * @return void
     */
    public function blockedAction()
    {
    }

    /**
     * Configuration page.
     *
     * @return void
     */
    public function configurationAction()
    {
        $this->view->token = pm_Settings::get('authToken');
        $actionConfig = new Modules_Harvard_ActionConfig;
        $this->view->actionConfig = $actionConfig;
        $this->view->availableEvents = $actionConfig->getAvailableEvents();
        $this->view->availableActions = $actionConfig->getAvailableActions();
    }

    /**
     * Generates a new API key.
     *
     * @return void
     */
    public function genkeyAction()
    {
        $key = Modules_Harvard_Helper::genRandHash();
        pm_Settings::set('authToken', $key);
        $this->_redirect('index/configuration');
    }

    /**
     * Adds a new event/action pair to the configuration.
     *
     * @return void
     */
    public function addactionAction()
    {
        $config = new Modules_Harvard_ActionConfig;

        if (isset($_POST['event']) && isset($_POST['action']))
        {
            $config->addAction($_POST['event'], $_POST['action']);
        }

        $this->_redirect('index/configuration');
    }
}
