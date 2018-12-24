<?php

$sami = new Sami\Sami(__DIR__.'/src', array(
    'theme' => 'detrack',
    'title' => 'Detrack Core PHP API',
    'build_dir' => __DIR__.'/docs/build/',
    'cache_dir' => __DIR__.'/docs/cache/',
    'remote_repository' => new Sami\RemoteRepository\GitHubRemoteRepository('detrack/detrack-core-php', dirname(__DIR__)),
    'default_opened_level' => 2,
));

$templates = $sami['template_dirs'];
$templates[] = __DIR__.'/docs/themes/';

$sami['template_dirs'] = $templates;

return $sami;
