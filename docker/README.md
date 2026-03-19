# Docker & Kubernetes

Configuration files for deploying Laravel Auth Starter Kits with Docker and Kubernetes.

## Docker

### Quick Start

```bash
# Build and start all services
docker-compose up -d

# View logs
docker-compose logs -f

# Stop services
docker-compose down
```

### Services

- **app**: Laravel PHP application (PHP 8.2 FPM)
- **nginx**: Web server (Nginx Alpine)
- **mysql**: Database (MySQL 8.0)
- **redis**: Cache & sessions (Redis Alpine)
- **node**: Asset compilation (Node.js 20)

### Building for Production

```bash
docker build -t laravel-auth:latest -f Dockerfile .
```

## Kubernetes

### Prerequisites

- kubectl configured
- Kubernetes cluster (local or cloud)
- Container registry access

### Deployment

```bash
# Create namespace
kubectl create namespace laravel-auth

# Apply secrets (edit first!)
kubectl apply -f k8s/secrets.yaml

# Deploy all resources
kubectl apply -f k8s/

# Check status
kubectl get pods -n laravel-auth
```

### Components

- **Deployment**: Laravel application pods
- **Service**: ClusterIP service for internal access
- **Ingress**: External access via domain
- **ConfigMap**: Environment configuration
- **Secrets**: Sensitive data (keys, passwords)
- **PersistentVolumeClaim**: Storage for uploads

### Scaling

```bash
# Scale to 3 replicas
kubectl scale deployment laravel-auth --replicas=3 -n laravel-auth

# Or use HPA for auto-scaling
kubectl apply -f k8s/hpa.yaml
```

## Environment Variables

| Variable | Description | Default |
|----------|-------------|---------|
| `APP_ENV` | Environment | production |
| `APP_DEBUG` | Debug mode | false |
| `APP_KEY` | Encryption key | (required) |
| `DB_HOST` | Database host | mysql |
| `DB_DATABASE` | Database name | laravel |
| `DB_USERNAME` | Database user | laravel |
| `DB_PASSWORD` | Database password | (required) |
| `REDIS_HOST` | Redis host | redis |
| `CACHE_DRIVER` | Cache driver | redis |
| `SESSION_DRIVER` | Session driver | redis |
| `QUEUE_CONNECTION` | Queue driver | redis |
