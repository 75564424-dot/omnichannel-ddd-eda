<?php



declare(strict_types=1);



namespace App\Http\Controllers\Control;



use App\Control\Application\Services\ControlCatalogService;

use App\Control\Application\Services\TenantAdminService;

use App\Models\User;

use App\Shared\Infrastructure\Models\TenantModel;
use App\Shared\Platform\Services\InstanceDeploymentService;

use Illuminate\Http\RedirectResponse;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Hash;

use Illuminate\Validation\Rule;

use Inertia\Inertia;

use Inertia\Response;



final class ProvisioningController

{

    public function __construct(

        private readonly TenantAdminService $admin,

        private readonly ControlCatalogService $catalog,
        private readonly InstanceDeploymentService $deployment,
    ) {}



    public function index(): Response

    {

        return Inertia::render('Control/Provisioning/Index', [

            'plans'      => $this->catalog->plansForSale(),

            'modules'    => $this->catalog->modulesForSale(),

            'industries' => $this->catalog->industries(),

            'steps'      => $this->provisioningChecklist(),
            'deployment_context' => $this->deployment->globalPresentation(),

            'pitch'      => [

                'title'       => 'Catálogo de servicio',

                'description' => 'Define plan y módulos contratados. El cliente los verá habilitados en su portal y podrá sincronizar el catálogo técnico (modules_config) sin volver a negociar el paquete.',

            ],

        ]);

    }



    public function store(Request $request): RedirectResponse

    {

        $validated = $request->validate([

            'company_name'   => ['required', 'string', 'max:120'],

            'legal_name'     => ['nullable', 'string', 'max:160'],

            'tax_id'         => ['nullable', 'string', 'max:40'],

            'industry'       => ['nullable', 'string', 'max:40'],

            'country'        => ['nullable', 'string', 'max:80'],

            'city'           => ['nullable', 'string', 'max:80'],

            'phone'          => ['nullable', 'string', 'max:30'],

            'billing_email'  => ['nullable', 'email', 'max:190'],

            'website'        => ['nullable', 'string', 'max:190'],

            'timezone'       => ['nullable', 'string', 'max:60'],

            'notes'          => ['nullable', 'string', 'max:500'],

            'slug'           => ['required', 'string', 'max:80', 'alpha_dash', 'unique:tenants,slug'],

            'plan'           => ['required', 'string', Rule::in($this->catalog->planKeysForSale())],

            'modules'        => ['required', 'array', 'min:1'],

            'modules.*'      => ['string', Rule::in($this->catalog->moduleKeys())],

            'admin_name'     => ['required', 'string', 'max:120'],

            'admin_email'    => ['required', 'email', 'max:190', 'unique:users,email'],

            'admin_password' => ['required', 'string', 'min:8'],

        ]);



        $profile = array_filter([

            'legal_name'    => $validated['legal_name'] ?? null,

            'tax_id'        => $validated['tax_id'] ?? null,

            'industry'      => $validated['industry'] ?? null,

            'country'       => $validated['country'] ?? null,

            'city'          => $validated['city'] ?? null,

            'phone'         => $validated['phone'] ?? null,

            'billing_email' => $validated['billing_email'] ?? null,

            'website'       => $validated['website'] ?? null,

            'timezone'      => $validated['timezone'] ?? 'UTC',

            'notes'         => $validated['notes'] ?? null,

        ], static fn ($v) => $v !== null && $v !== '');



        $modules = array_values(array_unique($validated['modules']));

        if (! in_array('middleware', $modules, true)) {

            $modules[] = 'middleware';

        }



        $tenant = $this->admin->create(

            $validated['company_name'],

            $validated['slug'],

            $validated['plan'],

            $profile,

            $modules,

        );



        $settings = is_array($tenant->settings) ? $tenant->settings : [];

        $settings['primary_admin_email'] = $validated['admin_email'];
        $settings['app_url'] = $this->deployment->presentationForTenant($tenant)['recommended_app_url'];
        $settings['deployment'] = [
            'mode'                 => 'instance_per_client',
            'status'               => 'pending_dedicated_instance',
            'required_client_slug' => $tenant->slug,
            'provisioned_at'       => now()->toIso8601String(),
        ];

        $tenant->update(['settings' => $settings]);



        User::query()->create([
            'tenant_id'     => $tenant->id,
            'name'          => $validated['admin_name'],
            'email'         => $validated['admin_email'],
            'password'      => Hash::make($validated['admin_password']),
            'platform_role' => 'platform_admin',
        ]);



        return redirect()
            ->route('control.companies.show', $tenant)
            ->with('message', 'Registro SaaS completado. Despliegue la instancia dedicada con PLATFORM_CLIENT_SLUG='.$tenant->slug.'.')
            ->with('show_deployment_guide', true);

    }



    /** @return list<array<string, mixed>> */

    private function provisioningChecklist(): array

    {

        $tenantCount = TenantModel::query()->count();

        $hasAdmin = User::query()->where('platform_role', 'platform_admin')->exists();



        return [

            ['key' => 'tenant', 'label' => 'Crear tenant', 'done' => $tenantCount > 0, 'detail' => "{$tenantCount} en registro"],

            ['key' => 'schemas', 'label' => 'Schemas / migraciones', 'done' => true, 'detail' => 'Base local migrada'],

            ['key' => 'modules', 'label' => 'Catálogo comercial', 'done' => true, 'detail' => 'config/saas_catalog.php'],

            ['key' => 'admin', 'label' => 'Admin principal instancia', 'done' => $hasAdmin, 'detail' => $hasAdmin ? 'platform_admin existe' : 'pendiente'],

            ['key' => 'api_keys', 'label' => 'API keys M2M', 'done' => (string) config('security.api_keys') !== '', 'detail' => 'PLATFORM_API_KEYS o artisan platform:issue-api-token'],

            ['key' => 'infra', 'label' => 'Desplegar infraestructura', 'done' => env('DOCKER_APP_ROLE') !== null, 'detail' => env('DOCKER_APP_ROLE') ? 'Docker role: '.env('DOCKER_APP_ROLE') : 'Entorno local artisan'],

        ];

    }

}

