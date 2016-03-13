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
     */
    public function disableMailUser($domain, $user) {
        $this->setMailUserStatus($domain, $user, false);
    }

    /**
     * Enables the mailbox for a certain user.
     *
     * @param   string  $domain  The domain name the mail account belongs to.
     * @param   string  $user    The user name to unblock.
     */
    public function enableMailUser($domain, $user) {
        $this->setMailUserStatus($domain, $user, true);
    }

    /**
     * Sets the mailbox status for a certain user.
     *
     * @param   string  $domain   The domain name the mail account belongs to.
     * @param   string  $user     The user name.
     * @param   bool    $enabled  Whether the mailbox should be enabled or disabled.
     */
    private function setMailUserStatus($domain, $user, $enabled = false) {
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

        if( $status ) {
            pm_Log::debug("$user@$domain's mailbox status was set to $setEnabled");

            if( !$enabled ) {
                // TODO: $this->recordDisabledMailbox($domain, $user);
            }
        }
        else {
            $trueName = $this->getMailFromAlias($site_id, $user);

            if ($trueName !== false) {
                $this->setMailUserStatus($domain, $trueName, $enabled);
            }

            pm_Log::err("$user@$domain's mailbox status could not be set");
        }

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

        if( $status ) {
            pm_Log::debug("$domain status was set to $action");
            if( $action == disable ) {
                $this->recordDisabledDomain($domain);
            }
        }
        else {
            pm_Log::err("$domain status could not be set");
        }
    }

    public function getMailDomainStatus($domain) {
        $site_id = $this->getIdFromDomain($domain);

        $request = <<<APICALL
            <mail>
                <get_prefs>
                    <filter>
                        <site-id>$site_id</site-id>
                    </filter>
                </get_prefs>
            </mail>
APICALL;

        $response = pm_ApiRpc::getService()->call($request);

        $status = $response->mail->get_prefs->result->prefs->mailservice == "true";

        pm_Log::err("status of $domain is $status");
        return $status;
    }

    private function recordDisabledDomain($domain) {
        $domains = $this->getDisabledDomains();
        if( !in_array($domain, $domains) ) {
            array_push($domains, $domain);
        }
        pm_Settings::set(disabled_mail_domains, json_encode($domains));
    }

    public function getDisabledDomains() {
        $domains_json = pm_Settings::get(disabled_mail_domains);
        if( $domains_json == null ) {
            $domains = array();
        }
        else {
            $domains = json_decode($domains_json);
        }
        if( $domains == null ) {
            pm_Log::err("Could not decode list of blocked domains($domains_json)");
            return null;
        }

        function status($domain) {
            return !$this->getMailDomainStatus($domain);
        }
        // check to make sure they're still disabled:
        $domains = array_filter($domains, "status");

        return $domains;
    }

    private function getIdFromDomain($domain) {
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

        foreach ($mailBoxes as $mailBox) {
            $aliases = $mailBox->xpath('mailname/alias');

            for ($i = 0; $i < count($aliases); $i++) {
                if ((string) $aliases[$i] === $alias) {
                    return (string) $mailBox->mailname->name;
                }
            }
        }

        return false;
    }

}
