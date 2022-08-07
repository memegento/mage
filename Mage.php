<?php

declare(strict_types=1);

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\UrlInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

final class Mage
{
    private static array $_registry = [];

    public static function getVersion(): string
    {
        return self::getOM()->get(ProductMetadataInterface::class)->getVersion();
    }

    public static function getEdition(): string
    {
        return self::getOM()->get(ProductMetadataInterface::class)->getEdition();
    }

    public static function register(string $key, $value, bool $graceful = false)
    {
        if (isset(self::$_registry[$key])) {
            if ($graceful) {
                return;
            }
            self::throwException('Mage registry key "'.$key.'" already exists');
        }
        self::$_registry[$key] = $value;
    }

    /**
     * Unregister a variable from register by key
     *
     * @param string $key
     */
    public static function unregister(string $key): void
    {
        if (isset(self::$_registry[$key])) {
            if (is_object(self::$_registry[$key]) && (method_exists(self::$_registry[$key], '__destruct'))) {
                self::$_registry[$key]->__destruct();
            }
            unset(self::$_registry[$key]);
        }
    }

    /**
     * Retrieve a value from registry by a key
     */
    public static function registry(string $key)
    {
        return self::$_registry[$key] ?? null;
    }

    public static function getModel(string $modelClass = '', array $arguments = []): object
    {
        if (str_contains($modelClass, '/')) {
            $modelClass = self::Magento1NameToMagento2Name($modelClass, 'Model');
        }

        return self::getOM()->create($modelClass, $arguments);
    }

    public static function getSingleton(string $modelClass = ''): object
    {
        return self::getOM()->get($modelClass);
    }

    public static function dispatchEvent(string $name, array $data = []): void
    {
        /** @var ManagerInterface $eventManager */
        $eventManager = self::getOM()->get(ManagerInterface::class);
        $eventManager->dispatch($name, $data);
    }

    public static function log(string $message, array $context = [], ?int $level = 100): void
    {
        $logger = self::getOM()->get(LoggerInterface::class);
        $logger->log($level, $message, $context);
    }

    /**
     * @param string $path The path through the tree of configuration values, e.g., 'general/store_information/name'
     * @param null|int|string|StoreInterface $store
     *
     * @return string|int|array|bool
     */
    public static function getStoreConfig(string $path, $store = null)
    {
        $copeConfig = self::getOM()->get(ScopeConfigInterface::class);

        return $copeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $store);
    }

    /**
     *
     * Retrieve config flag for store by path
     *
     * @param string $path
     * @param null|int|string|StoreInterface $store
     *
     * @return bool
     */
    public static function getStoreConfigFlag(string $path, $store = null): bool
    {
        $copeConfig = self::getOM()->get(ScopeConfigInterface::class);

        return $copeConfig->isSetFlag($path, ScopeInterface::SCOPE_STORE, $store);
    }

    public static function getUrl(string $route = '', array $params = []): string
    {
        return self::getOM()->get(UrlInterface::class)->getUrl($route, $params);
    }

    public static function getBaseUrl(string $type = UrlInterface::URL_TYPE_LINK, ?bool $secure = null): string
    {
        /** @var StoreManagerInterface $storeManager */
        $storeManager = self::getOM()->get(StoreManagerInterface::class);

        return $storeManager->getStore()->getBaseUrl($type, $secure);
    }

    public static function throwException(string $message): void
    {
        throw new LocalizedException(__($message));
    }

    private static function Magento1NameToMagento2Name(string $name, string $type = 'Model'): string
    {
        $typePart = sprintf('_%s_', $type);

        $name = str_replace('/', $typePart, $name);
        $nameByPart = explode('_', $name);

        foreach ($nameByPart as &$part) {
            $part = ucfirst($part);
        }

        return 'Magento\\' . implode('\\', $nameByPart);
    }

    private static function getOM(): ObjectManagerInterface
    {
        return ObjectManager::getInstance();
    }
}
