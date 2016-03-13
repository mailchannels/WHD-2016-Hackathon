<?php

/**
 * Helper class for various purposes.
 *
 * @author  Constantin Romankiewicz <constantin@zweiiconkram.de>
 * @since   1.0
 */
class Modules_Harvard_Helper
{
    public static function error($message, $statusCode = 500)
    {
        http_response_code($statusCode);
        die($message);
    }
}