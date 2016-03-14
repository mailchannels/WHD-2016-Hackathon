<?php

/**
 * Customer class.
 *
 * @author  Timo feuerstein <feuerstein.rhp@gmail.com>
 * @since   1.0
 */
class Modules_Harvard_Customer
{

    public static function affectedDomains()
    {
        $domains = $accessableDomains = array();
        $MailSettings = new Modules_Harvard_MailSettings();
        $domains = $MailSettings->getDisabledDomains();
        $CurrentUser = pm_Session::getClient();
        foreach((array)$domains as $domain){
            if($CurrentUser->hasAccessToDomain($domain['id'])){
                $accessableDomains[] = $domain;
            }
        }
        return $accessableDomains;
    }

}