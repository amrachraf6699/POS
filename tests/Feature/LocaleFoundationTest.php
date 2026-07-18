<?php

namespace Tests\Feature;

use Tests\TestCase;

class LocaleFoundationTest extends TestCase
{
    public function test_main_application_layout_is_arabic_and_rtl(): void
    {
        config(['app.locale' => 'ar']);

        $html = view('layouts.app')->render();

        $this->assertStringContainsString('lang="ar"', $html);
        $this->assertStringContainsString('dir="rtl"', $html);
        $this->assertStringContainsString('cdn.tailwindcss.com', $html);
    }

    public function test_tracker_layout_remains_english_and_ltr(): void
    {
        $response = $this->get('/__tracker');

        $response->assertOk();
        $response->assertSee('lang="en"', false);
        $response->assertSee('dir="ltr"', false);
    }
}
