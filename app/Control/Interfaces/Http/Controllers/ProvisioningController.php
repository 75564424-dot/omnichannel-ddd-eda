<?php

declare(strict_types=1);

namespace App\Control\Interfaces\Http\Controllers;

use App\Control\Application\Services\ControlCatalogService;
use App\Control\Application\Services\Tenants\ProvisionNewTenantService;
use App\Control\Application\Services\Tenants\ProvisioningChecklistService;
use App\Shared\Platform\Services\InstanceDeploymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

final class ProvisioningController
{
    public function __construct(
        private readonly ControlCatalogService $catalog,
        private readonly InstanceDeploymentService $deployment,
        private readonly ProvisioningChecklistService $checklist,
        private readonly ProvisionNewTenantService $provisioner,
    ) {}

    public function index(): Response
    {
        return Inertia::render('Control/Provisioning/Index', [
            'plans' => $this->catalog->plansForSale(),
            'modules' => $this->catalog->modulesForSale(),
            'industries' => $this->catalog->industries(),
            'steps' => $this->checklist->checklist(),
            'deployment_context' => $this->deployment->globalPresentation(),
            'pitch' => [
                'title' => 'Catálogo de servicio',
                'description' => 'Define plan y módulos contratados. El cliente los verá habilitados en su portal y podrá sincronizar el catálogo técnico (modules_config) sin volver a negociar el paquete.',
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:120'],
            'legal_name' => ['nullable', 'string', 'max:160'],
            'tax_id' => ['nullable', 'string', 'max:40'],
            'industry' => ['nullable', 'string', 'max:40'],
            'country' => ['nullable', 'string', 'max:80'],
            'city' => ['nullable', 'string', 'max:80'],
            'phone' => ['nullable', 'string', 'max:30'],
            'billing_email' => ['nullable', 'email', 'max:190'],
            'website' => ['nullable', 'string', 'max:190'],
            'timezone' => ['nullable', 'string', 'max:60'],
            'notes' => ['nullable', 'string', 'max:500'],
            'slug' => ['required', 'string', 'max:80', 'alpha_dash', 'unique:tenants,slug'],
            'plan' => ['required', 'string', Rule::in($this->catalog->planKeysForSale())],
            'modules' => ['required', 'array', 'min:1'],
            'modules.*' => ['string', Rule::in($this->catalog->moduleKeys())],
            'admin_name' => ['required', 'string', 'max:120'],
            'admin_email' => ['required', 'email', 'max:190', 'unique:users,email'],
            'admin_password' => ['required', 'string', 'min:8'],
        ]);

        try {
            $result = $this->provisioner->provision($validated);
        } catch (\Throwable $e) {
            return back()->withErrors(['provisioning' => $e->getMessage()])->withInput();
        }

        return redirect()
            ->route('control.companies.show', $result['tenant'])
            ->with('message', $result['message'])
            ->with('show_deployment_guide', $result['show_deployment_guide']);
    }
}
