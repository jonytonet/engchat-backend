# 🐳 Docker & Laravel Sail - EngChat Setup

## 📋 **Pré-requisitos**

### Windows:
- **Docker Desktop** para Windows
- **WSL2** habilitado (recomendado)
- **Git Bash** ou **PowerShell**

### Verificar instalação:
```bash
docker --version
docker-compose --version
```

---

## 🚀 **Primeiros Passos**

### 1. **Subir os containers:**
```bash
# Opção 1: Usando nosso helper
.\sail.bat up

# Opção 2: Comando completo
./vendor/bin/sail up -d
```

### 2. **Aguardar containers estarem prontos:**
```bash
# Verificar status
.\sail.bat ps

# Ver logs em tempo real  
.\sail.bat logs
```

### 3. **Executar migrações:**
```bash
.\sail.bat migrate
.\sail.bat seed
```

---

## 🌐 **Serviços Disponíveis**

### **Aplicação Principal:**
- 🌍 **EngChat App**: http://localhost:8000
- 📚 **Swagger Docs**: http://localhost:8000/api/documentation
- 🔗 **Laravel Reverb**: ws://localhost:8080

### **Serviços de Apoio:**
- 🗄️ **MariaDB**: localhost:3306
- 🔴 **Redis**: localhost:6379  
- 🐰 **RabbitMQ Management**: http://localhost:15672
- 📧 **Mailpit**: http://localhost:8025

### **Credenciais RabbitMQ:**
- **User:** engchat
- **Password:** secret

---

## 🛠️ **Comandos Úteis**

### **Gerenciamento de Containers:**
```bash
# Iniciar todos os serviços
.\sail.bat up

# Parar todos os serviços  
.\sail.bat down

# Rebuild containers
.\sail.bat build

# Ver logs
.\sail.bat logs
```

### **Comandos Laravel:**
```bash
# Artisan commands
.\sail.bat artisan migrate
.\sail.bat artisan queue:work
.\sail.bat artisan reverb:start

# Composer
.\sail.bat composer install
.\sail.bat composer require package/name

# NPM
.\sail.bat npm install
.\sail.bat npm run dev
```

### **Banco de Dados:**
```bash
# Migrations
.\sail.bat migrate

# Seeders
.\sail.bat seed

# Fresh migration + seed
.\sail.bat fresh

# Acessar MariaDB
.\sail.bat mariadb
```

### **WebSocket (Reverb):**
```bash
# Iniciar servidor WebSocket
.\sail.bat reverb

# Em modo debug
.\sail.bat artisan reverb:start --debug
```

### **Testes:**
```bash
# Executar todos os testes
.\sail.bat test

# Testes específicos
.\sail.bat artisan test --filter ConversationTest
```

---

## 📁 **Estrutura Docker**

### **Serviços Configurados:**
```yaml
laravel.test:    # App Laravel + PHP 8.4
mariadb:         # Banco de dados
redis:           # Cache & Sessions  
rabbitmq:        # Message Queue
mailpit:         # Email testing
```

### **Volumes Persistentes:**
- `sail-mariadb` → Dados do banco
- `sail-redis` → Cache Redis
- `sail-rabbitmq` → Filas RabbitMQ

### **Portas Mapeadas:**
- **80** → Aplicação Web
- **3306** → MariaDB
- **5672** → RabbitMQ AMQP
- **6379** → Redis
- **8000** → Laravel App
- **8025** → Mailpit Web
- **8080** → Laravel Reverb
- **15672** → RabbitMQ Management

---

## 🔧 **Configuração Avançada**

### **Variáveis de Ambiente (.env):**
```env
# Docker Sail
WWWGROUP=1000
WWWUSER=1000

# Database  
DB_HOST=mariadb
DB_DATABASE=engchat_backend
DB_USERNAME=sail
DB_PASSWORD=password

# Redis
REDIS_HOST=redis
CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# RabbitMQ
RABBITMQ_HOST=rabbitmq
RABBITMQ_USER=engchat
RABBITMQ_PASSWORD=secret

# WebSocket
REVERB_HOST=0.0.0.0
REVERB_PORT=8080
BROADCAST_CONNECTION=reverb
```

### **Scaling & Performance:**
```bash
# Reverb com Redis scaling
REVERB_SCALING_ENABLED=true
REVERB_SCALING_CHANNEL=reverb

# Queue workers
.\sail.bat artisan queue:work --tries=3
```

---

## 🔍 **Troubleshooting**

### **Container não inicia:**
```bash
# Ver logs detalhados
.\sail.bat logs laravel.test

# Rebuild forçado
.\sail.bat down
.\sail.bat build --no-cache
.\sail.bat up
```

### **Banco não conecta:**
```bash
# Verificar se MariaDB está rodando
.\sail.bat ps

# Testar conexão
.\sail.bat artisan tinker
# DB::connection()->getPdo();
```

### **WebSocket não funciona:**
```bash
# Verificar configuração Reverb
.\sail.bat artisan config:cache
.\sail.bat artisan reverb:restart

# Testar porta
telnet localhost 8080
```

### **Permissões (Linux/WSL):**
```bash
# Corrigir permissões
sudo chown -R $USER:$USER .
sudo chmod -R 755 storage bootstrap/cache
```

---

## 📊 **Monitoramento**

### **Health Checks:**
```bash
# Status dos serviços
.\sail.bat ps

# Logs específicos
.\sail.bat logs mariadb
.\sail.bat logs redis
.\sail.bat logs rabbitmq
```

### **Performance:**
```bash
# Resource usage
docker stats

# Container details
.\sail.bat exec laravel.test htop
```

---

## 🚀 **Produção**

### **Build para produção:**
```bash
# Otimizações Laravel
.\sail.bat artisan config:cache
.\sail.bat artisan route:cache
.\sail.bat artisan view:cache

# Assets
.\sail.bat npm run build
```

### **Docker Compose produção:**
```yaml
# docker-compose.prod.yml
# - Remover volumes de desenvolvimento
# - Adicionar nginx reverse proxy  
# - Configurar SSL/TLS
# - Separar secrets
```

---

**💡 Para mais comandos, execute: `.\sail.bat` sem parâmetros**

**🔗 Documentação oficial:** [Laravel Sail](https://laravel.com/docs/11.x/sail)
