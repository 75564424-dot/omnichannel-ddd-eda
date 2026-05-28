variable "client_slug" {
  type = string
}

variable "environment" {
  type    = string
  default = "production"
}

variable "app_replicas" {
  type    = number
  default = 2
}

variable "db_instance_class" {
  type    = string
  default = "db.t3.medium"
}

variable "redis_node_type" {
  type    = string
  default = "cache.t3.micro"
}
