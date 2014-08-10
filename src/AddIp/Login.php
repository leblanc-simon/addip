<?php

namespace AddIp;

class Login
{
    private $username = null;
    private $password = null;
    private $ip = null;

    private $config = null;

    /**
     * Login class constructor
     *
     * @param   array   $server     the array with the authentification parameter (PHP_AUTH_USER and PHP_AUTH_PW)
     */
    public function __construct(array $server)
    {
        if (isset($server['PHP_AUTH_USER']) === true && empty($server['PHP_AUTH_USER']) === false) {
            $this->username = $server['PHP_AUTH_USER'];
        }

        if (isset($server['PHP_AUTH_PW']) === true && empty($server['PHP_AUTH_PW']) === false) {
            $this->password = $server['PHP_AUTH_PW'];
        }

        if (isset($server['REMOTE_ADDR']) === true && empty($server['REMOTE_ADDR']) === false) {
            $this->ip = $server['REMOTE_ADDR'];
        }
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
     * Check if the login is valid
     *
     * @throws  \Exception   If the configuration is not initialize
     * @return  bool        True if the login / password is valid, false else
     */
    public function check()
    {
        if (null === $this->config) {
            throw new \Exception('config must be initialize');
        }

        if (null === $this->username || null === $this->ip) {
            return false;
        }

        $real_password = $this->config->getPassword($this->username);
        if ($real_password !== $this->password) {
            Log::logError($this->username, $this->ip, 'Invalid password : '.$this->password);
            return false;
        }

        return true;
    }


    /**
     * Return the current username
     *
     * @return null|string  The current username
     */
    public function getUsername()
    {
        return $this->username;
    }


    /**
     * Return the current ip of the user
     *
     * @return null|string  The current ip of the user
     */
    public function getIp()
    {
        return $this->ip;
    }


    /**
     * Build header to request user authentification
     */
    public function requestAuth()
    {
        $realm = 'Secure access';

        header('HTTP/1.1 401 Unauthorized');
        header('WWW-Authenticate: Basic realm="'.$realm.'",qop="auth",nonce="'.uniqid().'",opaque="'.md5($realm).'"');
    }
}