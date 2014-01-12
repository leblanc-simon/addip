<?php

require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'Log.php';
require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'Config.php';
require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'Login.php';
require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'Authorize.php';
require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'Template.php';

class Application
{
    static private $configuration = null;

    static private $username = null;
    static private $ip = null;

    /**
     * Run the application
     */
    static public function run()
    {
        self::loadConfiguration();

        if (self::login() === false) {
            return;
        }

        $result = self::authorizeAccess();
        self::showTemplate($result);
    }


    /**
     * Load the application configuration
     *
     * @static
     * @access  private
     */
    static private function loadConfiguration()
    {
        self::$configuration = new Config(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'config.ini');
        self::$configuration->load();
    }


    /**
     * Login processing
     *
     * @return  bool      false if authentification fail or is require, true else
     * @static
     * @access  private
     */
    static private function login()
    {
        $login = new Login($_SERVER);
        $login->setConfig(self::$configuration);

        if ($login->check() === false) {
            $login->requestAuth();
            return false;
        }

        self::$username = $login->getUsername();
        self::$ip = $login->getIp();

        return true;
    }


    /**
     * Authorize the access of the user
     */
    static private function authorizeAccess()
    {
        $command = dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.'bin'.DIRECTORY_SEPARATOR.'addip';
        $authorize = new Authorize(self::$username, self::$ip, $command.' %s %s');
        $authorize->setConfig(self::$configuration);

        return $authorize->authorizeAccess();
    }


    /**
     * Show the web page
     *
     * @param bool|array    $result     the command's result
     */
    static private function showTemplate($result)
    {
        $template = new Template(self::$username, $result);
        $template->show();
    }
}