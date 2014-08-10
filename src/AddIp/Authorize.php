<?php

namespace AddIp;

class Authorize
{
    private $username = null;
    private $ip = null;
    private $command = null;
    private $config = null;

    public function __construct($username, $ip, $command)
    {
        $this->username = $username;
        $this->ip = $ip;
        $this->command = $command;
    }


    /**
     * Inject configuration
     *
     * @param Config $config
     * @return $this
     */
    public function setConfig(Config $config)
    {
        $this->config = $config;
        return $this;
    }


    /**
     * Authorize the access for the user
     *
     * @return array|bool   true if it's ok, array(command, output) else
     * @throws \Exception    if the configuration isn't initialize
     */
    public function authorizeAccess()
    {
        if ($this->config === null) {
            throw new \Exception('config must be initialize');
        }

        $rights = $this->config->getRights($this->username);
        $rights_to_send = array();

        foreach ($rights as $right) {
            $rights_to_send[] = $right['server'].':'.$right['port'];
        }

        $command = sprintf($this->config->getCommandPrefix().$this->command.$this->config->getCommandSuffix(),
                            escapeshellarg(implode(',', $rights_to_send)),
                            escapeshellarg($this->ip)
        );

        exec($command, $output, $return);
        if (0 === $return) {
            Log::logSuccess($this->username, $this->ip, 'Success in command : '.$command);
            return true;
        }

        Log::logError($this->username, $this->ip, 'Error in command : '.$command);
        return array('command' => $command, 'output' => implode('<br />', $output));
    }
}