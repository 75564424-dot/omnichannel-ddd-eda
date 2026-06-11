<?php

declare(strict_types=1);

namespace App\Control\Interfaces\Http\Controllers;

use App\Control\Application\Services\Tenants\CompanyListingService;
use App\Control\Application\Services\Tenants\CompanyShowPageService;
use App\Control\Application\Services\Tenants\TenantAdminService;
use App\Control\Application\Services\Tenants\TenantModuleCatalogService;
use App\Control\Application\Services\Tenants\TenantOperatorService;
use App\Control\Application\Services\Tenants\TenantPresentationService;
use App\Models\User;
use App\Shared\Infrastructure\Models\TenantModel;
use App\Shared\Platform\LocalFleet\LocalFleetInstanceProvisioner;
use App\Shared\Platform\Services\InstanceDeploymentService;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

final class CompanyController
{
    public function __construct(
        private readonly TenantPresentationService $presentation,
        private readonly TenantAdminService $admin,
        private readonly TenantModuleCatalogService $moduleCatalog,
        private readonly CompanyListingService $listing,
        private readonly CompanyShowPageService $showPage,
        private readonly TenantOperatorService $operators,
        private readonly InstanceDeploymentService $deployment,
        private readonly LocalFleetInstanceProvisioner $localFleet,
        private readonly \App\Control\Application\Services\Tenants\TenantLifecycleOrchestrator $orchestrator,
        private readonly Gate $gate,
    ) {}

    public function index(): Response
    {
        return Inertia::render('Control/Companies/Index', [
            'tenants' => $this->listing->tenantsForIndex(),
            'instance_slug' => Str::slug((string) config('platform.client_slug', '')),
            'simulation_defaults' => $this->listing->simulationDefaults(),
            'active_simulation_run_id' => session('active_simulation_run_id'),
            'deployment_context' => $this->deployment->globalPresentation(),
        ]);
    }

    public function show(TenantModel $tenant): Response
    {
        return Inertia::render('Control/Companies/Show', $this->showPage->buildProps($tenant));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['required', 'string', 'max:80', 'alpha_dash', 'unique:tenants,slug'],
            'plan' => ['required', 'string', Rule::in($this->presentation->availablePlans())],
        ]);

        $tenant = $this->admin->create($validated['name'], $validated['slug'], $validated['plan']);
        $fleetResult = $this->localFleet->provision($tenant);

        $message = $fleetResult->provisioned
            ? 'Empresa creada con instancia aislada en '.$fleetResult->appUrl()
            : 'Empresa (tenant) creada.';

        return redirect()->route('control.companies.index')->with('message', $message);
    }

    public function start(TenantModel $tenant): RedirectResponse
    {
        $this->orchestrator->start($tenant);

        return back()->with('message', 'Instancia de empresa levantada correctamente.');
    }

    public function suspend(TenantModel $tenant): RedirectResponse
    {
        $this->orchestrator->suspend($tenant);

        return back()->with('message', 'Servicio de empresa suspendido.');
    }

    public function activate(TenantModel $tenant): RedirectResponse
    {
        $this->orchestrator->restore($tenant);

        return back()->with('message', 'Servicio de empresa restaurado.');
    }

    public function restore(TenantModel $tenant): RedirectResponse
    {
        $this->orchestrator->restore($tenant);

        return back()->with('message', 'Servicio de empresa restaurado.');
    }

    public function lifecycleStatus(TenantModel $tenant): \Illuminate\Http\JsonResponse
    {
        return response()->json($this->orchestrator->lifecycleStatus($tenant));
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
            'limits.producers_max' => ['required', 'integer', 'min:0', 'max:50'],
            'limits.subscribers_max' => ['required', 'integer', 'min:0', 'max:50'],
            'catalog.service_contact_message' => ['nullable', 'string', 'max:500'],
            'catalog.middleware.name' => ['nullable', 'string', 'max:120'],
            'catalog.middleware.description' => ['nullable', 'string', 'max:500'],
            'catalog.producers' => ['nullable', 'array'],
            'catalog.producers.*.id' => ['required_with:catalog.producers', 'string', 'max:80', 'alpha_dash'],
            'catalog.producers.*.name' => ['required_with:catalog.producers', 'string', 'max:120'],
            'catalog.producers.*.event_types_emitted' => ['nullable'],
            'catalog.producers.*.channels' => ['nullable'],
            'catalog.subscribers' => ['nullable', 'array'],
            'catalog.subscribers.*.id' => ['required_with:catalog.subscribers', 'string', 'max:80', 'alpha_dash'],
            'catalog.subscribers.*.name' => ['required_with:catalog.subscribers', 'string', 'max:120'],
            'catalog.subscribers.*.event_types_consumed' => ['nullable'],
        ]);

        try {
            $this->moduleCatalog->saveCatalog($tenant, $validated['catalog'], $validated['limits']);
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
            'modules' => ['required', 'array'],
            'modules.*' => ['string', Rule::in($available)],
        ]);

        $this->admin->updateModules($tenant, $validated['modules']);

        return back()->with('message', 'Módulos actualizados.');
    }

    public function storeOperator(Request $request, TenantModel $tenant): RedirectResponse
    {
        $this->gate->authorize('platform.manage-users');

        $block = $this->operators->operatorBlockReason($tenant);
        if ($block !== null) {
            return back()->withErrors(['operator' => $block]);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'platform_role' => ['required', Rule::in($this->operators->instanceRoleValues())],
        ]);

        $this->operators->createOperator(
            $tenant,
            $validated['name'],
            $validated['email'],
            $validated['password'],
            $validated['platform_role'],
        );

        return back()->with('message', 'Operador de instancia creado.');
    }

    public function updateOperatorRole(Request $request, TenantModel $tenant, User $user): RedirectResponse
    {
        $this->gate->authorize('platform.manage-users');

        $validated = $request->validate([
            'platform_role' => ['required', Rule::in($this->operators->instanceRoleValues())],
        ]);

        $this->operators->updateOperatorRole($user, $tenant, $validated['platform_role']);

        return back()->with('message', 'Rol actualizado.');
    }

    public function updateOperatorPassword(Request $request, TenantModel $tenant, User $user): RedirectResponse
    {
        $this->gate->authorize('platform.manage-users');

        $validated = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $this->operators->updateOperatorPassword($user, $tenant, $validated['password']);

        return back()->with('message', 'Contraseña actualizada para '.$user->getAttribute('email').'.');
    }
}
