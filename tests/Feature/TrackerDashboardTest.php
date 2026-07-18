<?php

namespace Tests\Feature;

use Modules\Tracker\App\Domain\Services\TrackerService;
use Tests\TestCase;

class TrackerDashboardTest extends TestCase
{
    public function test_tracker_dashboard_is_public_and_contains_all_project_phases_and_tasks(): void
    {
        $response = $this->get('/__tracker');

        $response->assertOk()
            ->assertSee('Engineering progress')
            ->assertSee('12')
            ->assertSee('48')
            ->assertSee('Not started')
            ->assertSee('Agent maintained');
    }

    public function test_tracker_dashboard_has_no_write_routes(): void
    {
        $this->assertFalse(app('router')->getRoutes()->hasNamedRoute('tracker.store'));
        $this->assertFalse(app('router')->getRoutes()->hasNamedRoute('tracker.update'));
        $this->assertFalse(app('router')->getRoutes()->hasNamedRoute('tracker.destroy'));
    }

    public function test_tracker_has_problem_phase_and_task_detail_routes(): void
    {
        $dashboard = app(TrackerService::class)->dashboard();
        $phase = $dashboard['phases'][0];
        $task = $phase['tasks'][0];

        $this->get('/__tracker/problems')->assertOk()->assertSee('Problems & conflicts', false);
        $this->get(route('tracker.phases.show', $phase['id']))->assertOk()->assertSee($phase['title']);
        $this->get(route('tracker.tasks.show', [$phase['id'], $task['id']]))->assertOk()->assertSee($task['title']);

        if ($dashboard['issue_items'] !== []) {
            $issue = $dashboard['issue_items'][0];
            $this->get(route('tracker.problems.show', $issue['id']))->assertOk()->assertSee($issue['message']);
        }
    }

    public function test_resolve_route_requires_a_resolution(): void
    {
        config(['tracker.web_updates' => true]);
        $issues = app(TrackerService::class)->dashboard()['issue_items'];

        if ($issues === []) {
            $this->assertTrue(app('router')->getRoutes()->hasNamedRoute('tracker.problems.resolve'));

            return;
        }

        $this->withoutMiddleware()->post(route('tracker.problems.resolve', $issues[0]['id']), [])->assertSessionHasErrors('resolution');
    }
}
