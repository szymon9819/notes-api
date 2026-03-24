<?php

declare(strict_types=1);

namespace Tests\Feature\Lint;

use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;
use Tests\TestCase;

final class PintNegationSpacingTest extends TestCase
{
    public function test_pint_removes_spaces_after_not_operator_for_instanceof(): void
    {
        $fixtureDirectory = storage_path('framework/testing/pint');
        $fixturePath = $fixtureDirectory . '/negation-spacing.php';

        File::ensureDirectoryExists($fixtureDirectory);
        File::put($fixturePath, <<<'PHP'
<?php

declare(strict_types=1);

final class PintNegationFixture
{
    public function handle(object $handler): void
    {
        if (! $handler instanceof \stdClass) {
            return;
        }
    }
}
PHP);

        $process = new Process([
            PHP_BINARY,
            base_path('vendor/bin/pint'),
            '--config=' . base_path('pint.json'),
            '--no-interaction',
            $fixturePath,
        ]);

        try {
            $process->mustRun();
            $formattedContents = File::get($fixturePath);
        } finally {
            File::delete($fixturePath);
        }

        $this->assertStringContainsString('if (!$handler instanceof stdClass) {', $formattedContents);
    }
}
