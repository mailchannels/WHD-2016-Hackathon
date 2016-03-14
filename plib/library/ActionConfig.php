<?php
/**
 * Created by PhpStorm.
 * User: mike
 * Date: 2016-03-13
 * Time: 9:10 PM
 */

class Modules_Harvard_ActionConfig implements IteratorAggregate {

    private $config = [];

    function Modules_Harvard_ActionConfig() {
        $this->config = self::getActionConfig();
    }

    public function addAction($event, $action) {
        $found = false;
        foreach($this->config as $idx => $item) {
            if ($item['event'] == $event) {
                $found = true;
                $this->config[$idx] = ['event' => $event, 'action' => $action];
            }
        }
        if (!$found) {
            array_push($this->config, ['event' => $event, 'action' => $action]);
        }

        self::setActionConfig($this->config);
    }

    /**
     * Retrieve the action configuration from key/val storage
     */
    private static function getActionConfig()
    {
        $json = pm_Settings::get(actionConfig);

        pm_Log::debug("getActionConfig -> $json");

        if (!isset($json)) {
            $json = '[{"condition_name":"*"}]'; // TBD: Empty list once everything is working
        }

        return self::cleanConfig(json_decode($json, true));
    }

    /**
     * Store the action configuration to the key/val storage
     */
    private static function setActionConfig($config)
    {
        pm_Log::debug(sprintf("setActionConfig -> %s", print_r($config, true)));
        pm_Settings::set(actionConfig, json_encode(self::cleanConfig($config)));
    }

    private static function cleanConfig($config) {
        if (empty($config)) {
            $config = [];
        }
        foreach ($config as $idx => $item) {
            if (! (isset($item['event']) && isset($item['action']))) {
                unset($config[$idx]);
            }
        }
        return $config;
    }

    public static function getAvailableEvents() {
        return [
            'tag_is_spamming' => pm_Locale::lmsg('Sending Spam'),
            'tag_bad_recipients' => pm_Locale::lmsg('Sending to invalid recipients'),
            'tag_bounce_rate' => pm_Locale::lmsg('High bounce rate'),
            'tag_phishing' => pm_Locale::lmsg('Sending Phish')
        ];
    }

    public static function getAvailableActions() {
        return [
            'block_sender' => pm_Locale::lmsg('Block Sender'),
            'block_domain' => pm_Locale::lmsg('Block Domain')
        ];
    }

    /**
     * Retrieve an external iterator
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     * @since 5.0.0
     */
    public function getIterator()
    {
        return new ArrayIterator($this->config);
    }
}