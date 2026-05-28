# Kubernetes manifests — apply order

```bash
kubectl apply -f namespace.yaml
kubectl apply -f configmap-env.yaml      # edit secrets before prod
kubectl apply -f configmap-nginx.yaml
kubectl apply -f deployment-web.yaml
kubectl apply -f deployment-worker.yaml
kubectl apply -f service.yaml
kubectl apply -f ingress.yaml              # adjust host/TLS
kubectl apply -f hpa.yaml                  # Fase 3 scaling
kubectl apply -f cronjob-backup.yaml       # requires PVC middleware-backup-pvc
```

Image: build `Dockerfile` target `fpm`, tag `platform-event-bus-core:<version>`.

Probes: nginx sidecar on `:8080` → `/up`, `/health/ready`.
