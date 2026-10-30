# Kirby IndieConnector Remote Status

> Pre-release. Not yet published.

A Kirby 5 plugin that displays a remote IndieConnector webmention status
overview in a local Kirby Panel. The remote bridge is HMAC-authenticated and
read-only; it uses IndieConnector's public statistics and queue APIs rather
than accessing its SQLite database directly.

The Panel interface uses Kirby translations and provides English and German
catalogs.

## Status

The first implementation will cover the existing IndieConnector overview:
monthly totals, target pages, source platforms, authors, sent mentions, and
queue size. Remote queue processing is intentionally out of scope.
