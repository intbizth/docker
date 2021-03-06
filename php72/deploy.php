<?php

namespace Deployer;

require_once 'recipe/common.php';
require_once 'recipe/cloudflare.php';
require_once 'recipe/cachetool.php';

set('writable_chmod_mode', '0777');
set('cachetool', $_ENV['CACHE_TOOL_CGI_URL'] ?? '127.0.0.1:9000');
set('cloudflare', [
    'email' => $_ENV['CF_EMAIL'] ?? null,
    'api_key' => $_ENV['CF_API_KEY'] ?? null,
    'domain' => $_ENV['CF_DOMAIN'] ?? null,
]);

set('shared_dirs', explode(',', $_ENV['SHARED_DIRS'] ?? 'var/log,var/sessions,var/media'));
set('shared_files', ['.env']);
set('writable_dirs', ['var']);

set('bin/console', function () {
    return parse('{{bin/php}} {{release_path}}/bin/console --no-interaction');
});

desc('Migrate database');
task('database:migrate', function () {
    run('{{bin/console}} doctrine:migrations:migrate --allow-no-migration');
});

desc('Clear cache');
task('deploy:cache:clear', function () {
    run('{{bin/console}} cache:clear --no-warmup');
});

desc('Warm up cache');
task('deploy:cache:warmup', function () {
    run('{{bin/console}} cache:warmup');
});

desc('Deploy project');
task('deploy', [
    'deploy:info',
    'deploy:prepare',
    'deploy:lock',
    'deploy:release',
    'deploy:update_code',
    'deploy:shared',
    'deploy:vendors',
    'deploy:cache:clear',
    'deploy:cache:warmup',
    'deploy:writable',
    'deploy:symlink',
    'deploy:unlock',
    'database:migrate',
    'cleanup',
]);

after('deploy', 'success');

desc('Setup database');
task('database:setup', function () {
    // default
    run('{{bin/console}} doctrine:database:drop --connection=default --force --if-exists');
    run('{{bin/console}} doctrine:database:create --connection=default');
    run('{{bin/console}} doctrine:schema:update --dump-sql --force');
    // media
    run('{{bin/console}} doctrine:database:drop --connection=media --force --if-exists');
    run('{{bin/console}} doctrine:database:create --connection=media');
    run('{{bin/console}} doctrine:phpcr:init:dbal --force');
    run('{{bin/console}} doctrine:phpcr:repository:init');
    // fixture
    run('{{bin/console}} sylius:fixtures:load prod');
});

localhost('prod')
    ->set('writable_mode', 'chmod')
    ->set('deploy_path', '/home/www-data')
    ->set('repository', $_ENV['REPOSITORY'])
    ->set('branch', $_ENV['BRANCH'] ?? 'build')
;

/*
task('yarn:build', function () {
    run('cd {{release_path}} && yarn install && yarn run build');
});

after('deploy:vendors', 'yarn:build');
*/

desc('Clearing APCu system cache');
task('cachetool:clear:apcu', function () {
    $releasePath = get('release_path');
    $options = get('cachetool');

    if (strlen($options)) {
        $options = "--fcgi={$options}";
    }

    cd($releasePath);
    $hasCachetool = run("if [ -e $releasePath/{{bin/cachetool}} ]; then echo 'true'; fi");

    if ('true' !== $hasCachetool) {
        run("curl -sO https://gordalina.github.io/cachetool/downloads/{{bin/cachetool}}");
    }

    run("{{bin/php}} {{bin/cachetool}} apcu:cache:clear system {$options}");
});

task('test', function() {
var_dump($_ENV);
});

task('yarn:build', function() {
    run('node -v');
    run('yarn -v');
    run('yarn run build');
});

if ($_ENV['CF_API_KEY'] ?? null) {
    after('deploy:cache:clear', 'deploy:cloudflare');
}

if (1 === intval($_ENV['OPcode_ON'] ?? 0)) {
    after('deploy:cache:clear', 'cachetool:clear:opcache');
}

if (1 === intval($_ENV['APC_ON'] ?? 0)) {
    after('deploy:cache:clear', 'cachetool:clear:apc');
}

if (1 === intval($_ENV['APCu_ON'] ?? 0)) {
    after('deploy:cache:clear', 'cachetool:clear:apcu');
}

if (1 === intval($_ENV['DATA_SETUP'] ?? 0)) {
    after('deploy:vendors', 'database:setup');
}

if (1 === intval($_ENV['YARN_BUILD'] ?? 0)) {
    after('deploy:vendors', 'yarn:build');
}
