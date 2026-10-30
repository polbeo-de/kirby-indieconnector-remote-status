<?php
declare(strict_types=1);
use Kirby\Cms\App as Kirby;
use Polbeo\KirbyIndieConnectorRemoteStatus\Bridge\StatusController;

spl_autoload_register(static function (string $class): void {
    $prefix = 'Polbeo\\KirbyIndieConnectorRemoteStatus\\';
    if (str_starts_with($class, $prefix)) require __DIR__ . '/src/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
});
Kirby::plugin('polbeo/kirby-indieconnector-remote-status', [
    'panel' => ['js' => 'index.js'],
    'translations' => ['en' => ['polbeo.indie.remote.title' => 'Remote webmentions', 'polbeo.indie.remote.refresh' => 'Refresh', 'polbeo.indie.remote.unavailable' => 'The remote source is unavailable.'], 'de' => ['polbeo.indie.remote.title' => 'Remote-Webmentions', 'polbeo.indie.remote.refresh' => 'Aktualisieren', 'polbeo.indie.remote.unavailable' => 'Die Remote-Quelle ist nicht erreichbar.']],
    'areas' => ['remote-webmentions' => ['label' => 'Remote-Webmentions', 'icon' => 'live', 'menu' => true, 'link' => 'remote-webmentions', 'views' => [['pattern' => 'remote-webmentions', 'action' => fn (): array => ['component' => 'k-remote-webmentions-view', 'title' => 'Remote-Webmentions']]]]],
    'api' => ['routes' => [[
        'pattern' => 'indieconnector-remote-status/sources', 'method' => 'GET', 'action' => function (): array {
            if (kirby()->user() === null) throw new RuntimeException('Panel authentication required');
            $config = \Polbeo\KirbyIndieConnectorRemoteStatus\Configuration::role(__DIR__, 'console');
            $sources = $config['sources'] ?? (isset($config['source']) ? ['hinterhof' => $config['source']] : []);
            $result = []; foreach ($sources as $id => $source) if (is_string($id) && is_array($source)) $result[] = ['id' => $id, 'label' => $source['label'] ?? $id, 'live' => $source['live'] ?? false];
            return $result;
        },
    ], [
        'pattern' => 'indieconnector-remote-status/(:any)/status', 'method' => 'GET', 'action' => function (string $sourceId): array {
            if (kirby()->user() === null) throw new RuntimeException('Panel authentication required');
            $config = \Polbeo\KirbyIndieConnectorRemoteStatus\Configuration::role(__DIR__, 'console');
            $sources = $config['sources'] ?? (isset($config['source']) ? ['hinterhof' => $config['source']] : []);
            $source = $sources[$sourceId] ?? []; if (($config['enabled'] ?? false) !== true || !is_array($source)) throw new RuntimeException('Remote source unavailable');
            $year = (int) kirby()->request()->get('year', date('Y')); $month = (int) kirby()->request()->get('month', date('n'));
            return (new \Polbeo\KirbyIndieConnectorRemoteStatus\Console\StatusClient())->get($source, $year, $month);
        },
    ]]],
    'routes' => [[
        'pattern' => 'indieconnector-remote-status/v1/status', 'method' => 'GET', 'action' => function () {
            $config = \Polbeo\KirbyIndieConnectorRemoteStatus\Configuration::role(__DIR__, 'bridge');
            if (($config['enabled'] ?? false) !== true) return new \Kirby\Http\Response(json_encode(['error' => ['code' => 'disabled']]), 'application/json', 404);
            return (new StatusController(kirby(), $config))->status();
        },
    ]],
]);
