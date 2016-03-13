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
    function hello() {
        return "Hello world";
    }

    function disableMailUser() {

    }

    function enableMailDomain($domain) {
        Modules_Harvard_MailSettings::setMailDomainStatus($domain, enable);
    }

    function disableMailDomain($domain) {
        Modules_Harvard_MailSettings::setMailDomainStatus($domain, disable);
    }

    function setMailDomainStatus($domain, $action) {
        pm_Log::debug("setMailDomainStatus: $domain => $action");
        $site_id = Modules_Harvard_MailSettings::getIdFromDomain($domain);

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
                Modules_Harvard_MailSettings::recordDisabledDomain($domain);
            }
        }
        else {
            pm_Log::err("$domain status could not be set");
        }
    }

    function getMailDomainStatus($domain) {
        $site_id = Modules_Harvard_MailSettings::getIdFromDomain($domain);

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

    function recordDisabledDomain($domain) {
        $domains = Modules_Harvard_MailSettings::getDisabledDomains();
        if( !in_array($domain, $domains) ) {
            array_push($domains, $domain);
        }
        pm_Settings::set(disabled_mail_domains, json_encode($domains));
    }

    function getDisabledDomains() {
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
            return !Modules_Harvard_MailSettings::getMailDomainStatus($domain);
        }
        // check to make sure they're still disabled:
        $domains = array_filter($domains, "status");

        return $domains;
    }

    function getIdFromDomain($domain) {
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

}