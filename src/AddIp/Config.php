<?php

class Config
{
    private $filename = null;

    private $commands = array();

    private $servers = array();
    private $ports = array();
    private $users = array();
    private $rights = array();

    /**
     * Constructor for configuration class
     *
     * @param string    $filename   The configuration filename to load
     */
    public function __construct($filename)
    {
        $this->setFilename($filename);
    }


    /**
     * Load the configuration
     *
     * @throws Exception    if the filename is not defined
     * @throws Exception    if an error occured while parsing the filename
     */
    public function load()
    {
        if (null === $this->filename) {
            throw new Exception('You must define filename before load the configuration');
        }

        $ini = parse_ini_file($this->filename, true);
        if (false === $ini) {
            throw new Exception('Impossible to parse the file '.$this->filename);
        }

        $this->loadCommands($ini);
        $this->loadServers($ini);
        $this->loadPorts($ini);
        $this->loadUsers($ini);
        $this->loadRights($ini);
    }


    /**
     * Get the password of a user
     *
     * @param   string  $username   The username of the user for which we want the password
     * @return  null|string         The password of the user, null if the username doesn't exist
     */
    public function getPassword($username)
    {
        if (isset($this->users[$username]) === false) {
            return null;
        }

        return $this->users[$username];
    }


    /**
     * Get the rights of the user
     *
     * @param   string  $username   The username of the user for which we want the rights
     * @return  array               An array with all permissions ([[server, port], [server, port]])
     */
    public function getRights($username)
    {
        if (isset($this->rights[$username]) === false) {
            return array();
        }

        return $this->rights[$username];
    }


    /**
     * Return the prefix string to add in the command
     *
     * @return string
     */
    public function getCommandPrefix()
    {
        if (isset($this->commands['prefix']) === false) {
            return '';
        }

        return (string)$this->commands['prefix'].' ';
    }


    /**
     * Return the suffix string to add in the command
     *
     * @return string
     */
    public function getCommandSuffix()
    {
        if (isset($this->commands['suffix']) === false) {
            return '';
        }

        return ' '.(string)$this->commands['suffix'];
    }


    /**
     * Return the command parameter
     *
     * @return string|null
     */
    public function getCommands($key = null)
    {
        if ($key !== null && isset($this->commands[$key]) === false) {
            return null;
        }

        return (null === $key) ? $this->commands : $this->commands[$key];
    }


    /**
     * Load the servers configuration
     *
     * @param array     $ini    The array with the global configuration
     * @return bool             True if the configuration exists, false else
     */
    private function loadCommands(array $ini)
    {
        return $this->loadSimple($ini, 'command', 'commands');
    }


    /**
     * Load the servers configuration
     *
     * @param array     $ini    The array with the global configuration
     * @return bool             True if the configuration exists, false else
     */
    private function loadServers(array $ini)
    {
        return $this->loadSimple($ini, 'server', 'servers');
    }


    /**
     * Load the ports configuration
     *
     * @param array     $ini    The array with the global configuration
     * @return bool             True if the configuration exists, false else
     */
    private function loadPorts(array $ini)
    {
        return $this->loadSimple($ini, 'port', 'ports');
    }


    /**
     * Load the users configuration
     *
     * @param array     $ini    The array with the global configuration
     * @return bool             True if the configuration exists, false else
     */
    private function loadUsers(array $ini)
    {
        return $this->loadSimple($ini, 'user', 'users');
    }


    /**
     * Load the rights configuration
     *
     * @param array     $ini    The array with the global configuration
     * @return bool             True if the configuration exists, false else
     */
    private function loadRights(array $ini)
    {
        if (isset($ini['right']) === false) {
            return false;
        }

        foreach ($ini['right'] as $user => $rights) {
            if (isset($this->users[$user]) === false) {
                // The user doesn't exist, useless to get rights
                continue;
            }

            $this->rights[$user] = array();

            foreach ($rights as $right) {
                list($server, $port) = explode(':', $right);
                if (empty($server) === true || empty($port) === true) {
                    throw new Exception('impossible to parse line '.$right);
                }

                if (isset($this->ports[$port]) === false) {
                    throw new Exception('impossible to find port '.$port);
                }

                // all is a keyword for all servers
                if ('all' === $server) {
                    foreach ($this->servers as $server) {
                        $this->rights[$user][] = array(
                            'server' => $server,
                            'port' => $this->ports[$port],
                        );
                    }
                } else {
                    if (isset($this->servers[$server]) === false) {
                        throw new Exception('impossible to find server '.$server);
                    }

                    $this->rights[$user][] = array(
                        'server' => $this->servers[$server],
                        'port' => $this->ports[$port],
                    );
                }
            }
        }

        return true;
    }


    /**
     * Load a configuration in the class
     *
     * @param array     $ini    The array with the global configuration
     * @param string    $key    The key of the configuration in the array ($ini)
     * @param string    $var    The name of var to use for store the configuration
     * @return bool             True if the configuration exists, false else
     */
    private function loadSimple(array $ini, $key, $var)
    {
        if (isset($ini[$key]) === false) {
            return false;
        }

        $this->$var = $ini[$key];

        return true;
    }


    /**
     * Init the filename to use to get the configuration
     *
     * @param $filename
     * @throws Exception   if the filename doesn't exist
     */
    private function setFilename($filename)
    {
        if (file_exists($filename) === false) {
            throw new Exception($filename.' doesn\'t exist');
        }

        $this->filename = $filename;
    }
}