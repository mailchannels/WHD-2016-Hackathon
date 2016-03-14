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
       // pm_Settings::set('disabled_mail_boxes', '{}');
        parent::init();

        // Init title for all actions
        $this->view->pageTitle = "Project Harvard";

        // Init tabs for all actions
        $this->view->tabs = array(
            array(
                'title' => pm_Locale::lmsg('blocked-domains'),
                'action' => 'blocked'
            ),
            array(
                'title' => pm_Locale::lmsg('blocked-mailboxes'),
                'action' => 'blocked_mailboxes'
            ),
            array(
                'title' => pm_Locale::lmsg('configuration'),
                'action' => 'configuration',
            ),
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
        $mailSettings = new Modules_Harvard_MailSettings();
        $this->view->blockedDomains = $mailSettings->getDisabledDomains();
        if (isset($_POST['unblock']))
        {
            $mailSettings->enableMailDomain($_POST['unblock']);

            $this->view->blockedDomains = array_filter($this->view-blockedDomains, function($i) {
                $i['domain'] != $_POST['unblock'];
            });
        }

    }

    /**
     * List of blocked mailboxes.
     *
     * @return void
     */
    public function blockedmailboxesAction()
    {
        $mailSettings = new Modules_Harvard_MailSettings;
        $this->view->blockedMailboxes = $mailSettings->getDisabledMailboxes();

        if (isset($_POST['unblock']))
        {
            $split = explode('@', $_POST['unblock'], 2);

            if (count($split) === 2)
            {
                $user = $split[0];
                $domain = $split[1];

                $mailSettings->enableMailUser($domain, $user);

                $this->view->blockedMailboxes = array_filter(
                    $this->view->blockedMailboxes,
                    function($i) use($user, $domain) {
                        return $i['domain_name'] !== $domain && $i['user'] !== $user;
                    }
                );
            }
        }
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

        if (isset($_POST['remove-event'])) {
            $config->removeAction($_POST['remove-event']);
        }
        elseif (isset($_POST['event']) && isset($_POST['action']))
        {
            $config->addAction($_POST['event'], $_POST['action']);
        }

        $this->_redirect('index/configuration');
    }
}
