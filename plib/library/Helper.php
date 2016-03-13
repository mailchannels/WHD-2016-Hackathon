<?php

/**
 * Helper class for various purposes.
 *
 * @author  Constantin Romankiewicz <constantin@zweiiconkram.de>
 * @since   1.0
 */
class Modules_Harvard_Helper
{
    /**
     * Prints an error message, returns an appropriate HTTP status code and exits.
     *
     * @param   string  $message     Error message.
     * @param   int     $statusCode  HTTP status code.
     *
     * @return void
     */
    public static function error($message, $statusCode = 500)
    {
        http_response_code($statusCode);
        die($message);
    }


    /**
     * Generates an unique random hash based on the user ID and current timestamp.
     *
     * @param   int  $numbers  Number of included random numbers.
     *
     * @return string Unique hash
     */
    public static function genRandHash($numbers = 3)
    {
        $str = (string) microtime();

        if (pm_Session::isExist())
        {
            $str .= pm_Session::getClient()->getId();
        }
        else
        {
            $str .= 0;
        }

        for ($i = 0; $i < $numbers; $i++)
        {
            $rand = mt_rand(0, PHP_INT_MAX);
            $str .= $rand;
        }

        return sha1($str);
    }
}