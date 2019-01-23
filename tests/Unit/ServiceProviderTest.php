<?php

namespace Laravel\InfluxDB\Tests\Unit;

use Illuminate\Filesystem\Filesystem;
use InfluxDB\Database;
use InfluxDB\Driver\Guzzle;
use InfluxDB\Driver\UDP;
use Laravel\InfluxDB\ServiceProvider;
use Laravel\InfluxDB\Tests\NonPublicAccessibleTrait;
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
    use NonPublicAccessibleTrait;

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
     * Test config values are merged
     */
    public function testDefaultConfigValues()
    {
        $config = config('influxdb');

        $this->assertIsArray($config);

        $this->assertArrayHasKey('database', $config);
        $this->assertIsArray($config['database']);

        $this->assertArrayHasKey('dsn', $config['database']);
        $this->assertArrayHasKey('protocol', $config['database']);
        $this->assertArrayHasKey('host', $config['database']);
        $this->assertArrayHasKey('port', $config['database']);
        $this->assertArrayHasKey('username', $config['database']);
        $this->assertArrayHasKey('password', $config['database']);
        $this->assertArrayHasKey('dbname', $config['database']);
        $this->assertArrayHasKey('ssl', $config['database']);
        $this->assertArrayHasKey('verify_ssl', $config['database']);
        $this->assertArrayHasKey('timeout', $config['database']);

        $this->assertArrayHasKey('queue', $config);
        $this->assertIsArray($config['queue']);

        $this->assertArrayHasKey('enable', $config['queue']);
        $this->assertArrayHasKey('name', $config['queue']);
        $this->assertEquals(false, $config['queue']['enable']);
        $this->assertEquals('default', $config['queue']['name']);

        $this->assertArrayHasKey('logging', $config);
    }

    /**
     * Test manager is bound in application container
     */
    public function testBoundDatabase()
    {
        $classes = (new ServiceProvider($this->app))->provides();

        foreach ($classes as $class) {
            $this->assertTrue($this->app->bound($class));
            $this->assertInstanceOf($class, $this->app->get($class));
        }
    }

    /**
     * Test make InfluxDB instance from dsn
     * Expects return InfluxDB\Database instance with connection via https protocol
     */
    public function testMakeInfluxDBInstanceFromDSN()
    {
        $dsn = 'udp+influxdb://username:pass@localhost:4444/demo';

        config()->set('influxdb.database.dsn', $dsn);

        /** @var \InfluxDB\Database $instance */
        $instance = $this->app->make(Database::class);

        $this->assertInstanceOf(Database::class, $instance);
        $this->assertEquals('demo', $instance->getName());
        $this->assertInstanceOf(UDP::class, $instance->getClient()->getDriver());
    }

    /**
     * Test make InfluxDB instance with protocol http and option ssl is TRUE
     * Expects return InfluxDB\Database instance with connection via https protocol
     */
    public function testMakeInfluxDBInstanceWithSSL()
    {
        config()->set('influxdb.database.ssl', true);
        config()->set('influxdb.database.protocol', 'http');

        /** @var \InfluxDB\Database $instance */
        $instance = $this->app->make(Database::class);

        $this->assertInstanceOf(Database::class, $instance);
        $this->assertStringStartsWith('https://', $instance->getClient()->getBaseURI());
        $this->assertInstanceOf(Guzzle::class, $instance->getClient()->getDriver());
    }

    /**
     * Test make InfluxDB instance with DSN (without database) and database name
     * Expects return InfluxDB\Database instance with name is database name
     */
    public function testMakeInfluxDBInstanceWithDBName()
    {
        $dsn = 'udp+influxdb://username:pass@localhost:4444';
        $dbname = 'demo';

        config()->set('influxdb.database.dsn', $dsn);
        config()->set('influxdb.database.dbname', $dbname);

        /** @var \InfluxDB\Database $instance */
        $instance = $this->app->make(Database::class);

        $this->assertInstanceOf(Database::class, $instance);
        $this->assertEquals($dbname, $instance->getName());
    }
}
