<?php

declare(strict_types=1);

namespace App\Http\Controllers\Control;

use App\Control\Application\Services\TenantAdminService;
use App\Control\Application\Services\TenantModuleCatalogService;
use App\Control\Application\Services\TenantPresentationService;
use App\Control\Application\Services\TenantSimulationAutomationService;
use App\Shared\Platform\Services\InstanceDeploymentService;
use App\Dashboard\Application\UseCases\GetSystemNodeStatusUseCase;
use App\Models\User;
use App\Shared\Identity\Domain\PlatformRole;
use App\Shared\Infrastructure\Models\TenantModel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

final class CompanyController
{
    public function __construct(
        private readonly TenantPresentationService $presentation,
        private readonly TenantAdminService $admin,
        private readonly TenantModuleCatalogService $moduleCatalog,
        private readonly GetSystemNodeStatusUseCase $nodeStatus,
        private readonly TenantSimulationAutomationService $simulationAutomation,
        private readonly InstanceDeploymentService $deployment,
    ) {}

    public function index(): Response
    {
        $instanceSlug = \Illuminate\Support\Str::slug((string) config('platform.client_slug', ''));
        $defaults     = config('platform.simulation.defaults', []);

        $tenants = array_map(function (array $row) {
            $tenant = TenantModel::query()->find($row['id']);
            if ($tenant === null) {
                return $row;
            }

            return array_merge($row, [
                'can_simulate'         => $this->simulationAutomation->canSimulateTenant($tenant),
                'simulate_block_reason'=> $this->simulationAutomation->simulationBlockReason($tenant),
                'fixture_slug'         => $this->simulationAutomation->resolveFixtureSlug($tenant),
            ]);
        }, $this->presentation->listTenants());

        return Inertia::render('Control/Companies/Index', [
            'tenants'        => $tenants,
            'instance_slug'  => $instanceSlug,
            'simulation_defaults' => [
                'events_per_minute' => (int) ($defaults['events_per_minute'] ?? 10),
                'duration_minutes'  => (int) ($defaults['duration_minutes'] ?? 1),
            ],
            'active_simulation_run_id' => session('active_simulation_run_id'),
            'deployment_context'       => $this->deployment->globalPresentation(),
        ]);
    }

    public function show(TenantModel $tenant): Response
    {
        $detail = $this->presentation->tenantDetail($tenant->id);
        if ($detail === null) {
            abort(404);
        }

        $nodes = $this->nodeStatus->execute()->toArray();

        return Inertia::render('Control/Companies/Show', [
            'tenant'     => $detail,
            'deployment' => $this->deployment->presentationForTenant($tenant),
            'health'     => [
                'nodes'         => $nodes['status_by_node'] ?? [],
                'last_updated'  => $nodes['last_updated'] ?? null,
            ],
            'plans'   => $this->presentation->availablePlans(),
            'modules' => $this->presentation->availableModuleKeys(),
            'roles'   => array_map(
                fn (PlatformRole $role) => ['value' => $role->value, 'label' => $role->label()],
                array_filter(PlatformRole::cases(), fn (PlatformRole $r) => $r->isInstanceOperator()),
            ),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['required', 'string', 'max:80', 'alpha_dash', 'unique:tenants,slug'],
            'plan' => ['required', 'string', Rule::in($this->presentation->availablePlans())],
        ]);

        $this->admin->create($validated['name'], $validated['slug'], $validated['plan']);

        return redirect()->route('control.companies.index')->with('message', 'Empresa (tenant) creada.');
    }

    public function suspend(TenantModel $tenant): RedirectResponse
    {
        $this->admin->suspend($tenant);

        return back()->with('message', 'Tenant suspendido.');
    }

    public function activate(TenantModel $tenant): RedirectResponse
    {
        $this->admin->activate($tenant);

        return back()->with('message', 'Tenant activado.');
    }

    public function updatePlan(Request $request, TenantModel $tenant): RedirectResponse
    {
        $validated = $request->validate([
            'plan' => ['required', 'string', Rule::in($this->presentation->availablePlans())],
        ]);

        $this->admin->updatePlan($tenant, $validated['plan']);

        return back()->with('message', 'Plan actualizado.');
    }

    public function modulesConfig(TenantModel $tenant): Response
    {
        return Inertia::render('Control/Companies/ModulesConfig', $this->moduleCatalog->presentationForTenant($tenant));
    }

    public function updateModulesCatalog(Request $request, TenantModel $tenant): RedirectResponse
    {
        $validated = $request->validate([
            'limits.producers_max'   => ['required', 'integer', 'min:0', 'max:50'],
            'limits.subscribers_max' => ['required', 'integer', 'min:0', 'max:50'],
            'catalog.service_contact_message' => ['nullable', 'string', 'max:500'],
            'catalog.middleware.name'        => ['nullable', 'string', 'max:120'],
            'catalog.middleware.description' => ['nullable', 'string', 'max:500'],
            'catalog.producers'              => ['nullable', 'array'],
            'catalog.producers.*.id'         => ['required_with:catalog.producers', 'string', 'max:80', 'alpha_dash'],
            'catalog.producers.*.name'       => ['required_with:catalog.producers', 'string', 'max:120'],
            'catalog.producers.*.event_types_emitted' => ['nullable'],
            'catalog.producers.*.channels'   => ['nullable'],
            'catalog.subscribers'            => ['nullable', 'array'],
            'catalog.subscribers.*.id'       => ['required_with:catalog.subscribers', 'string', 'max:80', 'alpha_dash'],
            'catalog.subscribers.*.name'     => ['required_with:catalog.subscribers', 'string', 'max:120'],
            'catalog.subscribers.*.event_types_consumed' => ['nullable'],
        ]);

        try {
            $this->moduleCatalog->saveCatalog(
                $tenant,
                $validated['catalog'],
                $validated['limits'],
            );
        } catch (\RuntimeException $e) {
            return back()->withErrors(['catalog' => $e->getMessage()]);
        }

        return redirect()
            ->route('control.companies.modules', $tenant)
            ->with('message', 'Catálogo técnico de módulos guardado.');
    }

    public function applyModulesCatalog(TenantModel $tenant): RedirectResponse
    {
        try {
            $this->moduleCatalog->applyToCurrentInstance($tenant);
        } catch (\RuntimeException $e) {
            return back()->withErrors(['apply' => $e->getMessage()]);
        }

        return back()->with('message', 'Catálogo aplicado a config/modules/modules_config.json. El cliente debe ejecutar sync-config.');
    }

    public function updateModules(Request $request, TenantModel $tenant): RedirectResponse
    {
        $available = $this->presentation->availableModuleKeys();
        $validated = $request->validate([
            'modules'   => ['required', 'array'],
            'modules.*' => ['string', Rule::in($available)],
        ]);

        $this->admin->updateModules($tenant, $validated['modules']);

        return back()->with('message', 'Módulos actualizados.');
    }

    public function storeOperator(Request $request, TenantModel $tenant): RedirectResponse
    {
        Gate::authorize('platform.manage-users');

        $block = $this->deployment->operatorBlockReason($tenant);
        if ($block !== null) {
            return back()->withErrors(['operator' => $block]);
        }

        $instanceRoleValues = array_map(
            fn (PlatformRole $r) => $r->value,
            array_filter(PlatformRole::cases(), fn (PlatformRole $r) => $r->isInstanceOperator()),
        );

        $validated = $request->validate([
            'name'          => ['required', 'string', 'max:120'],
            'email'         => ['required', 'email', 'max:190', 'unique:users,email'],
            'password'      => ['required', 'string', 'min:8'],
            'platform_role' => ['required', Rule::in($instanceRoleValues)],
        ]);

        User::query()->create([
            'tenant_id'     => $tenant->id,
            'name'          => $validated['name'],
            'email'         => $validated['email'],
            'password'      => Hash::make($validated['password']),
            'platform_role' => $validated['platform_role'],
        ]);

        $settings = is_array($tenant->settings) ? $tenant->settings : [];
        if (($settings['primary_admin_email'] ?? null) === null) {
            $settings['primary_admin_email'] = $validated['email'];
            $tenant->update(['settings' => $settings]);
        }

        return back()->with('message', 'Operador de instancia creado.');
    }

    public function updateOperatorRole(Request $request, TenantModel $tenant, User $user): RedirectResponse
    {
        Gate::authorize('platform.manage-users');

        $this->assertTenantOperator($tenant, $user);

        $instanceRoleValues = array_map(
            fn (PlatformRole $r) => $r->value,
            array_filter(PlatformRole::cases(), fn (PlatformRole $r) => $r->isInstanceOperator()),
        );

        $validated = $request->validate([
            'platform_role' => ['required', Rule::in($instanceRoleValues)],
        ]);

        $user->update(['platform_role' => $validated['platform_role']]);

        return back()->with('message', 'Rol actualizado.');
    }

    public function updateOperatorPassword(Request $request, TenantModel $tenant, User $user): RedirectResponse
    {
        Gate::authorize('platform.manage-users');

        $this->assertTenantOperator($tenant, $user);

        $validated = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('message', 'Contraseña actualizada para '.$user->getAttribute('email').'.');
    }

    private function assertTenantOperator(TenantModel $tenant, User $user): void
    {
        if (! $user->belongsToTenant($tenant->id)) {
            abort(404);
        }

        $role = PlatformRole::tryFromString((string) $user->getAttribute('platform_role'));
        if ($role === null || ! $role->isInstanceOperator()) {
            abort(403);
        }
    }
}
