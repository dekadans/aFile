<?php
/** @var \League\CLImate\CLImate $climate */

$userRepository = new \lib\Repositories\UserRepository(\lib\Database::getInstance());

if (!$climate->arguments->get('encryptionKeyUsername')) {
    $climate->red()->out('Error! A username must be passed.');
    die;
}

$username = $climate->arguments->get('encryptionKeyUsername');
$user = $userRepository->getUserByUsername($username);

if (!$user->isset()) {
    $climate->red()->out('Error! Could not find user!');
    die;
}

$climate->br()->out('Enter password:');
$input = $climate->password('>');
$password = $input->prompt();

$protectedKey = $userRepository->getProtectedEncryptionKeyForUser($user->getId());
try {
    $key = \Defuse\Crypto\KeyProtectedByPassword::loadFromAsciiSafeString($protectedKey)->unlockKey($password)->saveToAsciiSafeString();
} catch (\Defuse\Crypto\Exception\BadFormatException $e) {
    $climate->br()->out('Error!');
    die;
} catch (\Defuse\Crypto\Exception\EnvironmentIsBrokenException $e) {
    $climate->br()->out('Error!');
    die;
} catch (\Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException $e) {
    $climate->br()->out('Error! Probably due to incorrect password. Try again.');
    die;
}

$climate->br()->lightCyan('This is your password-encrypted key as it is saved in the database.');
$climate->br()->out($protectedKey);

$climate->br()->lightCyan('This is your hashed password.');
$climate->br()->out($user->getHashedPassword());

$climate->br()->lightCyan('This is your ACTUAL key used to encrypt/decrypt.');
$climate->br()->out($key);

$climate->br()->lightCyan('These might be useful if something bad happens and you need to manually restore files left encrypted. Save them somewhere safe.');
$climate->br()->lightCyan('Also note that files shared with a public URL are not encrypted with this key. Their keys are found in the \'share\' database table');