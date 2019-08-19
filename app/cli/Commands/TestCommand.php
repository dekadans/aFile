<?php

namespace cli\Commands;

use lib\Repositories\FileRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TestCommand extends Command
{
    /** @var FileRepository */
    private $fileRepository;

    public function __construct(FileRepository $fileRepository)
    {
        $this->fileRepository = $fileRepository;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('test');
        $this->setDescription('Just a test');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $file = $this->fileRepository->findByUniqueString('r2p77');

        $output->writeln($file->getName());
    }
}