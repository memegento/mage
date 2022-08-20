<?php

declare(strict_types=1);

namespace Memegento\Mage\Test\Integration;

use Mage;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use PHPUnit\Framework\TestCase;
use ReflectionException;

class MageTest extends TestCase
{
    public function testMageClassExists(): void
    {
        static::assertTrue(class_exists(Mage::class), 'Mage class does not exist');
    }

    /**
     * @magentoConfigFixture current_store general/store_information/name Foo
     */
    public function testConfig(): void
    {
        $value = Mage::getStoreConfig('general/store_information/name');

        static::assertEquals('Foo', $value);
    }

    public function testLogs(): void
    {
        Mage::log('Test Log', ['foo' => 'bar']);

        static::assertTrue(true, 'Test gets to this point');
    }

    public function testGetModel(): void
    {
        $model = Mage::getModel(ScopeConfigInterface::class);

        static::assertInstanceOf(ScopeConfigInterface::class, $model);
    }

    public function testGetSingleton(): void
    {
        $singletonModel1 = Mage::getSingleton(ScopeConfigInterface::class);
        $singletonModel2 = Mage::getSingleton(ScopeConfigInterface::class);
        $newModel3 = Mage::getModel(ScopeConfigInterface::class);

        static::assertSame($singletonModel1, $singletonModel2);
        static::assertNotSame($singletonModel1, $newModel3);
    }

    public function testDispatchEvent(): void
    {
        Mage::dispatchEvent('test_event', ['foo' => 'bar']);

        static::assertTrue(true, 'Test gets to this point');
    }

    public function testRegistry(): void
    {
        $valInRegistry = 'bar';
        Mage::register('foo', $valInRegistry);

        static::assertEquals($valInRegistry, Mage::registry('foo'), 'Registry works');

        Mage::unregister('foo');

        static::assertNull(Mage::registry('foo'), 'Registry unregistered');
    }

    public function testException(): void
    {
        static::expectException(LocalizedException::class);
        static::expectExceptionMessage('Mage registry key "foo" already exists');

        Mage::register('foo', 'bar');
        Mage::register('foo', 'bar2');
    }

    public function testNameConversion(): void
    {
        $magento1Name = 'catalog/product';
        $model = Mage::getModel($magento1Name);

        static::assertInstanceOf(Product::class, $model);
    }

    public function testNotFoundModel(): void
    {
        static::expectException(ReflectionException::class);
        static::expectExceptionMessage('Class "Magento\Foo\Model\Bar" does not exist');

        Mage::getModel('foo/bar');
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testCodeSamplesFromMagento1(): void
    {
        $product = Mage::getModel('catalog/product')->load(1);
        $productUrl = $this->getFullProductUrl(1);

        static::assertEquals('Simple Product', $product->getName());
        static::assertEquals('simple', $product->getSku());
        static::assertStringContainsString('simple-product', $productUrl);
    }

    /**
     * This is the copy of the function from M1
     * \Mage_Catalog_Helper_Product::getProductUrl()
     *
     * @param $productId
     * @param $categoryId
     * @return mixed
     */
    private function getFullProductUrl($productId, $categoryId = null)
    {
        $product = Mage::getModel('catalog/product')->load($productId);
        if ($categoryId && $product->canBeShowInCategory($categoryId)) {
            $category = Mage::getModel('catalog/category')->load($categoryId);
            $product->setCategory($category);
        }

        return $product->getProductUrl();
    }
}
