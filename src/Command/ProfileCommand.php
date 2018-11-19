<?php

namespace Fbourigault\PhpBench\Extension\Blackfire\Command;

use PhpBench\Benchmark\RunnerConfig;
use PhpBench\Console\Command\Handler\RunnerHandler;
use PhpBench\Extensions\XDebug\Command\Handler\OutputDirHandler;
use PhpBench\Model\Iteration;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class ProfileCommand extends Command
{
    private $runnerHandler;

    public function __construct(RunnerHandler $runnerHandler)
    {
        parent::__construct();
        $this->runnerHandler = $runnerHandler;
    }

    protected function configure()
    {
        $this->setName('blackfire:profile');
        RunnerHandler::configure($this);
        OutputDirHandler::configure($this);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $config = RunnerConfig::create()
            ->withExecutor([
                'executor' => 'blackfire',
                'callback' => function (Iteration $iteration, array $result) use ($output) {
                    $output->writeln("Graph URL <info>{$result['profile']}</info>");
                }
            ])
            ->withIterations([1])
        ;

        $this->runnerHandler->runFromInput($input, $output, $config);
    }
}
