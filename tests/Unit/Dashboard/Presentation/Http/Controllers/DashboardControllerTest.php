<?php

declare(strict_types=1);

namespace Tests\Unit\Dashboard\Presentation\Http\Controllers;

use DateTimeImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Source\Dashboard\Application\Service\DashboardService;
use Source\Dashboard\Domain\Entity\ActivityLogEntity;
use Source\Dashboard\Domain\Entity\DashboardEntity;
use Source\Dashboard\Domain\Entity\PageStatusCountEntity;
use Source\Users\Domain\Models\User;
use Tests\TestCase;

#[Group('presentation')]
class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    private const string ENDPOINT = '/panel';

    /** @test */
    #[Test]
    public function should_render_dashboard_view_for_authenticated_user(): void
    {
        // GIVEN: An authenticated user and a DashboardEntity returned by the service
        $dashboard = new DashboardEntity(
            pageStatusCounts: [
                new PageStatusCountEntity(status: 'active', count: 3),
                new PageStatusCountEntity(status: 'passive', count: 1),
            ],
            recentActivityLogs: [
                new ActivityLogEntity(
                    id: 1,
                    logName: 'default',
                    description: 'Page created',
                    event: 'created',
                    properties: [],
                    causerId: 1,
                    createdAt: new DateTimeImmutable,
                ),
            ],
        );

        $serviceMock = $this->mock(DashboardService::class);
        $serviceMock->shouldReceive('getDashboard')
            ->once()
            ->withNoArgs()
            ->andReturn($dashboard);

        $this->actingAsUser();

        // WHEN: Visiting the dashboard panel
        $response = $this->get(self::ENDPOINT);

        // THEN: Should render the view with the dashboard data
        $response->assertStatus(200);
        $response->assertViewIs('panel.dashboard');
        $response->assertViewHas('dashboard', $dashboard);
    }

    /** @test */
    #[Test]
    public function should_redirect_guest_to_login(): void
    {
        // GIVEN: No authenticated user (guest)

        // WHEN: Visiting the dashboard panel without authentication
        $response = $this->get(self::ENDPOINT);

        // THEN: Should redirect to the login page
        $response->assertRedirect('/login');
    }

    /** @test */
    #[Test]
    public function should_render_dashboard_with_empty_collections(): void
    {
        // GIVEN: Service returns a DashboardEntity with no data (fresh install)
        $dashboard = new DashboardEntity(pageStatusCounts: [], recentActivityLogs: []);

        $serviceMock = $this->mock(DashboardService::class);
        $serviceMock->shouldReceive('getDashboard')
            ->once()
            ->withNoArgs()
            ->andReturn($dashboard);

        $this->actingAsUser();

        // WHEN: Visiting the dashboard panel
        $response = $this->get(self::ENDPOINT);

        // THEN: Should still render the view with the empty dashboard entity
        $response->assertStatus(200);
        $response->assertViewIs('panel.dashboard');
        $response->assertViewHas('dashboard');

        $viewDashboard = $response->original->getData()['dashboard'];
        $this->assertEmpty($viewDashboard->pageStatusCounts());
        $this->assertEmpty($viewDashboard->recentActivityLogs());
    }

    // -------------------------------------------------------------------------
    // Helper
    // -------------------------------------------------------------------------

    private function actingAsUser(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);
        $this->actingAs($user);
    }
}
