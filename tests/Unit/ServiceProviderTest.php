<?php

namespace Laravel\InfluxDB\Tests\Unit;

use Illuminate\Filesystem\Filesystem;
use InfluxDB\Database;
use Laravel\InfluxDB\Tests\TestCase;

/**
 * Class ServiceProviderTest
 *
 * @package     Laravel\InfluxDB\Tests\Unit
 * @author      Oanh Nguyen <oanhnn.bk@gmail.com>
 * @license     The MIT license
 */
class ServiceProviderTest extends TestCase
{
    /**
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    /**
     * Set up before test
     */
    protected function setUp()
    {
        parent::setUp();

        $this->files = new Filesystem();
    }

    /**
     * Clear up after test
     */
    protected function tearDown()
    {
        $this->files->delete([
            $this->app->configPath('influxdb.php'),
        ]);

        parent::tearDown();
    }

    /**
     * Test file influxdb.php is existed in config directory after run
     *
     * php artisan vendor:publish --provider="Laravel\\InfluxDB\\ServiceProvider" --tag=config
     */
    public function testPublishVendorConfig()
    {
        $sourceFile = dirname(dirname(__DIR__)) . '/config/influxdb.php';
        $targetFile = config_path('influxdb.php');

        $this->assertFileNotExists($targetFile);

        $this->artisan('vendor:publish', [
            '--provider' => 'Laravel\\InfluxDB\\ServiceProvider',
            '--tag' => 'config',
        ]);

        $this->assertFileExists($targetFile);
        $this->assertEquals(file_get_contents($sourceFile), file_get_contents($targetFile));
    }

    /**
     * Test settings override some config values
     */
    public function testOverrideConfigValues()
    {
        // TODO
        $this->assertTrue(true);
    }

    /**
     * Test config values are merged
     */
    public function testDefaultConfigValues()
    {
        // TODO: default driver
    }

    /**
     * Test manager is bound in application container
     */
    public function testBoundDatabase()
    {
        $this->assertTrue($this->app->bound(Database::class));
        $this->assertInstanceOf(Database::class, $this->app->get(Database::class));
    }
}
