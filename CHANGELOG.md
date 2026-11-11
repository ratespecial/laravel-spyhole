# Changelog

All notable changes to `laravel-spyhole` will be documented in this file

## 3.0.0 - 2026-11-11

Upgrades rrweb from 1.1.3 to 2.2.1.

- **Breaking:** the recorder is now the `@rrweb/record` 2.2.1 bundle, published as `rrweb-record.min.js` with the UMD global `rrwebRecord`.  `rrweb.min.js` and the unused `rrweb.min.css` are removed, and `recording-handler.js` calls `rrwebRecord.record()`.
- **Breaking:** `rrweb-player.min.js` and `rrweb-player.min.css` are now `rrweb-player` 2.2.1.  `new rrwebPlayer({ target, props })` is unchanged, and rrweb 1.x recordings still replay.
- Re-publish the assets after upgrading: `php artisan vendor:publish --provider="Ratespecial\LaravelSpyhole\LaravelSpyholeServiceProvider" --tag=assets --force`, then remove the old `public/vendor/laravel-spyhole/rrweb.min.js` and `rrweb.min.css`.
- The embed view's script URLs carry `?v=3.0.0`, so browsers don't pair a cached 2.x `recording-handler.js` with the new recorder.  Bump it with each asset change.
- `recording-handler.js` line endings are normalised to LF.
- The recorder bundle is smaller: 24 KB gzipped, down from 34 KB.

## 1.0.0 - 201X-XX-XX

- initial release
