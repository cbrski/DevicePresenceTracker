<?php

namespace Tests\Browser;

use App\Device;
use App\DeviceLink;
use App\DeviceLinkStateLog;
use Facebook\WebDriver\Exception\NoSuchElementException;
use Illuminate\Support\Facades\DB;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Tests\Helpers\AssertHelpers;

class BladeTemplateTest extends DuskTestCase
{
    use AssertHelpers;

    public function testDatabaseHasAnyData()
    {
        $this->assertDatabaseCountConditions((new Device())->getTable(), ['>=', 1]);
        $this->assertDatabaseCountConditions((new DeviceLink())->getTable(), ['>=', 1]);
        $this->assertDatabaseCountConditions((new DeviceLinkStateLog())->getTable(), ['>=', 1]);
    }

    /**
     * @throws \Throwable
     * @depends testDatabaseHasAnyData
     */
    public function testSeeNowOrAgo()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/');
            try {
                $text = $browser->text('div > div:nth-child(1) > div.card.bg-success > a > div > h3 > span.badge.badge-secondary');
                $t_ago = strstr($text, 'ago');
                $t_now = strstr($text, 'now');
                $this->assertTrue($t_ago || $t_now);
            }
            catch (NoSuchElementException $e)
            {
                $this->fail('Bootstrap card not visible.');
            }
        });
    }

    /**
     * @throws \Throwable
     * @depends testDatabaseHasAnyData
     */
    public function testSeeDeviceName()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/');
            try {
                $text = $browser->text('div > div:nth-child(1) > div.card > a > div > h3 > span.badge.badge-light');
                $this->assertTrue(strlen($text)>=1);
            }
            catch (NoSuchElementException $e)
            {
                $this->fail('Device name badge does not exist.');
            }
        });
    }
}
