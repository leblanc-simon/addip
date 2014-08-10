<?php

namespace AddIp;

class Cli
{
    static private $configuration = null;

    static private $servers = array();
    static private $ip = null;

    static private $exit_status = array(
        'success' => 0,
        'bad_num_args' => 1,
        'fail_add_rules' => 2,
        'exception' => 100,
    );

    static public function run($args)
    {
        try {
            self::loadConfiguration();
            self::getArgs($args);

            if (self::processRules() === false) {
                self::exitApp('fail_add_rules');
            }

            self::exitApp('success');
        } catch (\Exception $e) {
            Log::logError('cli', '-', $e->getMessage());
            self::exitApp('exception');
        }
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


    static private function getArgs($args)
    {
        if (count($args) !== 3) {
            self::exitApp('bad_num_args');
        }

        $servers = $args[1];
        $ip = $args[2];

        if (preg_match('/[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}/', $ip) == false) {
            throw new \Exception('bad format of IP');
        }
        self::$ip = $ip;

        $list_servers = explode(',', $servers);
        foreach ($list_servers as $server) {
            list($name, $port) = explode(':', $server);
            self::$servers[] = array('server' => $name, 'port' => $port);
        }
    }


    static private function processRules()
    {
        $error = false;
        foreach (self::$servers as $server) {
            if (self::addException(self::$ip, $server['server'], $server['port']) === false) {
                Log::logError(null, self::$ip, 'Fail to add rule : '.$server['server'].':'.$server['port']);
                $error = true;
            }
        }

        return !$error;
    }


    static private function addException($ip, $server, $port)
    {
        $iptables_bin = self::$configuration->getCommands('iptables_bin');
        $iptables_rm = self::$configuration->getCommands('iptables_rm');
        $iptables_rm_alt = self::$configuration->getCommands('iptables_rm_alt');
        $local_server = self::$configuration->getCommands('local_server');
        $time = self::$configuration->getCommands('time');

        $command_check = $iptables_bin.' -L -v -n --line-numbers | grep "tcp dpt:'.$port.'" | grep "ACCEPT" | grep "'.$ip.'" > /dev/null';
        $command = $iptables_bin.' -I INPUT -p tcp --dport '.$port.' -s '.$ip.' -j ACCEPT';
        $command_del = 'if [ -f "'.$iptables_rm.'" ]; then echo "'.$iptables_rm.' '.$ip.' '.$port.'"; else echo "'.$iptables_rm_alt.' '.$ip.' '.$port.'"; fi | at now +'.$time.' hours';


        if ($server !== $local_server) {
            // The command is run over SSH
            $command_check = self::commandOverSSH($server, $command_check);
            $command = self::commandOverSSH($server, $command);
            $command_del = self::commandOverSSH($server, $command_del);
        }

        // - check if the rule already exist
        exec($command_check, $output, $return);
        if ($return === 0) {
            Log::logInfo('The rule already exists : '.$command_check);
            return true;
        } else {
            Log::logInfo('check : '.implode(' -- ', $return));
        }

        // - add the rule
        exec($command, $output, $return);
        if ($return === 0) {
            Log::logInfo('The rule is added : '.$server.':'.$port.' for '.$ip);
            // schedule the delete
            exec($command_del, $output, $return);
            if ($return === 0) {
                Log::logInfo('The schedule delete rule is added : '.$server.':'.$port.' for '.$ip);
                self::sendMail($ip, $server, $port);
                return true;
            }
        }

        return false;
    }


    /**
     * @param   string  $ip
     * @param   string  $server
     * @param   string  $port
     */
    static private function sendMail($ip, $server, $port)
    {
        $subject = 'Ajout de regle iptables temporaire';
        $message = 'Une regle iptables temporaire a ete ajoutee :
  - utilisateur : '.$ip.'
  - serveur : '.$server.'
  - port : '.$port;

        mail(self::$configuration->getCommands('mail'), $subject, $message);
    }


    /**
     * Build a command to send over SSH
     *
     * @param   string  $server     The server name
     * @param   string  $command    The command to execute
     * @return  string
     */
    static private function commandOverSSH($server, $command)
    {
        return 'ssh root@'.$server.' \''.$command.'\'';
    }


    /**
     * Exit the application and return a valid status code
     *
     * @param string    $status     The status exit (self::$exit_status)
     */
    static private function exitApp($status)
    {
        $exit = 255;
        if (isset(self::$exit_status[$status]) === true) {
            $exit = self::$exit_status[$status];
        }

        exit($exit);
    }
}