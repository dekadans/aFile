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

class PasswordCommand extends Command
{
    /** @var UserRepository */
    private $userRepository;

    /** @var QuestionHelper */
    private $questionHelper;

    /** @var User */
    private $user;

    /** @var string */
    private $oldPassword;

    /** @var string */
    private $newPassword;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('password');
        $this->setDescription('Change the password of a user.');

        $this->addArgument('username', InputArgument::REQUIRED, 'Username of the user whose password should be changed.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->questionHelper = $this->getHelper('question');

        $username = $input->getArgument('username');
        $this->user = $this->userRepository->getUserByUsername($username);

        if (!$this->user->isset()) {
            throw new \RuntimeException('User not found for username ' . $username);
        }

        $this->enterCurrentPassword($input, $output);
        $this->enterNewPassword($input, $output);

        $this->userRepository->updatePassword($this->user, $this->oldPassword, $this->newPassword);

        $output->writeln('The password has bee changed.');
    }

    private function enterCurrentPassword(InputInterface $input, OutputInterface $output)
    {
        $passwordQuestion = new Question('Please enter your current password: ');

        $passwordQuestion->setValidator(function($answer) {
            if (!password_verify($answer, $this->user->getHashedPassword())) {
                throw new \RuntimeException('Incorrect password');
            }

            return $answer;
        });

        $passwordQuestion->setHidden(true);
        $passwordQuestion->setMaxAttempts(3);

        $this->oldPassword = $this->questionHelper->ask($input, $output, $passwordQuestion);
    }


    private function enterNewPassword(InputInterface $input, OutputInterface $output)
    {
        $passwordQuestion = new Question('Please enter your new password: ');
        $passwordQuestion->setHidden(true);

        $this->newPassword = $this->questionHelper->ask($input, $output, $passwordQuestion);

        $passwordQuestion = new Question('Please repeat your new password: ');
        $passwordQuestion->setHidden(true);
        $passwordQuestion->setValidator(function($answer) {
            if ($answer !== $this->newPassword) {
                throw new \RuntimeException('The provided passwords do not match');
            }
        });
        $passwordQuestion->setMaxAttempts(3);

        $repeatedQuestion = $this->questionHelper->ask($input, $output, $passwordQuestion);
    }
}