<?php

/**
 * Receives and processes input.
 *
 * @author  Constantin Romankiewicz <constantin@zweiiconkram.de>
 * @since   1.0
 */
class Modules_Harvard_Receiver
{
    public function processJson($data)
    {
        pm_Log::debug(print_r($data, true));

        if (isset($data['envelope_sender']))
        {
            $sender = $data['envelope_sender'];

            if (preg_match('/(.*)\@(.*)/i', $data['envelope_sender'], $matches))
            {
                $username = $matches[1];
                $domain   = $matches[2];

                $mailSettings = new Modules_Harvard_MailSettings();
                var_dump($mailSettings->disableMailDomain($domain));
            }
        }
    }
}
