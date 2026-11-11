<?php

namespace Ratespecial\LaravelSpyhole\Tests\View;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Ratespecial\LaravelSpyhole\Tests\TestCase;

class EmbedSpyholeTest extends TestCase
{
    private const string ASSET_PREFIX = '/vendor/laravel-spyhole/';

    private const string ASSET_VERSION = '3.0.1';

    /**
     * Every script the embed view loads must ship in resources/assets, which is what `vendor:publish --tag=assets` copies.
     */
    #[Test]
    public function embedReferencesAssetsThatShip(): void
    {
        $html = view('laravel-spyhole::embed_spyhole')->render();

        preg_match_all('#src="[^"]*' . preg_quote(self::ASSET_PREFIX, '#') . '([^"?]+)(\?v=[^"]+)?"#', $html, $matches);

        $this->assertSame(['rrweb-record.min.js', 'recording-handler.js'], $matches[1]);

        foreach ($matches[1] as $file) {
            $this->assertFileExists(__DIR__ . '/../../resources/assets/' . $file);
        }
    }

    /**
     * The handler keeps its URL across releases, so a version query stops a browser pairing a cached old handler with a
     * new recorder bundle.
     */
    #[Test]
    public function embedScriptsAreVersioned(): void
    {
        $html    = view('laravel-spyhole::embed_spyhole')->render();
        $pattern = '#' . preg_quote(self::ASSET_PREFIX, '#') . '[^"]+\?v=' . preg_quote(self::ASSET_VERSION, '#') . '"#';

        $this->assertSame(2, preg_match_all($pattern, $html));
    }

    /**
     * The recorder is @rrweb/record's UMD bundle, whose global is `rrwebRecord`.  rrweb v1's `rrweb` global no longer exists.
     */
    #[Test]
    public function handlerUsesTheRecordBundleGlobal(): void
    {
        $handler = (string) file_get_contents(__DIR__ . '/../../resources/assets/recording-handler.js');

        $this->assertStringContainsString('rrwebRecord.record({', $handler);
        $this->assertStringNotContainsString('rrweb.record(', $handler);
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function bundles(): array
    {
        return [
            'recorder' => ['rrweb-record.min.js', 'g["rrwebRecord"]'],
            'player'   => ['rrweb-player.min.js', 'g["rrwebPlayer"]'],
        ];
    }

    /**
     * 3.0.0 shipped both bundles broken: stripping every line that starts with `//# sourceMappingURL=` also removed a
     * line holding the end of the inlined image-bitmap worker's source string and the code after it.  Only each
     * bundle's final line may be stripped.
     */
    #[Test]
    #[DataProvider('bundles')]
    public function bundlesAreIntact(string $file, string $global): void
    {
        $bundle = (string) file_get_contents(__DIR__ . '/../../resources/assets/' . $file);

        $this->assertStringContainsString($global, $bundle);
        // The inlined worker's own sourcemap comment sits inside a string literal and must survive.
        $this->assertStringContainsString('sourceMappingURL=image-bitmap-data-url-worker', $bundle);
        $this->assertStringContainsString('self.Blob&&new Blob([', $bundle);
        // The UMD wrapper must close, and the bundle's own trailing sourcemap comment is gone.
        $this->assertStringEndsWith("return module.exports;\n}))\n", $bundle);
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
