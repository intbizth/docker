<?php

require '/var/etc/vendor/deployer/deployer/recipe/symfony4.php';
require '/var/etc/vendor/deployer/recipes/recipe/cloudflare.php';
require '/var/etc/vendor/deployer/recipes/recipe/cachetool.php';

use function Deployer\{
    after, desc, task, localhost, run, set, get, cd
};

set('cachetool', '127.0.0.1:9000');
set('cloudflare', [
    'email' => $_ENV['CF_EMAIL'] ?? null,
    'api_key' => $_ENV['CF_API_KEY'] ?? null,
    'domain' => $_ENV['CF_DOMAIN'] ?? null,
]);

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
    ->set('repository', $_ENV['repository'])
    ->set('branch', $_ENV['branch'] ?? 'build')
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

if ($_ENV['OPcode_ON'] ?? null) {
    after('deploy:cache:clear', 'cachetool:clear:opcache');
}

if ($_ENV['APC_ON'] ?? null) {
    after('deploy:cache:clear', 'cachetool:clear:apc');
}

if ($_ENV['APCu_ON'] ?? null) {
    after('deploy:cache:clear', 'cachetool:clear:apcu');
}

if ($_ENV['CF_API_KEY'] ?? null) {
    after('deploy:cache:clear', 'deploy:cloudflare');
}

if ($_ENV['SETUP_DB'] ?? null) {
    after('deploy:vendors', 'database:setup');
}

after('cleanup', 'database:migrate');
