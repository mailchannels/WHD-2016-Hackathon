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

        $mailSettings = new Modules_Harvard_MailSettings();
        $disabledDomainsCount = count($mailSettings->getDisabledDomains());
        $disabledMailboxesCount = count($mailSettings->getDisabledMailboxes());

        pm_Log::debug("disabledMailboxes: $disabledMailboxesCount, disabledDomains: $disabledDomainsCount");

        // Init title for all actions
        $this->view->pageTitle = "Project Harvard";

        // Init tabs for all actions
        $this->view->tabs = array(
            array(
                'title' => pm_Locale::lmsg('blocked-domains') . ($disabledDomainsCount ? " ($disabledDomainsCount)" : ''),
                'action' => 'blocked'
            ),
            array(
                'title' => pm_Locale::lmsg('blocked-mailboxes') . ($disabledMailboxesCount ? " ($disabledMailboxesCount)" : ''),
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
            $domain = $_POST['unblock'];
            $mailSettings->enableMailDomain($domain);

            $this->view->blockedDomains = array_filter($this->view->blockedDomains, function($i) {
                return $i['domain'] != $_POST['unblock'];
            });

            $this->_redirect('index/blocked');
        }

        $this->view->list = $this->getListDomains();
    }

    /**
     * List of blocked mailboxes.
     *
     * @return void
     */
    public function blockedmailboxesAction()
    {
        $mailSettings = new Modules_Harvard_MailSettings;

        if (isset($_POST['unblock'])) {
            $split = explode('@', $_POST['unblock'], 2);

            if (count($split) === 2) {
                $user = $split[0];
                $domain = $split[1];

                $mailSettings->enableMailUser($domain, $user);

                $this->view->blockedMailboxes = array_filter(
                    $this->view->blockedMailboxes,
                    function ($i) use ($user, $domain) {
                        return $i['domain_name'] !== $domain && $i['user'] !== $user;
                    }
                );
            }

            $this->_redirect('index/blocked_mailboxes');
        }

        $this->view->list = $this->getListMailboxes();
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

    public function getListMailboxes()
    {
        $mailSettings = new Modules_Harvard_MailSettings;
        $disabledMailboxes = $mailSettings->getDisabledMailboxes();

        $data = array_map(
            function($e) {
                return array(
                    'mail' => '<a href="/smb/email-address/edit/id/' . $e['user_id'] . '/domainId/' . $e['domain_id'] . '">'
                        . $e['user'] . '@' . $e['domain_name']
                        . '</a>',
                    'reason' => '',
                    'user' => $e['user'],
                    'domain' => $e['domain_name'],
                );
            },
            $disabledMailboxes
        );

        $list = new pm_View_List_Simple($this->view, $this->_request);
        $list->setData($data);
        $list->setColumns(array(
            pm_View_List_Simple::COLUMN_SELECTION,

            'mail' => array(
                'title' => 'Mail account',
                'noEscape' => true,
                'searchable' => true,
            ),
            'reason' => array(
                'title' => 'Reason',
                'searchable' => true,
                'sortable' => false,
            ),
        ));
        $list->setTools(array(
            array(
                'title' => $this->lmsg('enable-mailbox-button'),
                'class' => 'sb-app-info',
                'link' => '',
                'execGroupOperation' => 'enable-mailboxes',
            ),
        ));

        // Take into account listDataAction corresponds to the URL /list-data/
        $list->setDataUrl(array('action' => 'list-mailboxes'));

        return $list;
    }

    public function getListDomains()
    {
        $mailSettings = new Modules_Harvard_MailSettings;
        $disabledDomains = $mailSettings->getDisabledDomains();

        $data = array_map(
            function($e) {
                return array(
                    'mail' => '<a href="/smb/mail-settings/edit/id/' . $e['id'] . '/domainId/' . $e['id'] . '">'
                        . $e['domain']
                        . '</a>',
                    'reason' => $e['reason'],
                    'domain' => $e['domain'],
                );
            },
            $disabledDomains
        );

        $list = new pm_View_List_Simple($this->view, $this->_request);
        $list->setData($data);
        $list->setColumns(
            array(
                pm_View_List_Simple::COLUMN_SELECTION,

                'mail' => array(
                    'title' => 'Domain',
                    'noEscape' => true,
                    'searchable' => true,
                ),
                'reason' => array(
                    'title' => 'Reason',
                    'searchable' => true,
                    'sortable' => false,
                ),
            )
        );
        $list->setTools(
            array(
                array(
                    'title' => $this->lmsg('enable-domain-button'),
                    'class' => 'sb-app-info',
                    'link' => '',
                    'execGroupOperation' => 'enable-domains',
                ),
            )
        );

        // Take into account listDataAction corresponds to the URL /list-data/
        $list->setDataUrl(array('action' => 'list-domains'));

        return $list;
    }

    public function enableMailboxesAction()
    {
        $ids = $this->_getParam('ids');

        if (!$ids)
        {
            return null;
        }

        $list = $this->getListMailboxes();
        $data = $list->fetchData()['data'];

        $messages = array();

        $mailSettings = new Modules_Harvard_MailSettings;

        foreach ($ids as $id)
        {
            $id = (int) $id;
            $mailSettings->enableMailUser($data[$id]['domain'], $data[$id]['user']);
            $address = $data[$id]['user'] . '@' . $data[$id]['domain'];
            $messages[] = ['status' => 'info', 'content' => "Mailbox $address was successfully enabled."];
        }

        $this->_helper->json(['status' => 'success', 'statusMessages' => $messages]);
    }

    public function enableDomainsAction()
    {
        $ids = $this->_getParam('ids');

        if (!$ids)
        {
            return null;
        }

        $list = $this->getListDomains();
        $data = $list->fetchData()['data'];

        $messages = array();

        $mailSettings = new Modules_Harvard_MailSettings;

        foreach ($ids as $id)
        {
            $domain = $data[(int) $id]['domain'];
            $mailSettings->enableMailDomain($domain);
            $messages[] = ['status' => 'info', 'content' => "Domain $domain was successfully enabled."];
        }

        $this->_helper->json(['status' => 'success', 'statusMessages' => $messages]);
    }

    /**
     * Handles list actions (ordering, searching,...) for mailboxes.
     *
     * @return void
     */
    public function listMailboxesAction()
    {
        $list = $this->getListMailboxes();
        $this->_helper->json($list->fetchData());
    }

    /**
     * Handles list actions (ordering, searching,...) for domains.
     *
     * @return void
     */
    public function listDomainsAction()
    {
        $list = $this->getListDomains();
        $this->_helper->json($list->fetchData());
    }
}
