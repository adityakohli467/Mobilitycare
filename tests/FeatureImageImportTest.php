<?php

use PHPUnit\Framework\TestCase;

class FeatureImageImportTest extends TestCase
{
    public function testFeatureImagePreviewUsesStorefrontPath(): void
    {
        $source = file_get_contents(dirname(__DIR__) . '/admin/view/template/catalog/product_form.twig');
        $this->assertSame(1, preg_match('/<img src="(\{\{ feature\.image .*?\}\})"/', $source, $matches));

        $twig = new Twig\Environment(new Twig\Loader\ArrayLoader(['preview' => $matches[1]]));
        $cases = [
            'catalog/productFeature/battery.webp' => 'https://example.test/image/catalog/productFeature/battery.webp',
            'catalog/productFeature/battery.jpg' => 'https://example.test/image/catalog/productFeature/battery.jpg',
            'https://trekinetic.com/wp-content/uploads/2026/09/Battery-Box.webp' => 'https://trekinetic.com/wp-content/uploads/2026/09/Battery-Box.webp',
            'http://example.test/battery.png' => 'http://example.test/battery.png',
            '' => 'view/image/placeholder.png'
        ];

        foreach ($cases as $path => $expected) {
            $this->assertSame($expected, $twig->render('preview', [
                'feature' => ['image' => $path],
                'base_url' => 'https://example.test/'
            ]));
        }
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testStorefrontResizesUploadedImagesToPublicUrls(): void
    {
        if (!function_exists('imagecreatefromwebp') || !function_exists('imagewebp')) {
            $this->markTestSkipped('GD WebP support is required.');
        }

        $baseDir = dirname(__DIR__);
        require_once $baseDir . '/system/engine/registry.php';
        require_once $baseDir . '/system/engine/model.php';
        require_once $baseDir . '/system/helper/utf8.php';
        require_once $baseDir . '/system/library/image.php';
        require_once $baseDir . '/catalog/model/tool/image.php';

        $directory = str_replace('\\', '/', sys_get_temp_dir()) . '/mobilitycare-image-test-' . uniqid() . '/';
        mkdir($directory);
        $directory = str_replace('\\', '/', realpath($directory)) . '/';
        define('DIR_IMAGE', $directory);

        $registry = new Registry();
        $request = new stdClass();
        $request->server = ['HTTPS' => true, 'HTTP_ACCEPT' => 'image/webp'];
        $registry->set('request', $request);
        $registry->set('config', new class {
            public function get($name)
            {
                return $name === 'config_ssl' ? 'https://example.test/' : 'http://example.test/';
            }
        });
        $model = new ModelToolImage($registry);
        $sourceImage = imagecreatetruecolor(12, 8);

        try {
            foreach (['webp' => 'imagewebp', 'png' => 'imagepng', 'jpg' => 'imagejpeg', 'gif' => 'imagegif'] as $extension => $writer) {
                $filename = 'feature image.' . $extension;
                $writer($sourceImage, $directory . $filename);

                foreach ([true, false] as $https) {
                    $request->server['HTTPS'] = $https;
                    $prefix = $https ? 'https://example.test/image/' : 'http://example.test/image/';

                    foreach ([[6, 6], [12, 8]] as $size) {
                        $url = $model->resize($filename, $size[0], $size[1]);
                        $this->assertStringStartsWith($prefix . 'cache/', $url);
                        $this->assertStringNotContainsString(' ', $url);
                        $cachedFile = $directory . rawurldecode(substr($url, strlen($prefix)));
                        $this->assertFileExists($cachedFile);
                        $dimensions = getimagesize($cachedFile);
                        $this->assertSame($size[0], $dimensions[0]);
                        $this->assertSame($size[1], $dimensions[1]);
                    }
                }
            }

            if (function_exists('imagebmp')) {
                imagebmp($sourceImage, $directory . 'feature image.bmp');
                foreach ([true, false] as $https) {
                    $request->server['HTTPS'] = $https;
                    $this->assertSame(($https ? 'https' : 'http') . '://example.test/image/feature%20image.bmp', $model->resize('feature image.bmp', 6, 6));
                }
            }
        } finally {
            imagedestroy($sourceImage);
            foreach (glob($directory . 'cache/*') ?: [] as $file) {
                unlink($file);
            }
            if (is_dir($directory . 'cache')) {
                rmdir($directory . 'cache');
            }
            foreach (glob($directory . '*') ?: [] as $file) {
                unlink($file);
            }
            rmdir($directory);
        }
    }
}