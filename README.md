# Memegento - Mage

<a href="https://www.buymeacoffee.com/memegento" target="_blank"><img src="https://www.buymeacoffee.com/assets/img/custom_images/orange_img.png" alt="Buy Me A Coffee" style="height: 41px !important;width: 174px !important;box-shadow: 0px 3px 2px 0px rgba(190, 190, 190, 0.5) !important;-webkit-box-shadow: 0px 3px 2px 0px rgba(190, 190, 190, 0.5) !important;" ></a>

This module adds the legendary Mage class from Magento 1 to Magento 2. Now developers can write Magento 1-like code in Magento 2 or event port Magento 1 code to Magento 2 without any changes. 

**Pull requests from the community are welcomed!**

## Requirements

- PHP 7.4+
- Magento 2 CE or EE installation

## Installation

```bash
composer require memegento/mage
```

## Usage

Mage class provides some basic methods to interact with Magento 2 as if it were Magento 1.

```php

$productModelLegacyWay = Mage::getModel('catalog/product');

$productModelNewWay = Mage::getModel(\Magento\Catalog\Model\Product::class);

$productUrl = Mage::getUrl('catalog/product/view', ['product_id' => $productId]);

Mage::log('Some message', ['context_field' => 'value'], Zend_Log::INFO);
Mage::throwException('Exception message');
```

## Compatibility

- 2.4.x Magento 2 Open Source or Commerce Edition
