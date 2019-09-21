<?php

namespace App;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use App\BbClient;

class ReplaceCommand extends Command
{
    const DEFAULT_BASE_URL = 'https://oldschool.co.nz/index.php?';

    protected function configure()
    {
        $this
            ->setName('replace')
            ->setDescription('Search and replace in BB forum posts')
            ->addOption('base-url','b', InputOption::VALUE_REQUIRED, 'Base URL', self::DEFAULT_BASE_URL)
            ->addOption('username','u', InputOption::VALUE_REQUIRED, 'Username')
            ->addOption('password','p', InputOption::VALUE_REQUIRED, 'Password')
            ->addArgument('search', InputArgument::REQUIRED, 'Search Regex')
            ->addArgument('replace', InputArgument::REQUIRED, 'Replace string')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $client = new BbClient(
            new \App\Container($input->getOption('base-url')),
            $input->getOption('base-url'),
            $input->getOption('username'),
            $input->getOption('password')
        );

        $output->writeln(sprintf('Replacing `%s` with `%s` for `%s`'
            , $input->getArgument('search')
            , $input->getArgument('replace')
            , $input->getOption('username')
        ));

        $client->replaceInPosts($input->getArgument('search'), $input->getArgument('replace'));
        $output->writeln('All done.');
    }
}