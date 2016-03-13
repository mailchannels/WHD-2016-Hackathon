<?php

/**
 * Created by PhpStorm.
 * User: mike
 * Date: 2016-03-13
 * Time: 1:47 PM
 */

const enabled = 'enable';
const disable = 'disable';

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
        $site_id = Modules_Harvard_MailSettings::getIdFromDomain($domain);

        $request = <<<APICALL
            <mail>
              <$action>
                <site-id>$site_id</site-id>
              </$action>
            </mail>
APICALL;

        $response = pm_ApiRpc::getService()->call($request);
        return $response->site->get->result->id;
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