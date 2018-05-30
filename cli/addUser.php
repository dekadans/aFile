<?php
/** @var \League\CLImate\CLImate $climate */

$userRepository = new \lib\Repositories\UserRepository(\lib\Database::getInstance());

if (!$climate->arguments->get('newUsername')) {
    $climate->red()->out('Error! A username must be passed.');
    die;
}

$username = $climate->arguments->get('newUsername');

if ($userRepository->getUserByUsername($username)->isset()) {
    $climate->red()->out('Error! A user with that username already exists!');
    die;
}

$climate->br()->out('Choose a password:');
$input = $climate->password('>');
$password1 = $input->prompt();

$climate->br()->out('Repeat it:');
$input = $climate->password('>');
$password2 = $input->prompt();

if (strcmp($password1, $password2) !== 0) {
    $climate->br()->red()->out('Error! Password mismatch.');
    die;
}

$result = $userRepository->createUser($username, $password1);

if (is_string($result)) {
    $climate->br()->green()->out('Error! ' . $result);
}
else {
    $climate->br()->green()->out('Done!');
}