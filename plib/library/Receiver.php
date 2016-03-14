<?php

/**
 * Receives and processes input.
 *
 * @author  Constantin Romankiewicz <constantin@zweiiconkram.de>
 * @since   1.0
 */
class Modules_Harvard_Receiver
{
    /**
     * Processes data from a JSON file generated by Mailchannels.
     * Deals with reportedly spamming email accounts.
     *
     * @param   array  $data  The decoded data.
     *
     * @return void
     */
    public function processJson($data)
    {
        pm_Log::debug(print_r($data, true));

        $actionConfig = new Modules_Harvard_ActionConfig;

        if (empty($actionConfig))
        {
            return;
        }

        if (!(isset($data['envelope_sender']) && isset($data['condition_name'])))
        {
            Modules_Harvard_Helper::error('Invalid request.', 400);
        }

        $event = $data['condition_name'];
        $sender = $data['envelope_sender'];

        if (preg_match('/(.*)\@(.*)/i', $data['envelope_sender'], $matches))
        {
            $username = $matches[1];
            $domain = $matches[2];
        }

        $mailSettings = new Modules_Harvard_MailSettings();

        foreach ($actionConfig as $item) {
            if ($item['event'] == $event || $item['event'] == 'all') {

                pm_Log::debug("condition: $item->condition_name, domain: $domain");

                $mailSettings = new Modules_Harvard_MailSettings;

                // TODO: Check for relevant settings.
                $mailSettings->disableMailDomain($domain);
                //$mailSettings->disableMailUser($domain, $username);

                return;
            }
        }
    }
}
