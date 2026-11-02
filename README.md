# Kirby IndieConnector Remote Status

> Status: maintained plugin. Release artifacts and version tags are published
> through the repository release process.

A Kirby 5 plugin that displays a remote IndieConnector webmention status
overview in a local Kirby Panel. The remote bridge is HMAC-authenticated and
read-only; it uses IndieConnector's public statistics and queue APIs rather
than accessing its SQLite database directly.

The Panel interface uses Kirby translations and provides English and German
catalogs.

## Status

The plugin provides the existing IndieConnector overview remotely: monthly
totals, target pages, source platforms, authors, sent webmentions, and queue
size. Remote queue processing is intentionally out of scope.

## Requirements

- PHP >= 8.2
- Kirby 5
- [IndieConnector](https://github.com/mauricerenck/kirby-indieconnector) on
  the remote source

## Installation

```sh
composer require polbeo/kirby-indieconnector-remote-status
```

## Roles and configuration

The remote bridge and local Panel console are independently enabled. Keep real
credentials in an ignored host configuration file and commit placeholders
only:

```php
<?php

return [
    'polbeo.indieconnector-remote-status' => [
        'bridge' => [
            'enabled' => true,
            'keys' => [
                'local-panel' => ['secret' => 'replace-with-a-random-secret'],
            ],
        ],
        'console' => [
            'enabled' => true,
            'sources' => [
                'production' => [
                    'label' => 'Production',
                    'baseUrl' => 'https://example.invalid',
                    'keyId' => 'local-panel',
                    'secret' => 'replace-with-the-matching-random-secret',
                    'live' => true,
                ],
            ],
        ],
    ],
];
```

For local development, the same structure may be returned from the ignored
plugin file `config/local.php`. Host configuration takes precedence. Use a
different random secret for every source. Secrets must never be exposed to
Panel JavaScript, URLs, query parameters, responses, or logs.

## Security model

The bridge exposes a read-only, versioned status endpoint. Requests are
authenticated with HMAC over the method, path, timestamp, nonce, and request
body. Unknown keys, stale timestamps, nonce replays, and invalid signatures
are rejected. Endpoints are disabled by default.

The Panel communicates only with authenticated Kirby Panel routes; the
server-side client performs all remote requests. This keeps source secrets out
of the browser.

## Panel operation

The local Panel area is called **Remote Webmentions** and uses Kirby's English
and German translation catalogs. Editors choose a configured source and refresh
the read-only overview. The summary represents received webmentions only:

- **Target pages**, **Sources**, and **Authors** appear after an incoming
  webmention or collected social response has been processed.
- **Sent webmentions** lists webmentions sent by the site to third-party
  targets; it does not list posts published on social networks.
- Registered social posts with no response are intentionally absent from this
  overview because they are not webmentions yet.

## Compatibility

| Component | Supported |
| --- | --- |
| PHP | >= 8.2 |
| Kirby | 5.x |
| IndieConnector | installed on the remote source |
