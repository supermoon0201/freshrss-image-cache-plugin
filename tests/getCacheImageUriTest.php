<?php

declare(strict_types=1);

if (!class_exists('Override')) {
    #[Attribute(Attribute::TARGET_METHOD)]
    final class Override
    {
    }
}

if (!class_exists('Minz_Extension')) {
    class Minz_Extension
    {
    }
}

final class TestUserConf
{
    public string $image_cache_url = 'https://freshrss.leyang.cc/_imgcache/piccache?url=';
}

final class FreshRSS_Context
{
    private static ?TestUserConf $userConf = null;

    public static function userConf(): TestUserConf
    {
        if (self::$userConf === null) {
            self::$userConf = new TestUserConf();
        }

        return self::$userConf;
    }
}

require_once dirname(__DIR__) . '/extension.php';

function assertSameString(string $expected, string $actual, string $message): void
{
    if ($expected !== $actual) {
        fwrite(STDERR, $message . PHP_EOL . 'Expected: ' . $expected . PHP_EOL . 'Actual:   ' . $actual . PHP_EOL);
        exit(1);
    }
}

$cacheUrl = FreshRSS_Context::userConf()->image_cache_url;
$originUrl = 'https://example.com/image.jpg';
$cachedOriginUrl = $cacheUrl . rawurlencode($originUrl);

assertSameString(
    $cachedOriginUrl,
    ImageCacheExtension::getCacheImageUri($originUrl),
    'Remote image URLs should be rewritten to the configured cache URL.'
);

assertSameString(
    $cachedOriginUrl,
    ImageCacheExtension::getCacheImageUri($cachedOriginUrl),
    'Already cached image URLs must not be wrapped by the cache URL again.'
);

assertSameString(
    'data:image/png;base64,abc',
    ImageCacheExtension::getCacheImageUri('data:image/png;base64,abc'),
    'Data URLs should never be rewritten.'
);

fwrite(STDOUT, "getCacheImageUri tests passed\n");
