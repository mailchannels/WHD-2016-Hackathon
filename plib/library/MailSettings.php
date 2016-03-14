<?php

/**
 * Created by PhpStorm.
 * User: mike
 * Date: 2016-03-13
 * Time: 1:47 PM
 */

const enabled = 'enable';
const disable = 'disable';

const disabled_mail_domains = 'disabled_mail_domains';

class Modules_Harvard_MailSettings
{
    /**
     * Disables the mailbox for a certain user.
     *
     * @param   string  $domain  The domain name the mail account belongs to.
     * @param   string  $user    The user name to block.
     *
     * @return void
     */
    public function disableMailUser($domain, $user)
    {
        $this->setMailUserStatus($domain, $user, false);
    }

    /**
     * Enables the mailbox for a certain user.
     *
     * @param   string  $domain  The domain name the mail account belongs to.
     * @param   string  $user    The user name to unblock.
     *
     * @return void
     */
    public function enableMailUser($domain, $user)
    {
        $this->setMailUserStatus($domain, $user, true);
    }

    /**
     * Sets the mailbox status for a certain user.
     *
     * @param   string  $domain   The domain name the mail account belongs to.
     * @param   string  $user     The user name.
     * @param   bool    $enabled  Whether the mailbox should be enabled or disabled.
     *
     * @return  bool  True on success, false on failure.
     */
    private function setMailUserStatus($domain, $user, $enabled = false)
    {
        $site_id = $this->getIdFromDomain($domain);

        $setEnabled = $enabled ? 'true' : 'false';

        $request = <<<APICALL
            <mail>
            <update>
               <set>
                  <filter>
                      <site-id>$site_id</site-id>
                      <mailname>
                          <name>$user</name>
                          <mailbox>
                              <enabled>$setEnabled</enabled>
                          </mailbox>
                      </mailname>
                  </filter>
               </set>
            </update>
            </mail>
APICALL;
        $response = pm_ApiRpc::getService()->call($request);
        $status = $response->mail->update->set->result->status == "ok";

        if ($status)
        {
            pm_Log::debug("$user@$domain's mailbox status was set to $setEnabled");

            if (!$enabled)
            {
                // TODO: $this->recordDisabledMailbox($domain, $user);
            }

            return true;
        }
        else
        {
            $trueName = $this->getMailFromAlias($site_id, $user);

            if ($trueName !== false)
            {
                return $this->setMailUserStatus($domain, $trueName, $enabled);
            }

            pm_Log::err("$user@$domain's mailbox status could not be set");
        }

        return false;
    }

    public function enableMailDomain($domain) {
        $this->setMailDomainStatus($domain, enable);
    }

    public function disableMailDomain($domain) {
        $this->setMailDomainStatus($domain, disable);
    }

    private function setMailDomainStatus($domain, $action) {
        $site_id = $this->getIdFromDomain($domain);

        $request = <<<APICALL
            <mail>
              <$action>
                <site-id>$site_id</site-id>
              </$action>
            </mail>
APICALL;

        $response = pm_ApiRpc::getService()->call($request);

        $status = $response->mail->disable->result->status == "ok";

        if ($status)
        {
            pm_Log::debug("$domain status was set to $action");

            if ($action == disable)
            {
                $this->recordDisabledDomain("$site_id", $domain);
            }
        }
        else
        {
            pm_Log::err("$domain status could not be set");
        }
    }

    private function getMailDomainStatus($domains) {
        if( is_null($domains) ) {
            return NULL;
        }

        if (empty($domains))
        {
            return array();
        }

        $request = "<mail> <get_prefs> <filter>";

        foreach ($domains as $id => $domain)
        {
            $request .= "<site-id>$id</site-id>";
        }

        $request .= " </filter> </get_prefs> </mail>";

        $response = pm_ApiRpc::getService()->call($request);

        $results = $response->mail->get_prefs->result;

        $domain_results = array();

        $list_changed = false;
        foreach($results as $result) {
            $site_id = $result->{'site-id'};
            $enabled = $result->prefs->mailservice == "true";

            if (!$enabled)
            {
                array_push($domain_results, array('id' => "$site_id", 'domain' => $domains["$site_id"]));
            }
            else {
                $list_changed = true;
            }
        }

        if( $list_changed ) {
            Modules_Harvard_MailSettings::setDisabledDomains($domain_results);
        }

        return $domain_results;
    }

    function recordDisabledDomain($domain_id, $domain_name) {
        $domains = $this->getDisabledDomains();

        if (is_null($domains))
        {
            pm_Log::err("Error recording domain $domain_id");

            return null;
        }
        array_push($domains, array( 'id' => $domain_id, 'domain' => $domain_name));

        Modules_Harvard_MailSettings::setDisabledDomains($domains);
    }

    private function setDisabledDomains($domains) {
        $result = array();

        foreach ($domains as $domain)
        {
            $result[$domain['id']] = $domain['domain'];
        }

        pm_Log::err("Setting domains: " . implode($result));
        pm_Settings::set(disabled_mail_domains, json_encode($result));
    }

    /**
     * Retrieves a list of all domains with disabled mail service.
     *
     * @return  array|null  Array containing the disabled domains on success, null on failure.
     */
    public function getDisabledDomains()
    {
        //pm_Settings::clean();
        $domains_json = pm_Settings::get(disabled_mail_domains);

        if (is_null($domains_json))
        {
            return array();
        }
        else
        {
            $domains = json_decode($domains_json, true);

            if (is_null($domains))
            {
                pm_Log::err("Could not decode list of blocked domains ($domains_json)");

                return null;
            }
        }

        return self::getMailDomainStatus($domains);
    }

    /**
     * Retrieves the site ID for a given domain name.
     *
     * @param   string  $domain  Domain name to search for.
     *
     * @return  int  The site ID on success.
     */
    private function getIdFromDomain($domain)
    {
        $request = <<<APICALL
            <site>
              <get>
                <filter>
                  <name>$domain</name>
                </filter>
                <dataset>
                  <gen_info/>
                </dataset>
              </get>
            </site>
APICALL;

        $response = pm_ApiRpc::getService()->call($request);

        return $response->site->get->result->id;
    }

    /**
     * Retrieves the main mail account name for a given alias.
     *
     * @param   int     $siteId  ID of the site the mail account belongs to.
     * @param   string  $alias   Mail alias.
     *
     * @return  bool|string  The main account name on success, false on failure.
     */
    public function getMailFromAlias($siteId, $alias)
    {
        $request = <<<APICALL
<mail>
<get_info>
   <filter>
      <site-id>$siteId</site-id>
   </filter>
    <aliases/>
</get_info>
</mail>
APICALL;
        $response = pm_ApiRpc::getService()->call($request);

        $mailBoxes = $response->xpath('mail/get_info/result');

        foreach ($mailBoxes as $mailBox)
        {
            $aliases = $mailBox->xpath('mailname/alias');

            for ($i = 0; $i < count($aliases); $i++)
            {
                if ((string) $aliases[$i] === $alias)
                {
                    return (string) $mailBox->mailname->name;
                }
            }
        }

        return false;
    }
}
