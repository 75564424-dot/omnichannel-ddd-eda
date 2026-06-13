@echo off
set APP_KEY="base64:aS8JiVAoAmUOF7gJLFscm0zy0NiOosmXdRlwo/B4ALo="
set APP_NAME=pruebas
set APP_URL=http://127.0.0.1:8001
set DB_CONNECTION=sqlite
set DB_DATABASE=C:/Proyectos/antigravity/omnichannel-ddd-eda/database/instances/pruebas.sqlite
set CACHE_STORE=database
set SESSION_DRIVER=database
set SESSION_COOKIE=platform_session_pruebas
set SESSION_XSRF_COOKIE=platform_xsrf_pruebas
set QUEUE_CONNECTION=sync
set PLATFORM_DEPLOYMENT_MODE=instance_per_client
set PLATFORM_CLIENT_SLUG=pruebas
set PLATFORM_CLIENT_NAME=pruebas
set PLATFORM_CONTROL_PLANE=false
set PLATFORM_CONTROL_PLANE_URL=http://127.0.0.1:8000
set PLATFORM_SIMULATION_INTERNAL_TOKEN=local-dev-simulation-token
set PLATFORM_SEED_INSTANCE_TENANT=true
set MODULES_CONFIG_PATH=config/modules/instances/pruebas/modules_config.json
set APP_ENV=client-pruebas
cd /d "C:\Proyectos\antigravity\omnichannel-ddd-eda"
"C:\xampp\php\php.exe" "C:\Proyectos\antigravity\omnichannel-ddd-eda\artisan" platform:simulation:execute-run 28fd52ae-64d9-4c79-bf95-a27262398b2d --env=client-pruebas --no-ansi >> "C:\Proyectos\antigravity\omnichannel-ddd-eda\storage\logs\simulation-worker-28fd52ae-64d9-4c79-bf95-a27262398b2d.log" 2>&1
