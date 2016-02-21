<?php

namespace AddIp;

class Template
{
    private $username = null;
    private $result = null;

    private $directory = null;


    public function __construct($username, $result)
    {
        $this->username = $username;
        $this->result = $result;

        $this->directory = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'template';
    }


    public function show()
    {
        if (true === $this->result) {
            $this->showSuccess();
        } else {
            $this->showError();
        }
    }


    private function showSuccess()
    {
        $content = file_get_contents($this->directory.'/success.html');
        $user_message = '';
        if (file_exists($this->directory.'/'.$this->username.'.html') === true) {
            $user_message = file_get_contents($this->directory.'/'.$this->username.'.html');
        }
        $content = str_replace('%user_message%', $user_message, $content);

        echo $content;
    }


    private function showError()
    {
        $content = file_get_contents($this->directory.'/error.html');
        $content = str_replace(
                    array('%command%', '%message%'),
                    array($this->result['command'], $this->result['output']),
                    $content
        );

        echo $content;
    }
}
