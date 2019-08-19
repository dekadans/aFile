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

class AddUserCommand extends Command
{
    /** @var UserRepository */
    private $userRepository;

    /** @var QuestionHelper */
    private $questionHelper;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('add-user');
        $this->setDescription('Adds a new user.');
        $this->addArgument('username', InputArgument::REQUIRED, 'The username of he new user.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->questionHelper = $this->getHelper('question');

        $username = $input->getArgument('username');
        $password = $this->enterPassword($input, $output);

        $result = $this->userRepository->createUser($username, $password);

        if ($result instanceof User) {
            $output->writeln('User added');
        } else {
            $output->writeln($result);
        }
    }

    private function enterPassword(InputInterface $input, OutputInterface $output)
    {
        $passwordQuestion = new Question('Please enter a password: ');
        $passwordQuestion->setHidden(true);

        $newPassword = $this->questionHelper->ask($input, $output, $passwordQuestion) ?? '';

        $passwordQuestion = new Question('Please repeat the password: ');
        $passwordQuestion->setHidden(true);
        $passwordQuestion->setValidator(function($answer) use ($newPassword) {
            if ($answer !== $newPassword) {
                throw new \RuntimeException('The provided passwords do not match');
            }
        });
        $passwordQuestion->setMaxAttempts(3);

        $repeatedQuestion = $this->questionHelper->ask($input, $output, $passwordQuestion);

        return $newPassword;
    }
}