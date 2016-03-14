<?php
/**
 * Inject Content
 *
 * @author  Timo Feuerstein <feuerstein.rhp@gmail.com>
 * @since   1.0
 */

class Modules_Harvard_ContentInclude extends pm_Hook_ContentInclude
{
    public function getJsOnReadyContent()
    {

        $script = 'var panel = new Element("div", {
                            "class": "custom-provider-header"
                        });

                        panel.update(
                            \'<div class="custom-provider-container">\' +
                            \'<p>' . pm_Locale::lmsg("message-suspended-site") . '</p>\' +
                            \'</div>\'
                        );
        ';
        $customersDomains = Modules_Harvard_Customer::affectedDomains();
        foreach((array)$customersDomains as $domain){
            $script .= 'document.getElementById("active-list-item-d:' . $domain['id'] . '").getElementsByClassName("caption-main")[0].appendChild(panel.cloneNode(true));' . PHP_EOL;
        }

        return $script;
    }

    public function getBodyContent()
    {
        return '<!-- tf html start -->
                <div class="mailchannels-alert-box">

                </div>
                <!-- tf html end -->';
    }
}

?>