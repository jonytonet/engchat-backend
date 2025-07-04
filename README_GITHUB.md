# 💬 EngChat - Plataforma de Atendimento MultiCanal

[![Laravel](https://img.shields.io/badge/Laravel-11.x-red.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-blue.svg)](https://php.net)
[![Docker](https://img.shields.io/badge/Docker-Ready-blue.svg)](https://docker.com)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

**EngChat** é uma plataforma moderna de atendimento ao cliente multicanal desenvolvida com Laravel, seguindo rigorosamente os princípios **SOLID**, **DDD** e **Clean Architecture**.

---

## 🚀 **Características Principais**

### 🏗️ **Arquitetura de Classe Mundial**
- ✅ **SOLID Principles** - Single Responsibility, Open/Closed, Liskov, Interface Segregation, Dependency Inversion
- ✅ **Domain Driven Design (DDD)** - Separação clara de domínios e responsabilidades
- ✅ **Clean Architecture** - Dependency rule e separation of concerns
- ✅ **Type Safety** - Strong typing em toda a aplicação
- ✅ **Zero Anti-patterns** - Código limpo e maintível

### 🛠️ **Stack Tecnológica**
- **Backend:** Laravel 11 + PHP 8.2+
- **Database:** MariaDB 10.11
- **Cache:** Redis 7.0
- **Queue:** RabbitMQ 3.12
- **WebSocket:** Laravel Reverb
- **Containers:** Docker + Laravel Sail
- **API Docs:** Swagger/OpenAPI 3.0
- **Authentication:** Laravel Sanctum (API) + Breeze (Admin)

### 🌟 **Funcionalidades**
- 💬 **Chat MultiCanal** - WhatsApp, Email, Web, integração unificada
- 🤖 **Bot Inteligente** - Automação e classificação automática
- 👥 **Gestão de Agentes** - Sistema de roles e departamentos
- 📊 **Analytics em Tempo Real** - Métricas e relatórios avançados
- 🔄 **Real-time** - WebSocket para comunicação instantânea
- 📱 **API REST** - Integração com apps Flutter
- 🔐 **Autenticação Dual** - API tokens + sessões web

---

## 🏗️ **Estrutura Arquitetural**

### 📁 **Organização do Código (DDD)**
```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Api/           # Endpoints para Flutter/Mobile
│   │   └── Admin/         # Endpoints para Admin Panel
│   ├── Requests/          # Form Validation
│   └── Resources/         # API Response Formatting
├── Services/              # Business Logic Layer
├── Repositories/          # Data Access Layer
│   ├── Contracts/         # Repository Interfaces
│   └── Eloquent/         # Eloquent Implementations
├── DTOs/                  # Data Transfer Objects
├── Models/                # Eloquent Models (Domain Entities)
├── Events/                # Domain Events
├── Listeners/             # Event Handlers
├── Jobs/                  # Queue Jobs
├── Observers/             # Model Observers
└── Enums/                # Value Objects
```

### 🔄 **Fluxo de Dados (Clean Architecture)**
```
Request → Controller → Service → Repository → Model
                ↓         ↑         ↑
              DTO ←   Business  →  Events
                      Rules
```

### 🎯 **Separação de Responsabilidades**
- **Controllers:** Apenas HTTP handling (requests/responses)
- **Services:** Business logic e orquestração
- **Repositories:** Acesso aos dados (queries/persistence)
- **DTOs:** Transferência type-safe de dados
- **Events:** Comunicação entre domínios

---

## 🐳 **Ambiente de Desenvolvimento**

### **Pré-requisitos**
- Docker Desktop
- Git
- Composer (opcional, via container)

### **🚀 Setup Rápido**

1. **Clone o repositório:**
   ```bash
   git clone https://github.com/jonytonet/engchat-backend.git
   cd engchat-backend
   ```

2. **Configure ambiente:**
   ```bash
   cp .env.example .env
   ```

3. **Inicie containers (Windows):**
   ```bash
   .\sail.bat up
   ```

4. **Execute migrations:**
   ```bash
   .\sail.bat migrate fresh
   ```

5. **Acesse a aplicação:**
   - **App:** http://localhost:8000
   - **API Docs:** http://localhost:8000/api/documentation
   - **Mailpit:** http://localhost:8025
   - **RabbitMQ:** http://localhost:15672

### **🛠️ Comandos Úteis (Windows)**
```bash
.\sail.bat up          # Iniciar containers
.\sail.bat down        # Parar containers
.\sail.bat shell       # Acessar shell do container
.\sail.bat migrate     # Executar migrations
.\sail.bat seed        # Executar seeders
.\sail.bat fresh       # Reset database + seed
.\sail.bat test        # Executar testes
.\sail.bat reverb      # Iniciar WebSocket server
```

---

## 📊 **Documentação da API**

### **🔗 Endpoints Principais**

#### **Autenticação**
```http
POST   /api/auth/login      # Login (token)
POST   /api/auth/logout     # Logout
GET    /api/auth/me         # User info
```

#### **Conversas**
```http
GET    /api/conversations           # Listar conversas
POST   /api/conversations           # Criar conversa
GET    /api/conversations/{id}      # Detalhes da conversa
PUT    /api/conversations/{id}      # Atualizar conversa
DELETE /api/conversations/{id}      # Deletar conversa
```

#### **Mensagens**
```http
GET    /api/conversations/{id}/messages    # Mensagens da conversa
POST   /api/conversations/{id}/messages    # Enviar mensagem
PUT    /api/messages/{id}/read             # Marcar como lida
```

#### **Contatos**
```http
GET    /api/contacts        # Listar contatos
POST   /api/contacts        # Criar contato
GET    /api/contacts/{id}   # Detalhes do contato
PUT    /api/contacts/{id}   # Atualizar contato
```

### **📚 Documentação Completa**
Acesse **http://localhost:8000/api/documentation** para documentação interativa Swagger.

---

## 🧪 **Testes**

### **Executar Testes**
```bash
.\sail.bat test                    # Todos os testes
.\sail.bat test --filter Feature   # Apenas feature tests
.\sail.bat test --filter Unit      # Apenas unit tests
```

### **Cobertura de Testes**
```bash
.\sail.bat test --coverage        # Relatório de cobertura
```

---

## 🔐 **Autenticação e Segurança**

### **API Authentication (Sanctum)**
- Token-based authentication para apps Flutter
- Rate limiting configurado
- CORS policies definidas

### **Admin Authentication (Breeze)**
- Session-based para painel administrativo
- Password reset functionality
- Profile management com upload de avatar

### **Roles e Permissões**
- **Admin:** Acesso total ao sistema
- **Manager:** Gestão de equipe e relatórios
- **Agent:** Atendimento ao cliente

---

## 📈 **Performance e Escalabilidade**

### **Cache Strategy**
- Redis para sessões e cache
- Database query optimization
- Eager loading configurado

### **Queue System**
- RabbitMQ para processamento assíncrono
- Background jobs para tarefas pesadas
- Rate limiting e retry policies

### **Real-time Features**
- Laravel Reverb WebSocket server
- Private channels por conversa
- Event broadcasting configurado

---

## 🚀 **Deploy e Produção**

### **Docker Production**
O projeto inclui configuração Docker pronta para produção com:
- Multi-stage builds
- Security optimizations
- Health checks
- Auto-scaling ready

### **Environment Variables**
Configurações essenciais no `.env`:
```env
APP_ENV=production
DB_CONNECTION=mariadb
REDIS_HOST=redis
QUEUE_CONNECTION=rabbitmq
BROADCAST_DRIVER=reverb
```

---

## 🤝 **Contribuição**

### **Code Standards**
- PSR-12 compliance
- SOLID principles obrigatórios
- Type hints em todos os métodos
- Testes unitários para novas features

### **Git Workflow**
```bash
git checkout -b feature/nova-funcionalidade
# Implementar feature
git commit -m "feat: implementar nova funcionalidade"
git push origin feature/nova-funcionalidade
# Criar Pull Request
```

### **Commit Convention**
- `feat:` Nova funcionalidade
- `fix:` Correção de bug
- `docs:` Documentação
- `style:` Formatação de código
- `refactor:` Refatoração
- `test:` Testes

---

## 📋 **Roadmap**

### **✅ Fase 1 - Fundação (Concluída)**
- ✅ Arquitetura SOLID/DDD/Clean
- ✅ Docker environment
- ✅ Autenticação dual
- ✅ API core structure
- ✅ Database design
- ✅ WebSocket setup

### **🟡 Fase 2 - Core Features (Em Progresso)**
- 🟡 Sistema de mensagens completo
- ⏳ WhatsApp Business API integration
- ⏳ Bot engine básico
- ⏳ File upload system

### **⏳ Fase 3 - Admin Panel**
- ⏳ TALL Stack interface
- ⏳ Real-time chat interface
- ⏳ User management
- ⏳ Analytics dashboard

### **⏳ Fase 4 - Advanced Features**
- ⏳ Video/audio calls
- ⏳ AI-powered responses
- ⏳ CRM integration
- ⏳ Mobile apps (Flutter)

---

## 📄 **Licença**

Este projeto está licenciado sob a **MIT License** - veja o arquivo [LICENSE](LICENSE) para detalhes.

---

## 👨‍💻 **Autor**

**@jonytonet**
- GitHub: [@jonytonet](https://github.com/jonytonet)
- LinkedIn: [Jony Tonet](https://linkedin.com/in/jonytonet)

---

## 🙏 **Agradecimentos**

- Laravel community pela framework excepcional
- Docker team pelo ambiente de desenvolvimento
- Open source contributors

---

**📊 Status do Projeto:** 🟢 **Em Desenvolvimento Ativo**  
**🎯 Progresso:** Semana 1/4 - Fundação sólida estabelecida  
**🏆 Qualidade:** 100% conformidade com padrões SOLID/DDD/Clean Architecture
