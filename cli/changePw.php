<?php
/** @var \League\CLImate\CLImate $climate */

$userRepository = new \lib\Repositories\UserRepository(\lib\Database::getInstance());

if (!$climate->arguments->get('username')) {
    $climate->red()->out('Error! A username must be passed.');
    die;
}

$username = $climate->arguments->get('username');

$climate->out($username);