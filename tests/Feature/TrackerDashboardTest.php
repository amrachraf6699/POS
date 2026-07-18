<?php

namespace Tests\Feature;

use Tests\TestCase;

class TrackerDashboardTest extends TestCase
{
    public function test_tracker_dashboard_is_public_and_contains_all_project_phases_and_tasks(): void
    {
        $response = $this->get('/__tracker');

        $response->assertOk()
            ->assertSee('خطة POS MVP')
            ->assertSee('12')
            ->assertSee('48')
            ->assertSee('لم يبدأ')
            ->assertSee('متابعة مشروع POS');
    }

    public function test_tracker_dashboard_has_no_write_routes(): void
    {
        $this->assertFalse(app('router')->getRoutes()->hasNamedRoute('tracker.store'));
        $this->assertFalse(app('router')->getRoutes()->hasNamedRoute('tracker.update'));
        $this->assertFalse(app('router')->getRoutes()->hasNamedRoute('tracker.destroy'));
    }
}
