#!/usr/local/bin/php5
<?php

require_once dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'autoload.php';

\AddIp\Cli::run($argv);
