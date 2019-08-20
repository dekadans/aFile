<?php

namespace cli\Commands;

use lib\DataTypes\User;
use lib\Repositories\UserRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class AddUserCommand extends Command
{
    /** @var UserRepository */
    private $userRepository;

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
        $io = new SymfonyStyle($input, $output);
        $io->title('Add user');

        $username = $input->getArgument('username');
        $password = $this->enterPassword($io);

        $result = $this->userRepository->createUser($username, $password);

        if ($result instanceof User) {
            $io->success('User added');
        } else {
            $io->error($result);
        }
    }

    private function enterPassword(SymfonyStyle $io)
    {
        $newPassword = $io->askHidden('Please enter a password');

        $repeatedQuestion = $io->askHidden('Please repeat the password', function($answer) use ($newPassword) {
            if ($answer !== $newPassword) {
                throw new \RuntimeException('The provided passwords do not match');
            }

            return $answer;
        });

        return $newPassword;
    }
}