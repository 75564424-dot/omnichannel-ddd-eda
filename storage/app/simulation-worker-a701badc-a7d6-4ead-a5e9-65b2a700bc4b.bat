@echo off
set APP_KEY="base64:mxZly3IiOxaAkKOfAtEGsy1wMjzYJmEtgcYWm6NzOjY="
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
"C:\xampp\php\php.exe" "C:\Proyectos\antigravity\omnichannel-ddd-eda\artisan" platform:simulation:execute-run a701badc-a7d6-4ead-a5e9-65b2a700bc4b --env=client-pruebas --no-ansi >> "C:\Proyectos\antigravity\omnichannel-ddd-eda\storage\logs\simulation-worker-a701badc-a7d6-4ead-a5e9-65b2a700bc4b.log" 2>&1
