# 📋 Planos de Desenvolvimento Separados - EngChat

**Data:** 2025-07-05 13:55:43  
**Desenvolvedor:** @jonytonet  
**Separação:** Backend Admin vs Frontend Agentes

---

## 🏗️ **DIVISÃO DE RESPONSABILIDADES:**

### **🖥️ Laravel Backend + Admin (Gerentes/Administradores)**

-   ✅ **API completa** para todos os dados
-   ✅ **Painel administrativo** TALL Stack
-   ✅ **Gestão e configuração** do sistema
-   ✅ **Relatórios e métricas** (sem tempo real)
-   ✅ **Cadastros e configurações**
-   ✅ **Monitoramento de conversas**

### **📱 Flutter Frontend (Agentes)**

-   ✅ **Interface de atendimento** nativa
-   ✅ **Chat em tempo real** com clientes
-   ✅ **Filas e transferências**
-   ✅ **Status e disponibilidade**
-   ✅ **Templates e atalhos**
-   ✅ **Notificações push**

---

# 🖥️ PLANO LARAVEL BACKEND + ADMIN

**Foco:** API + Painel Administrativo para Gestão  
**Usuários:** Administradores e Gerentes  
**Objetivo:** Controle total do sistema + API para mobile

---

## 📋 **RESPONSABILIDADES DO LARAVEL:**

### **🔧 Backend (API):**

-   API RESTful completa
-   WebSocket para real-time
-   Processamento de mensagens WhatsApp
-   Sistema de filas e bot
-   Autenticação e autorização
-   Jobs e queues
-   Storage de arquivos

### **🖥️ Admin Panel (TALL Stack):**

-   Dashboard de supervisão
-   Gestão de usuários e agentes
-   Configuração de departamentos/times
-   Templates e shortcuts
-   Relatórios e métricas
-   Monitoramento de conversas
-   Configurações do sistema

---

## 🚀 **PLANO DE DESENVOLVIMENTO LARAVEL (30 DIAS):**

### **SEMANA 1 (05-11 Jul) - API Foundation**

#### **Dia 1-2: Setup e Estrutura**

-   [ ] **Ambiente Laravel 12**
    ```bash
    composer create-project laravel/laravel engchat-backend
    composer require laravel/sanctum laravel/reverb
    composer require livewire/livewire alpinejs/alpine tailwindcss/tailwindcss
    ```
-   [ ] **Migrations Críticas** (25 tabelas)
    ```php
    // Prioridade MÁXIMA
    users, roles, departments, teams, agents
    contacts, conversations, messages, channels
    categories, bot_flows, conversation_queue
    ```
-   [ ] **Models e Relationships**
    ```php
    User::class, Agent::class, Conversation::class
    Message::class, Contact::class, Category::class
    // Todos os relacionamentos Eloquent
    ```

#### **Dia 3-4: Autenticação e Core API**

-   [ ] **Sanctum Authentication**
    ```php
    POST /api/auth/login
    POST /api/auth/logout
    GET  /api/auth/me
    POST /api/auth/refresh
    ```
-   [ ] **Core API Endpoints**

    ```php
    // Agents
    GET    /api/agents
    PUT    /api/agents/{id}/status
    GET    /api/agents/{id}/availability

    // Conversations
    GET    /api/conversations
    POST   /api/conversations
    GET    /api/conversations/{id}
    PUT    /api/conversations/{id}/assign
    PUT    /api/conversations/{id}/transfer

    // Messages
    GET    /api/conversations/{id}/messages
    POST   /api/conversations/{id}/messages
    PUT    /api/messages/{id}/read

    // Queue
    GET    /api/queue/stats
    GET    /api/queue/conversations
    ```

#### **Dia 5-7: WhatsApp Integration**

-   [ ] **WhatsApp Business API**

    ```php
    app/Services/WhatsAppService.php

    // Webhook
    POST /api/webhooks/whatsapp

    // Methods
    sendMessage(), sendTemplate(), sendMedia()
    getProfile(), getMediaUrl()
    ```

-   [ ] **Message Processing**
    ```php
    app/Jobs/ProcessIncomingMessage.php
    app/Jobs/SendWhatsAppMessage.php
    app/Jobs/ProcessMediaMessage.php
    ```

### **SEMANA 2 (12-18 Jul) - Sistema de Bot e Filas**

#### **Dia 8-10: Bot Engine**

-   [ ] **Bot Core System**

    ```php
    app/Services/BotService.php
    app/Services/ConversationClassifier.php
    app/Services/QueueManager.php

    // Bot Flows
    WelcomeFlow, CategoryAssignment, DataCollection
    ```

-   [ ] **Queue Management**

    ```php
    app/Services/QueueService.php

    // Methods
    addToQueue(), getQueuePosition(), assignAgent()
    updateQueuePosition(), sendQueueNotification()
    ```

#### **Dia 11-13: WebSocket (Reverb)**

-   [ ] **Real-time Events**
    ```php
    // Events
    MessageSent, MessageRead, ConversationAssigned
    AgentStatusChanged, QueuePositionUpdated
    ConversationTransferred, TypingIndicator
    ```
-   [ ] **Broadcasting Setup**
    ```php
    // Channels
    conversation.{id}, agent.{id}, queue.{department}
    ```

#### **Dia 14: API Finalização**

-   [ ] **Endpoints Avançados**

    ```php
    // Templates
    GET    /api/templates
    POST   /api/templates/{id}/send

    // Shortcuts
    GET    /api/shortcuts
    POST   /api/shortcuts/{id}/use

    // Files
    POST   /api/upload
    GET    /api/files/{id}
    ```

### **SEMANA 3 (19-25 Jul) - Admin Panel (TALL Stack)**

#### **Dia 15-17: Layout e Dashboard**

-   [ ] **Layout Base**

    ```php
    resources/views/layouts/admin.blade.php

    // Componentes Livewire
    <livewire:sidebar />
    <livewire:header />
    <livewire:notifications />
    ```

-   [ ] **Dashboard Principal**
    ```php
    // app/Livewire/Dashboard.php
    - Estatísticas gerais (não tempo real)
    - Conversas por status
    - Agentes online/offline
    - Fila de espera atual
    ```
-   [ ] **Menu de Navegação**
    ```
    📊 Dashboard
    👥 Agentes
    💬 Conversas
    📋 Contatos
    🏢 Departamentos
    👨‍👩‍👧‍👦 Times
    📝 Templates
    ⚡ Atalhos
    🤖 Bot & Fluxos
    ⚙️ Configurações
    ```

#### **Dia 18-20: Gestão de Usuários**

-   [ ] **Gestão de Agentes**

    ```php
    // app/Livewire/Agents/
    AgentList.php, AgentForm.php, AgentDetail.php

    // Funcionalidades
    - Criar/editar agentes
    - Definir departamentos
    - Configurar capacidade
    - Histórico de performance
    ```

-   [ ] **Departamentos e Times**

    ```php
    // app/Livewire/Departments/
    DepartmentList.php, DepartmentForm.php

    // app/Livewire/Teams/
    TeamList.php, TeamForm.php, TeamMembers.php
    ```

#### **Dia 21-22: Gestão de Conversas**

-   [ ] **Monitor de Conversas**

    ```php
    // app/Livewire/Conversations/
    ConversationList.php, ConversationDetail.php
    ConversationMonitor.php

    // Funcionalidades
    - Visualizar todas as conversas
    - Filtros por status/agente/departamento
    - Detalhes da conversa
    - Histórico completo
    - Transferir conversas
    ```

-   [ ] **Gestão de Contatos**

    ```php
    // app/Livewire/Contacts/
    ContactList.php, ContactForm.php, ContactDetail.php

    // Funcionalidades
    - CRUD completo de contatos
    - Histórico de interações
    - Tags e classificações
    - Blacklist management
    ```

### **SEMANA 4 (26-31 Jul) - Configurações e Finalização**

#### **Dia 23-25: Templates e Automação**

-   [ ] **Gestão de Templates**

    ```php
    // app/Livewire/Templates/
    TemplateList.php, TemplateForm.php, TemplatePreview.php

    // Funcionalidades
    - CRUD de templates WhatsApp
    - Preview de templates
    - Variáveis e parâmetros
    - Associação com departamentos
    ```

-   [ ] **Atalhos e Bot**

    ```php
    // app/Livewire/Shortcuts/
    ShortcutList.php, ShortcutForm.php

    // app/Livewire/Bot/
    BotFlowList.php, BotFlowBuilder.php
    ```

#### **Dia 26-28: Relatórios**

-   [ ] **Relatórios Gerenciais**

    ```php
    // app/Livewire/Reports/
    AgentPerformance.php, ConversationStats.php
    QueueAnalytics.php, TemplateUsage.php

    // Métricas (sem tempo real)
    - Performance de agentes
    - Estatísticas de conversa
    - Tempo de resposta médio
    - Taxa de resolução
    - Uso de templates
    ```

#### **Dia 29-30: Deploy e Documentação**

-   [ ] **Production Setup**
-   [ ] **API Documentation**
-   [ ] **Admin User Guide**

---

## 📂 **ESTRUTURA DE ARQUIVOS LARAVEL:**

```
engchat-backend/
├── app/
│   ├── Http/
│   │   ├── Controllers/Api/
│   │   │   ├── AuthController.php
│   │   │   ├── AgentController.php
│   │   │   ├── ConversationController.php
│   │   │   ├── MessageController.php
│   │   │   ├── TemplateController.php
│   │   │   └── QueueController.php
│   │   └── Controllers/
│   │       └── WebhookController.php
│   ├── Livewire/
│   │   ├── Dashboard.php
│   │   ├── Agents/
│   │   ├── Conversations/
│   │   ├── Contacts/
│   │   ├── Templates/
│   │   └── Reports/
│   ├── Services/
│   │   ├── WhatsAppService.php
│   │   ├── BotService.php
│   │   ├── QueueService.php
│   │   └── NotificationService.php
│   ├── Jobs/
│   │   ├── ProcessIncomingMessage.php
│   │   ├── SendWhatsAppMessage.php
│   │   └── UpdateQueuePosition.php
│   └── Models/
├── resources/views/
│   ├── layouts/
│   │   └── admin.blade.php
│   ├── livewire/
│   └── components/
└── routes/
    ├── api.php
    ├── web.php
    └── channels.php
```

---
