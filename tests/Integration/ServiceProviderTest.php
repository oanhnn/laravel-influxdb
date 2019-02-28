<?php

namespace Tests\Integration;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Log;
use InfluxDB\Database;
use InfluxDB\Driver\Guzzle;
use InfluxDB\Driver\UDP;
use Laravel\InfluxDB\Facades\InfluxDB;
use Laravel\InfluxDB\ServiceProvider;
use Tests\TestCase;

/**
 * Class ServiceProviderTest
 *
 * @package     Tests\Integration
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
    protected function setUp(): void
    {
        parent::setUp();

        $this->files = new Filesystem();
    }

    /**
     * Clear up after test
     */
    protected function tearDown(): void
    {
        $this->files->delete([
            $this->app->configPath('influxdb.php'),
        ]);

        parent::tearDown();
    }

    /**
     * Tests config file is existed in config directory after run
     *
     * php artisan vendor:publish --provider="Laravel\\InfluxDB\\ServiceProvider" --tag=laravel-influxdb-config
     *
     * @test
     */
    public function it_should_publish_vendor_config()
    {
        $sourceFile = dirname(dirname(__DIR__)) . '/config/influxdb.php';
        $targetFile = base_path('config/influxdb.php');

        $this->assertFileNotExists($targetFile);

        $this->artisan('vendor:publish', [
            '--provider' => 'Laravel\\InfluxDB\\ServiceProvider',
            '--tag' => 'laravel-influxdb-config',
        ]);

        $this->assertFileExists($targetFile);
        $this->assertEquals(file_get_contents($sourceFile), file_get_contents($targetFile));
    }

    /**
     * Test config values are merged
     *
     * @test
     */
    public function it_should_provides_default_config()
    {
        $config = config('influxdb');

        $this->assertTrue(is_array($config));

        $this->assertArrayHasKey('database', $config);
        $this->assertTrue(is_array($config['database']));

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
        $this->assertTrue(is_array($config['queue']));

        $this->assertArrayHasKey('enable', $config['queue']);
        $this->assertArrayHasKey('name', $config['queue']);
        $this->assertEquals(false, $config['queue']['enable']);
        $this->assertEquals('default', $config['queue']['name']);

        $this->assertArrayHasKey('logging', $config);
    }

    /**
     * Test manager is bound in application container
     *
     * @test
     */
    public function it_should_bound_some_services()
    {
        $classes = (new ServiceProvider($this->app))->provides();

        foreach ($classes as $class) {
            $this->assertTrue($this->app->bound($class));
            if (class_exists($class)) {
                $this->assertInstanceOf($class, $this->app->make($class));
            }
        }
    }

    /**
     * Test make InfluxDB instance from dsn
     *
     * Expects return InfluxDB\Database instance with connection via https protocol
     *
     * @test
     */
    public function it_can_make_influxdb_instance_from_dsn()
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
     *
     * Expects return InfluxDB\Database instance with connection via https protocol
     *
     * @test
     */
    public function it_can_make_influxdb_instance_with_ssl()
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
     *
     * Expects return InfluxDB\Database instance with name is database name
     *
     * @test
     */
    public function it_can_make_influxdb_instance_with_db_name()
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

    /**
     * Test make log driver
     *
     * @test
     */
    public function it_should_provides_log_driver()
    {
        // Ignore this test
        if (version_compare($this->app->version(), '5.6.0', '<')) {
            $this->markTestSkipped('This test only available on Laravel 5.6.0+');
        }

        config()->set('logging.channels.custom', [
            'driver' => 'influxdb',
            'name' => 'log-name',
            'level' => 'info',
            'bubble' => true,
        ]);

        InfluxDB::shouldReceive('writePoints')->once()->andReturnTrue();

        Log::channel('custom')->error('An error message');
    }
}
