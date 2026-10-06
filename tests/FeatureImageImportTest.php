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
}