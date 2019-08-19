<?php

namespace cli\Commands;

use lib\DataTypes\User;
use lib\Repositories\UserRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class KeyCommand extends Command
{
    /** @var UserRepository */
    private $userRepository;

    /** @var QuestionHelper */
    private $questionHelper;

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
        $this->questionHelper = $this->getHelper('question');

        $username = $input->getArgument('username');
        $this->user = $this->userRepository->getUserByUsername($username);

        if (!$this->user->isset()) {
            throw new \RuntimeException('User not found for username ' . $username);
        }

        $protectedKey = $this->userRepository->getProtectedEncryptionKeyForUser($this->user->getId());

        $password = $this->enterPassword($input, $output);

        $key = $protectedKey->unlockKey($password);
        $output->writeln($key->saveToAsciiSafeString());
    }

    private function enterPassword(InputInterface $input, OutputInterface $output) : string
    {
        $passwordQuestion = new Question('Please enter your password: ');

        $passwordQuestion->setValidator(function($answer) {
            if (!password_verify($answer, $this->user->getHashedPassword())) {
                throw new \RuntimeException('Incorrect password');
            }

            return $answer;
        });

        $passwordQuestion->setHidden(true);
        $passwordQuestion->setMaxAttempts(3);

        return $this->questionHelper->ask($input, $output, $passwordQuestion) ?? '';
    }
}