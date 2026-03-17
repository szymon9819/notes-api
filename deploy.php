<?php

declare(strict_types=1);

namespace Deployer;

use RuntimeException;

require 'recipe/laravel.php';

function deployEnv(string $key, ?string $default = null): string
{
    $value = getenv($key);

    if ($value !== false && $value !== '') {
        return $value;
    }

    if ($default !== null) {
        return $default;
    }

    throw new RuntimeException("Missing required deploy configuration: {$key}");
}

set('application', 'notes-api');
set('repository', deployEnv('DEPLOY_REPOSITORY'));
set('keep_releases', 5);
set('http_user', deployEnv('DEPLOY_HTTP_USER', 'www-data'));

$production = host('production')
    ->setHostname(deployEnv('DEPLOY_HOST'))
    ->setRemoteUser(deployEnv('DEPLOY_REMOTE_USER', 'ubuntu'))
    ->setDeployPath(deployEnv('DEPLOY_PATH', '/var/www/notes-api'));

$identityFile = getenv('DEPLOY_IDENTITY_FILE');

if ($identityFile !== false && $identityFile !== '') {
    $production->set('identity_file', $identityFile);
}

task('artisan:reload', function (): void {
    run('cd {{release_path}} && {{bin/php}} artisan reload --no-interaction');
});

after('deploy:success', 'artisan:reload');
after('deploy:failed', 'deploy:unlock');
