<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Core\Cache;

use ACP3\Core\Environment\ApplicationMode;
use ACP3\Core\Environment\ApplicationPath;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\CacheProvider;
use Doctrine\Common\Cache\PhpFileCache;

class CacheDriverFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $appPathMock;
    /**
     * @var \ACP3\Core\Cache\CacheDriverFactory
     */
    private $cacheDriverFactory;

    protected function setUp()
    {
        $this->initializeMockObjects();
    }

    private function initializeMockObjects()
    {
        $this->appPathMock = $this->createMock(ApplicationPath::class);
    }

    private function initializeCacheDriverFactory($cacheDriver, $environment)
    {
        $this->cacheDriverFactory = new CacheDriverFactory(
            $this->appPathMock,
            $cacheDriver,
            $environment
        );
    }

    public function testCreateWithValidCacheDriver()
    {
        $this->setUpAppPathMockExpectations();

        $this->initializeCacheDriverFactory('Array', 'test');

        $this->assertInstanceOf(CacheProvider::class, $this->cacheDriverFactory->create('test'));
    }

    private function setUpAppPathMockExpectations()
    {
        $this->appPathMock->expects($this->any())
            ->method('getCacheDir')
            ->willReturn('cache/');
    }

    public function testCreateInvalidCacheDriverThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->setUpAppPathMockExpectations();

        $this->initializeCacheDriverFactory('LoremIpsum', 'test');

        $this->cacheDriverFactory->create('test');
    }

    public function testCreateForceArrayCacheForDeveloperMode()
    {
        $this->setUpAppPathMockExpectations();

        $this->initializeCacheDriverFactory('PhpFile', ApplicationMode::DEVELOPMENT);

        $this->assertInstanceOf(ArrayCache::class, $this->cacheDriverFactory->create('test'));
    }

    public function testCreateWithPhpFileCacheDriver()
    {
        $this->setUpAppPathMockExpectations();

        $this->initializeCacheDriverFactory('PhpFile', 'test');

        $this->assertInstanceOf(PhpFileCache::class, $this->cacheDriverFactory->create('test'));
    }
}
