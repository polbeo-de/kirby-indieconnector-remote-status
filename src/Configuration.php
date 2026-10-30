<?php
declare(strict_types=1);
namespace Polbeo\KirbyIndieConnectorRemoteStatus;

final class Configuration
{
    public static function role(string $root, string $role): array
    {
        $fallback = is_file($root . '/config/local.php') ? require $root . '/config/local.php' : [];
        $all = option('polbeo.indieconnector-remote-status', $fallback['polbeo.indieconnector-remote-status'] ?? []);
        return is_array($all[$role] ?? null) ? $all[$role] : [];
    }
}
