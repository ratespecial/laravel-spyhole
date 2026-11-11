<?php

namespace Ratespecial\LaravelSpyhole\Tests\View;

use PHPUnit\Framework\Attributes\Test;
use Ratespecial\LaravelSpyhole\Tests\TestCase;

class EmbedSpyholeTest extends TestCase
{
    private const string ASSET_PREFIX = '/vendor/laravel-spyhole/';

    /**
     * Every script the embed view loads must ship in resources/assets, which is what `vendor:publish --tag=assets` copies.
     */
    #[Test]
    public function embedReferencesAssetsThatShip(): void
    {
        $html = view('laravel-spyhole::embed_spyhole')->render();

        preg_match_all('#src="[^"]*' . preg_quote(self::ASSET_PREFIX, '#') . '([^"]+)"#', $html, $matches);

        $this->assertSame(['rrweb-record.min.js', 'recording-handler.js'], $matches[1]);

        foreach ($matches[1] as $file) {
            $this->assertFileExists(__DIR__ . '/../../resources/assets/' . $file);
        }
    }

    /**
     * The recorder is @rrweb/record's UMD bundle, whose global is `rrwebRecord`.  rrweb v1's `rrweb` global no longer exists.
     */
    #[Test]
    public function handlerUsesTheRecordBundleGlobal(): void
    {
        $handler  = (string) file_get_contents(__DIR__ . '/../../resources/assets/recording-handler.js');
        $recorder = (string) file_get_contents(__DIR__ . '/../../resources/assets/rrweb-record.min.js');

        $this->assertStringContainsString('rrwebRecord.record({', $handler);
        $this->assertStringNotContainsString('rrweb.record(', $handler);
        $this->assertStringContainsString('g["rrwebRecord"]', $recorder);
    }

    #[Test]
    public function embedPassesItsConfiguration(): void
    {
        $html = view('laravel-spyhole::embed_spyhole', ['idValue' => 'my-id', 'type' => 'checkout'])->render();

        $this->assertStringContainsString('<input type="hidden" id="my-id" value="">', $html);
        $this->assertStringContainsString("type: 'checkout'", $html);
        $this->assertStringContainsString('defer src=', $html);
    }
}
