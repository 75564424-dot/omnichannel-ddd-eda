<?php

declare(strict_types=1);

namespace Tests\Feature\Control;

use App\Models\User;
use App\Shared\Infrastructure\Models\ClientIncidentReportModel;
use App\Shared\Infrastructure\Models\TenantModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ClientSupportReportTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function instance_operator_can_submit_support_report(): void
    {
        config([
            'platform.client_slug' => 'acme',
            'platform_auth.api_auth_enabled' => false,
        ]);

        $tenant = TenantModel::query()->create([
            'id'       => 'aaaaaaaa-1111-1111-1111-111111111111',
            'name'     => 'Acme',
            'slug'     => 'acme',
            'status'   => 'active',
            'settings' => [],
        ]);

        $user = User::query()->create([
            'tenant_id'     => $tenant->id,
            'name'          => 'Admin',
            'email'         => 'admin@local',
            'password'      => Hash::make('secret'),
            'platform_role' => 'platform_admin',
        ]);

        $this->actingAs($user)
            ->postJson('/support/reports', [
                'description' => 'El bus aparece detenido y no procesa eventos desde hace 20 minutos.',
                'severity'    => 'high',
                'page_url'    => 'http://127.0.0.1:8000/dashboard',
            ])
            ->assertCreated()
            ->assertJsonPath('report.status', 'open');

        $this->assertDatabaseCount('client_incident_reports', 1);

        $report = ClientIncidentReportModel::query()->first();
        $this->assertNotNull($report);
        $this->assertSame($user->email, $report->reporter_email);
        $this->assertIsArray($report->diagnostic_log);
        $this->assertArrayHasKey('bus', $report->diagnostic_log);
    }

    #[Test]
    public function saas_admin_sees_client_reports_on_incidents_page(): void
    {
        config([
            'platform.control_plane' => true,
            'platform_auth.web_auth_enabled' => true,
        ]);

        ClientIncidentReportModel::query()->create([
            'id'              => '11111111-1111-1111-1111-111111111111',
            'reporter_name'   => 'Cliente',
            'reporter_email'  => 'admin@local',
            'tenant_name'     => 'Acme',
            'description'     => 'Problema de integración',
            'severity'        => 'normal',
            'status'          => 'open',
            'diagnostic_log'  => ['bus' => ['status' => 'STOPPED']],
        ]);

        $saas = User::query()->create([
            'name'          => 'SaaS',
            'email'         => 'saas@local',
            'password'      => Hash::make('secret'),
            'platform_role' => 'saas_admin',
        ]);

        $this->actingAs($saas)
            ->get('/control/incidents')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Control/Incidents/Index')
                ->has('client_reports', 1)
                ->where('client_reports.0.description', 'Problema de integración'));
    }

    #[Test]
    public function saas_admin_can_respond_and_client_sees_unread_notification(): void
    {
        config([
            'platform.control_plane' => true,
            'platform_auth.web_auth_enabled' => true,
        ]);

        $client = User::query()->create([
            'name'          => 'Cliente',
            'email'         => 'client@local',
            'password'      => Hash::make('secret'),
            'platform_role' => 'platform_admin',
        ]);

        $report = ClientIncidentReportModel::query()->create([
            'id'              => '22222222-2222-2222-2222-222222222222',
            'user_id'         => $client->getKey(),
            'reporter_name'   => 'Cliente',
            'reporter_email'  => 'client@local',
            'description'     => 'Necesito ayuda con el bus',
            'severity'        => 'normal',
            'status'          => 'open',
            'diagnostic_log'  => ['bus' => ['status' => 'STOPPED']],
        ]);

        $saas = User::query()->create([
            'name'          => 'SaaS',
            'email'         => 'saas@local',
            'password'      => Hash::make('secret'),
            'platform_role' => 'saas_admin',
        ]);

        $this->actingAs($saas)
            ->post("/control/incidents/reports/{$report->id}/respond", [
                'admin_response' => 'Revisamos el bus. Reinicie el nodo middleware.',
            ])
            ->assertRedirect();

        $report->refresh();
        $this->assertSame('Revisamos el bus. Reinicie el nodo middleware.', $report->admin_response);
        $this->assertNull($report->client_read_at);

        config([
            'platform.control_plane' => false,
            'platform.client_slug' => 'acme',
        ]);

        $tenant = TenantModel::query()->create([
            'id'       => 'bbbbbbbb-2222-2222-2222-222222222222',
            'name'     => 'Acme',
            'slug'     => 'acme',
            'status'   => 'active',
            'settings' => [],
        ]);
        $client->forceFill(['tenant_id' => $tenant->id])->save();

        $this->actingAs($client)
            ->getJson('/support/notifications')
            ->assertOk()
            ->assertJsonPath('unread_count', 1)
            ->assertJsonPath('summary.answered', 1)
            ->assertJsonPath('reports.0.admin_response', 'Revisamos el bus. Reinicie el nodo middleware.')
            ->assertJsonPath('reports.0.status_label', 'En revisión');

        $this->actingAs($client)
            ->getJson("/support/reports/{$report->id}")
            ->assertOk()
            ->assertJsonPath('unread_count', 0);

        $this->actingAs($client)
            ->getJson('/support/notifications')
            ->assertJsonPath('unread_count', 0);
    }
}
