terraform {
  required_version = ">= 1.5.0"
}

# Placeholder — wire to cloud-specific modules (aws/, azure/, gcp/)
output "deployment_notes" {
  value = <<-EOT
    Terraform skeleton for ${var.client_slug} (${var.environment}).
    Next: add provider block + modules for RDS, Redis, compute, ingress.
    See docs/production/Cloud.md and Runbook_Deploy_VM.md for manual path.
  EOT
}
