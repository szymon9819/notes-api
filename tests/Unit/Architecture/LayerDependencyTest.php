<?php

declare(strict_types=1);

namespace Tests\Unit\Architecture;

use App\Application\Common\CQRS\Command;
use App\Application\Common\CQRS\CommandHandler;
use App\Application\Common\CQRS\Query;
use App\Application\Common\CQRS\QueryHandler;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

final class LayerDependencyTest extends TestCase
{
    #[Test]
    public function domain_layer_does_not_depend_on_framework_or_outer_layers(): void
    {
        $files = $this->phpFilesIn($this->appDirectory('Domain'));

        $this->assertFilesDoNotContain($files, [
            'Illuminate\\',
            'Laravel\\',
            'App\\Application\\',
            'App\\Infrastructure\\',
            'App\\Persistence\\',
        ]);
    }

    #[Test]
    public function application_layer_does_not_depend_on_framework_or_http_infrastructure_details(): void
    {
        $files = $this->phpFilesIn($this->appDirectory('Application'));

        $this->assertFilesDoNotContain($files, [
            'Illuminate\\',
            'Laravel\\',
            'App\\Infrastructure\\',
            'App\\Persistence\\',
        ]);
    }

    #[Test]
    public function commands_queries_and_handlers_follow_the_cqrs_contracts(): void
    {
        foreach ($this->applicationClassNames() as $className) {
            if ($className !== Command::class && str_ends_with($className, 'Command')) {
                $this->assertTrue(is_subclass_of($className, Command::class), sprintf('Command [%s] must implement [%s].', $className, Command::class));
            }

            if ($className !== Query::class && str_ends_with($className, 'Query')) {
                $this->assertTrue(is_subclass_of($className, Query::class), sprintf('Query [%s] must implement [%s].', $className, Query::class));
            }

            if ($className !== CommandHandler::class && str_contains($className, '\\Commands\\') && str_ends_with($className, 'Handler')) {
                $this->assertTrue(is_subclass_of($className, CommandHandler::class), sprintf('Command handler [%s] must implement [%s].', $className, CommandHandler::class));
            }

            if ($className !== QueryHandler::class && str_contains($className, '\\Queries\\') && str_ends_with($className, 'Handler')) {
                $this->assertTrue(is_subclass_of($className, QueryHandler::class), sprintf('Query handler [%s] must implement [%s].', $className, QueryHandler::class));
            }
        }
    }

    /**
     * @param  list<string>  $files
     * @param  list<string>  $forbiddenNamespaces
     */
    private function assertFilesDoNotContain(array $files, array $forbiddenNamespaces): void
    {
        foreach ($files as $file) {
            $contents = file_get_contents($file);

            $this->assertNotFalse($contents);

            foreach ($forbiddenNamespaces as $forbiddenNamespace) {
                $this->assertStringNotContainsString($forbiddenNamespace, $contents, sprintf('File [%s] must not depend on [%s].', $file, $forbiddenNamespace));
            }
        }
    }

    /**
     * @return list<string>
     */
    private function phpFilesIn(string $path): array
    {
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));
        $files = [];

        foreach ($iterator as $file) {
            if (!$file instanceof SplFileInfo) {
                continue;
            }

            if (!$file->isFile()) {
                continue;
            }

            if ($file->getExtension() !== 'php') {
                continue;
            }

            $files[] = $file->getPathname();
        }

        sort($files);

        return $files;
    }

    /**
     * @return list<string>
     */
    private function applicationClassNames(): array
    {
        $basePath = $this->appDirectory('Application') . DIRECTORY_SEPARATOR;
        $classes = [];

        foreach ($this->phpFilesIn($this->appDirectory('Application')) as $file) {
            $relativePath = substr($file, strlen($basePath));

            $classes[] = 'App\\Application\\' . str_replace(
                ['/', '.php'],
                ['\\', ''],
                $relativePath,
            );
        }

        sort($classes);

        return $classes;
    }

    private function appDirectory(string $path = ''): string
    {
        return dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'app' . ($path === '' ? '' : DIRECTORY_SEPARATOR . $path);
    }
}
