<?php
declare(strict_types=1);
namespace Polbeo\KirbyIndieConnectorRemoteStatus\Bridge;

use Kirby\Cms\App;
use Kirby\Http\Response;
use mauricerenck\IndieConnector\QueueHandler;
use mauricerenck\IndieConnector\WebmentionStats;

final class StatusController
{
    public function __construct(private readonly App $kirby, private readonly array $configuration) {}

    public function status(): Response
    {
        $request = $this->kirby->request();
        $path = 'indieconnector-remote-status/v1/status';
        $keyId = $request->header('X-Remote-Key');
        $timestamp = $request->header('X-Remote-Timestamp');
        $nonce = $request->header('X-Remote-Nonce');
        $signature = $request->header('X-Remote-Signature');
        $key = is_string($keyId) ? ($this->configuration['keys'][$keyId]['secret'] ?? null) : null;
        $validTime = is_string($timestamp) && ctype_digit($timestamp) && abs(time() - (int) $timestamp) <= 300;
        $payload = implode("\n", ['GET', $path, (string) $timestamp, (string) $nonce, hash('sha256', '')]);
        if (!is_string($key) || !is_string($nonce) || $nonce === '' || !$validTime || !is_string($signature) || !hash_equals(hash_hmac('sha256', $payload, $key), $signature)) {
            return new Response(json_encode(['error' => ['code' => 'unauthorized']]), 'application/json', 401);
        }
        $cache = $this->kirby->cache('polbeo.indieconnector-remote-status');
        $nonceKey = 'nonce.' . hash('sha256', $keyId . ':' . $nonce);
        if ($cache->get($nonceKey) !== null) {
            return new Response(json_encode(['error' => ['code' => 'replayed_request']]), 'application/json', 401);
        }
        $cache->set($nonceKey, true, 5);
        $year = max(2000, min(2100, (int) $request->get('year', date('Y'))));
        $month = max(1, min(12, (int) $request->get('month', date('n'))));
        $stats = new WebmentionStats();
        $queue = new QueueHandler();
        return new Response(json_encode(['data' => [
            'year' => $year, 'month' => $month,
            'summary' => $stats->getSummaryByMonth($year, $month) ?: [],
            'targets' => $stats->getTargets($year, $month) ?: [],
            'sources' => $stats->getSourceHosts($year, $month) ?: [],
            'authors' => $stats->getSourceAuthors($year, $month) ?: [],
            'sent' => $stats->getSentMentions($year, $month) ?: [],
            'itemsInQueue' => $queue->getQueuedItems(limit: 0, includeFailed: true)->count(),
        ]]), 'application/json');
    }
}
