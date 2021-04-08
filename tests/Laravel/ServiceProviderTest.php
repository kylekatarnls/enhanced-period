<?php

namespace Tests\Carbon\Laravel;

use Carbon\CarbonPeriod;
use EnhancedPeriod\Laravel\ServiceProvider;
use PHPUnit\Framework\TestCase;
use Spatie\Period\Period;

class ServiceProviderTest extends TestCase
{
    public function testBoot(): void
    {
        include_once __DIR__.'/ServiceProvider.php';
        $service = new ServiceProvider();
        $message = null;

        CarbonPeriod::macro('toEnhancedPeriod', null);

        try {
            CarbonPeriod::create('2019-04-08', '2019-04-14')->toEnhancedPeriod();
        } catch (\BadMethodCallException $e) {
            $message = $e->getMessage();
        }

        $this->assertSame('Method toEnhancedPeriod does not exist.', $message);

        $service->boot();

        $this->assertInstanceOf(Period::class, CarbonPeriod::create('2019-04-08', '2019-04-14')->toEnhancedPeriod());

        $this->assertNull($service->register());
    }
}
