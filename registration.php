<?php

declare(strict_types=1);

use Magento\Framework\Component\ComponentRegistrar;

ComponentRegistrar::register(
    ComponentRegistrar::MODULE,
    'Memegento_Mage',
    __DIR__
);

if (!class_exists('Mage')) {
    require_once __DIR__ . '/Mage.php';
}
