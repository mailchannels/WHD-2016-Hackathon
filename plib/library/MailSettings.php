<?php

/**
 * Created by PhpStorm.
 * User: mike
 * Date: 2016-03-13
 * Time: 1:47 PM
 */
class Modules_Harvard_MailSettings
{
    function hello() {
        return "Hello world";
    }

    function disableMailUser() {

    }

    function disableMailDomain($domain) {
        $site_id = Modules_Harvard_MailSettings::getIdFromDomain($domain);

        $request = <<<APICALL
            <mail>
              <disable>
                <site-id>$site_id</site-id>
              </disable>
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