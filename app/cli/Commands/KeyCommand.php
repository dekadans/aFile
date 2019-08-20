<?php

namespace cli\Commands;

use lib\DataTypes\User;
use lib\Repositories\UserRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class KeyCommand extends Command
{
    /** @var UserRepository */
    private $userRepository;

    /** @var User */
    private $user;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('key');
        $this->setDescription('Prints the encryption key for a given username.');
        $this->addArgument('username', InputArgument::REQUIRED, 'The username for which to print the encryption key');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $eio = $io->getErrorStyle();
        $eio->title('Print encryption key');

        $username = $input->getArgument('username');
        $this->user = $this->userRepository->getUserByUsername($username);

        if (!$this->user->isset()) {
            throw new \RuntimeException('User not found for username ' . $username);
        }

        $protectedKey = $this->userRepository->getProtectedEncryptionKeyForUser($this->user->getId());

        $password = $this->enterPassword($eio);

        $key = $protectedKey->unlockKey($password);

        $eio->note('Save this key somewhere safe, physically separate from the aFile installation.');

        $io->writeln($key->saveToAsciiSafeString());
        $eio->newLine();
    }

    private function enterPassword(SymfonyStyle $eio) : string
    {
        $password = $eio->askHidden('Please enter your password', function($answer) {
            if (!password_verify($answer, $this->user->getHashedPassword())) {
                throw new \RuntimeException('Incorrect password');
            }

            return $answer;
        });

        return $password ?? '';
    }
}