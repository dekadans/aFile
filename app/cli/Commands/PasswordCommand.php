<?php

namespace cli\Commands;

use Defuse\Crypto\Exception\BadFormatException;
use Defuse\Crypto\Key;
use lib\DataTypes\User;
use lib\Repositories\UserRepository;
use lib\Services\EncryptionService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class PasswordCommand extends Command
{
    /** @var UserRepository */
    private $userRepository;

    /** @var EncryptionService */
    private $encryptionService;

    /** @var User */
    private $user;

    /** @var string */
    private $oldPassword;

    /** @var string */
    private $newPassword;

    public function __construct(UserRepository $userRepository, EncryptionService $encryptionService)
    {
        $this->userRepository = $userRepository;
        parent::__construct();
        $this->encryptionService = $encryptionService;
    }

    protected function configure()
    {
        $this->setName('password');
        $this->setDescription('Change the password of a user.');

        $this->addArgument('username', InputArgument::REQUIRED, 'Username of the user whose password should be changed.');
        $this->addOption('keypath', 'k', InputOption::VALUE_REQUIRED, 'The path to the file containing the key');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Change password');

        $username = $input->getArgument('username');
        $this->user = $this->userRepository->getUserByUsername($username);

        if (!$this->user->isset()) {
            throw new \RuntimeException('User not found for username ' . $username);
        }

        $key = $this->getKey($input, $io);

        if ($key) {
            $io->note('Encryption key found');
            $this->enterNewPassword($io);
            $newProtectedKey = $this->encryptionService->passwordEncryptKey($key, $this->newPassword);
            $this->userRepository->updatePasswordAndKey($this->user, $newProtectedKey, $this->newPassword);
        } else {
            $this->enterCurrentPassword($io);
            $this->enterNewPassword($io);
            $this->userRepository->updatePassword($this->user, $this->oldPassword, $this->newPassword);
        }

        $io->success('The password has been changed.');
    }

    private function getKey(InputInterface $input, SymfonyStyle $io)
    {
        $keypath = $input->getOption('keypath');

        if (!empty($keypath)) {
            if (file_exists($keypath)) {
                $keyString = file_get_contents($keypath);
                try {
                    $key = Key::loadFromAsciiSafeString($keyString);
                    return $key;
                } catch (BadFormatException $e) {
                }
            }
            $io->error('A path to an encryption key was provided, but a valid key was not found.');
        }

        return false;
    }

    private function enterCurrentPassword(SymfonyStyle $io)
    {
        $this->oldPassword = $io->askHidden('Please enter your current password', function($answer) {
            if (!password_verify($answer, $this->user->getHashedPassword())) {
                throw new \RuntimeException('Incorrect password');
            }

            return $answer;
        });
    }


    private function enterNewPassword(SymfonyStyle $io)
    {
        $this->newPassword = $io->askHidden('Please enter your new password');

        $repeatedQuestion = $io->askHidden('Please repeat your new password', function($answer) {
            if ($answer !== $this->newPassword) {
                throw new \RuntimeException('The provided passwords do not match');
            }

            return $answer;
        });
    }
}