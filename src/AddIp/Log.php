<?php

class Log
{
    static public function logSuccess($username, $ip, $message)
    {
        self::write($username, $ip, $message, 'success');
    }


    static public function logInfo($message)
    {
        self::write(null, null, $message, 'info');
    }


    static public function logError($username, $ip, $message)
    {
        self::write($username, $ip, $message, 'error');
    }


    static private function write($username, $ip, $message, $type)
    {
        $directory = dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.'logs';
        if (is_dir($directory) === false) {
            if (@mkdir($directory, 0755) === false) {
                return false;
            }
        }

        $filename = $directory.DIRECTORY_SEPARATOR.'addip.log';

        return (bool)file_put_contents($filename, '['.$type.'] '.date('Y-m-d:H:i:s').' - '.$username.' - '.$ip.' - '.$message."\n", FILE_APPEND);
    }
}