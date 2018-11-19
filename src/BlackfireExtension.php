<?php

namespace Fbourigault\PhpBench\Extension\Blackfire;

use Fbourigault\PhpBench\Extension\Blackfire\Executor\BlackfireExecutor;
use PhpBench\DependencyInjection\Container;
use PhpBench\DependencyInjection\ExtensionInterface;
use Fbourigault\PhpBench\Extension\Blackfire\Command\ProfileCommand;

final class BlackfireExtension implements ExtensionInterface
{
    public function load(Container $container)
    {
        $container->register('blackfire.command.profile', function (Container $container) {
            return new ProfileCommand(
                $container->get('console.command.handler.runner')
            );
        }, ['console.command' => []]);

        $container->register('blackfire.executor.blackfire', function (Container $container) {
            return new BlackfireExecutor(
                $container->get('benchmark.remote.launcher')
            );
        }, ['benchmark_executor' => ['name' => 'blackfire']]);

        $container->mergeParameter('executors', [
            'blackfire' => [
                'executor' => 'blackfire',
            ],
        ]);
    }

    public function getDefaultConfig()
    {
        return [];
    }
}
