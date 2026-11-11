<p align="center">
    <img src="https://kalizi.dev/spyhole_logo.png">
</p>
<p align="center">
    <a href="https://packagist.org/packages/kalizi/laravel-spyhole"><img src="https://img.shields.io/packagist/dt/kalizi/laravel-spyhole" alt="Total Downloads"></a>
    <a href="https://packagist.org/packages/kalizi/laravel-spyhole"><img src="https://img.shields.io/packagist/v/kalizi/laravel-spyhole" alt="Latest Stable Version"></a>
    <a href="https://packagist.org/packages/kalizi/laravel-spyhole"><img src="https://img.shields.io/packagist/l/kalizi/laravel-spyhole" alt="License"></a>
    <a href="https://scrutinizer-ci.com/g/kalizi/laravel-spyhole"><img src="https://img.shields.io/scrutinizer/g/kalizi/laravel-spyhole.svg?style=flat-square" alt="Quality Score"></a>
    <a href="https://travis-ci.com/kalizi/laravel-spyhole"><img src="https://api.travis-ci.com/kalizi/laravel-spyhole.svg?branch=main&status=passed" alt="Build Status"></a></a>
</p>

Laravel Spyhole is a user session recorder for the Laravel Framework. Laravel Spyhole is a wrapper for the JS Library [RRWeb](https://www.rrweb.io/) (Record and replay the web). It allows a simple way to embed the Recorder into your views and start recordings out-of-the-box.

## Installation

You can install the package via composer:

```bash
composer require ratespecial/laravel-spyhole
```

## Usage

After installation publish the config file:

``` php
php artisan vendor:publish --provider="Ratespecial\LaravelSpyhole\LaravelSpyholeServiceProvider"
```

In the configuration file you can set:

* **Session ID tracking**: this will make the package track the session ID from the Laravel built-in Session, if set to false, a random UUID will be generated for the user session.
* **User ID tracking**: this will make the package track the user ID with recordings.
* **Minimum sampling rate**: the minimum number of records to be recorded from frontend before sending. (Default: 50)

Finally, to start recording, just embed the recorder view to the view you want to record.

```php
@include("laravel-spyhole::embed_spyhole")
```

### How it works in short

As pointed before, spyhole uses RRWeb as its internal recorder.

In the `resources/assets` folder there are the published assets where you can find the RRWeb files and the built version of `recording-handler.js`, a JS script handling the RRWeb initialization and recording sendings.  

#### Bundled rrweb assets

The rrweb files are committed, minified copies of the npm packages' UMD bundles.  There is no build step.

| File | Source | Version |
| --- | --- | --- |
| `rrweb-record.min.js` | `@rrweb/record` `dist/record.umd.min.cjs` (UMD global `rrwebRecord`) | 2.2.1 |
| `rrweb-player.min.js` | `rrweb-player` `dist/rrweb-player.umd.min.cjs` (UMD global `rrwebPlayer`) | 2.2.1 |
| `rrweb-player.min.css` | `rrweb-player` `dist/style.min.css` | 2.2.1 |

To update them:

1. `npm pack @rrweb/record@<version> rrweb-player@<version>` in an empty directory and extract the tarballs.
2. Copy the files above into `resources/assets`, keeping the `.min.js` names.  npm ships them as `.umd.min.cjs`, which web servers may not serve with a JavaScript MIME type.
3. Strip the trailing `//# sourceMappingURL=...` (JS) and `/*# sourceMappingURL=... */` (CSS) comments, since the `.map` files are not shipped.
4. Update this table and `CHANGELOG.md`.

Only the recorder (`@rrweb/record`) is loaded on recorded pages.  The player is for replay pages only.  Recordings made with rrweb 1.x replay in the 2.x player.

The recordings are posted to a route named `spyhole.store-entry` (whose URL is `/spyhole-api/record`). Recordings are accessible via the model `Ratespecial\LaravelSpyhole\Models\SessionRecording` with properties: ID, path, recordings (an array stored gzipped and base 64 encoded) and user ID.

### Testing

``` bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email me@kalizi.dev instead of using the issue tracker.

## Credits

- [Andrea Bondì](https://github.com/kalizi)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Laravel Package Boilerplate

This package was generated using the [Laravel Package Boilerplate](https://laravelpackageboilerplate.com).
