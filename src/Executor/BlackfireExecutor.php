<?php

namespace Fbourigault\PhpBench\Extension\Blackfire\Executor;

use PhpBench\Benchmark\ExecutorInterface;
use PhpBench\Benchmark\Metadata\BenchmarkMetadata;
use PhpBench\Benchmark\Metadata\SubjectMetadata;
use PhpBench\Benchmark\Remote\Launcher;
use PhpBench\Model\Iteration;
use PhpBench\Model\Result\MemoryResult;
use PhpBench\Model\Result\TimeResult;
use PhpBench\Registry\Config;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class BlackfireExecutor implements ExecutorInterface
{
    private $launcher;

    public function __construct(Launcher $launcher)
    {
        $this->launcher = $launcher;
    }

    public function execute(SubjectMetadata $subjectMetadata, Iteration $iteration, Config $config)
    {
        $tokens = [
            'class' => $subjectMetadata->getBenchmark()->getClass(),
            'file' => $subjectMetadata->getBenchmark()->getPath(),
            'subject' => $subjectMetadata->getName(),
            'revolutions' => $iteration->getVariant()->getRevolutions(),
            'beforeMethods' => var_export($subjectMetadata->getBeforeMethods(), true),
            'afterMethods' => var_export($subjectMetadata->getAfterMethods(), true),
            'parameters' => var_export($iteration->getVariant()->getParameterSet()->getArrayCopy(), true),
            'warmup' => $iteration->getVariant()->getWarmup() ?: 0,
            'extension_path' => __DIR__ . '/../..',
        ];
        $payload = $this->launcher->payload(__DIR__ . '/template/execute.template', $tokens);

        $result = $payload->launch();

        $config['callback']($iteration, $result);

        $iteration->setResult(new TimeResult($result['cost']['wt']));
        $iteration->setResult(new MemoryResult($result['cost']['pmu'], $result['cost']['mu'], $result['cost']['mu']));

        return $result;
    }

    public function executeMethods(BenchmarkMetadata $benchmark, array $methods)
    {
        $tokens = [
            'class' => $benchmark->getClass(),
            'file' => $benchmark->getPath(),
            'methods' => var_export($methods, true),
        ];

        $payload = $this->launcher->payload(__DIR__ . '/template/execute_methods.template', $tokens);
        $payload->launch();
    }

    public function healthCheck()
    {
        $payload = $this->launcher->payload(__DIR__ . '/template/health_check.template', []);
        $payload->launch();
    }

    public function configure(OptionsResolver $options)
    {
        $options->setDefaults([
            'callback' => function () {
            },
        ]);
    }
}
