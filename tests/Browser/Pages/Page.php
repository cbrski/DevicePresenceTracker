<?php

namespace Tests\Browser\Pages;

use Laravel\Dusk\Page as BasePage;

abstract clasdusPage extends BasePage
{
    /**
     * Get the global element shortcuts for the site.
     *
     * @return array
     */
    public static function siteElements()
    {
        return [
            '@element' => '#selector',
        ];
    }
}
