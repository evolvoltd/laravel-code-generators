<?php

namespace Tests\Feature\Dummy;

use App\Services\Integrations\DummyService;
use Mockery\MockInterface;
use Tests\TestCase;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class DummyServiceTest extends TestCase
{
    public function testMockWebService()
    {
        $this->partialMock('overload:' . DummyService::class, function (MockInterface $mock) {
            $mock->shouldReceive('call')
                ->andReturn(['test' => 'test']);
            $mock->shouldReceive('getData')
                ->andReturn(['test' => 'test']);
        });

        $this->assertEquals((new DummyService)->getData(), ['test' => 'test']);
    }
}
