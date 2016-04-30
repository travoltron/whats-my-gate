<?php

use Carbon\Carbon;
use App\Http\Controllers\NjbusController;

class WhatsMyGateTest extends TestCase
{
    /**
     * Test empty request handled
     * @return void
     */
    public function testEmptyRequest()
    {
        $response = $this->call('POST', '/endpoint', ['Body' => '']);
        $this->assertEquals(417, $response->status());
    }

    public function testInvalidWindow()
    {
        $njbus = new NjbusController();
        $this->assertNull($njbus->getWindow(Carbon::createFromTime(4, 0, 0, 'America/New_York')));
    }

    public function testValidWindow()
    {
        $njbus = new NjbusController();
        $this->assertNotNull($njbus->getWindow(Carbon::createFromTime(14, 0, 0, 'America/New_York')));
    }

    public function testInvalidBus()
    {
        $njbus = new NjbusController();
        $this->assertNull($njbus->getGate('FAKE', 0));
    }
    public function testValidBus()
    {
        $njbus = new NjbusController();
        $this->assertNull($njbus->getGate('FAKE', 0));
    }

}
