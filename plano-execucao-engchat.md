# 📋 Plano de Execução - EngChat MVP (30 dias)

**Projeto:** EngChat - Plataforma de Atendimento MultiCanal  
**Desenvolvedor:** @jonytonet  
**Data Início:** 2025-07-04  
**Meta:** MVP Chat funcional em 30 dias  

---

## 🏗️ ESTRUTURA DO PROJETO

### Repositórios Sugeridos:
```
engchat-backend/     # Laravel API + Admin
engchat-mobile/      # Flutter Apps (Android/iOS/Windows)
engchat-docs/        # Documentação e Deploy
```

---

## 🚀 FLUXO DE TRABALHO - LARAVEL BACKEND

### **SEMANA 1 (04-10 Jul)** - Fundação e Infraestrutura

#### **Dia 1-2: Setup Inicial**
- [ ] **Setup Laravel 12**
  ```bash
  composer create-project laravel/laravel engchat-backend
  cd engchat-backend
  composer require laravel/sanctum laravel/reverb
  ```
- [ ] **Configuração do Ambiente**
  - Docker Compose (Laravel + MariaDB + Redis + RabbitMQ)
  - Variáveis de ambiente (.env)
  - Configuração de filas e WebSocket
- [ ] **Database Migrations**
  - Criar migrations para todas as tabelas do sistema
  - Seeders para dados iniciais (roles, categorias, etc.)

#### **Dia 3-4: Autenticação e Usuários**
- [ ] **Sistema de Autenticação**
  ```php
  // API Routes
  POST /api/auth/login
  POST /api/auth/logout
  GET  /api/auth/me
  POST /api/auth/refresh
  ```
- [ ] **Models e Relationships**
  - User, Role, Department, Category
  - Contact, Conversation, Message
  - Definir todos os relacionamentos Eloquent
- [ ] **Middlewares**
  - Authentication, Role-based permissions
  - Rate limiting para API

#### **Dia 5-7: Core Chat API**
- [ ] **API Endpoints Básicos**
  ```php
  // Conversations
  GET    /api/conversations
  POST   /api/conversations
  GET    /api/conversations/{id}
  PUT    /api/conversations/{id}/assign
  
  // Messages
  GET    /api/conversations/{id}/messages
  POST   /api/conversations/{id}/messages
  POST   /api/messages/{id}/read
  
  // Contacts
  GET    /api/contacts
  POST   /api/contacts
  GET    /api/contacts/{id}
  ```
- [ ] **WebSocket Events (Reverb)**
  ```php
  // Events
  MessageSent, MessageRead, ConversationAssigned
  UserStatusChanged, TypingIndicator
  ```
- [ ] **File Upload System**
  - Storage configuration
  - Image/Audio processing
  - Security scanning

### **SEMANA 2 (11-17 Jul)** - Integração WhatsApp e Bot

#### **Dia 8-10: WhatsApp Integration**
- [ ] **WhatsApp Business API**
  ```php
  // Services
  app/Services/WhatsAppService.php
  
  // Webhooks
  POST /api/webhooks/whatsapp
  
  // Methods
  sendMessage(), sendMedia(), getProfile()
  ```
- [ ] **Webhook Handler**
  - Receber mensagens do WhatsApp
  - Processar diferentes tipos (text, image, audio)
  - Queue jobs para processamento assíncrono

#### **Dia 11-13: Sistema de Bot**
- [ ] **Bot Engine**
  ```php
  app/Services/BotService.php
  app/Services/CategoryClassifier.php
  
  // Fluxos
  WelcomeFlow, CategoryAssignment, BusinessHours
  ```
- [ ] **Auto Responses**
  - Sistema de templates
  - Conditional responses
  - Keyword matching
- [ ] **Queue System**
  ```php
  // Jobs
  ProcessIncomingMessage, SendWhatsAppMessage
  ClassifyConversation, AssignToAgent
  ```

#### **Dia 14: Testes e Refinamentos**
- [ ] **Testes Unitários**
  - AuthenticationTest, ConversationTest
  - WhatsAppServiceTest, BotServiceTest
- [ ] **Documentação API**
  - Swagger/OpenAPI documentation
  - Postman collection

### **SEMANA 3 (18-24 Jul)** - Painel Admin e Métricas

#### **Dia 15-17: Admin Panel (TALL Stack)**
- [ ] **Layout Base**
  ```php
  resources/views/layouts/admin.blade.php
  
  // Components
  <x-sidebar />
  <x-header />
  <x-chat-widget />
  ```
- [ ] **Dashboard Principal**
  - Métricas em tempo real
  - Gráficos de atendimento
  - Lista de conversas ativas
- [ ] **Gestão de Usuários**
  ```php
  // Livewire Components
  UserList, UserForm, RoleManager
  ```

#### **Dia 18-20: Gestão de Conversas**
- [ ] **Interface de Chat Admin**
  ```php
  // Livewire Components
  ConversationList, ChatWindow, ContactInfo
  ```
- [ ] **Sistema de Transferência**
  - Assign/Unassign conversations
  - Transfer between agents
  - Notification system
- [ ] **Templates e Respostas**
  - CRUD de templates
  - Quick replies
  - Category-based responses

#### **Dia 21: Relatórios e Métricas**
- [ ] **Analytics Dashboard**
  ```php
  // Livewire Components
  MetricsDashboard, ReportsGenerator
  
  // Metrics
  Response time, Resolution rate, Agent performance
  ```

### **SEMANA 4 (25-31 Jul)** - Finalização e Deploy

#### **Dia 22-24: Integrações Finais**
- [ ] **Broadcasting Real-time**
  ```php
  // Reverb configuration
  New messages, Status updates, Assignments
  ```
- [ ] **Notification System**
  - Email notifications
  - Internal notifications
  - Push notification prep for mobile

#### **Dia 25-27: Polimento e Testes**
- [ ] **Performance Optimization**
  - Database indexes
  - Query optimization
  - Cache implementation
- [ ] **Security Hardening**
  - Input validation
  - CSRF protection
  - API rate limiting

#### **Dia 28-30: Deploy e Documentação**
- [ ] **Production Setup**
  ```dockerfile
  # Docker production
  # Nginx configuration
  # SSL setup
  ```
- [ ] **API Documentation**
- [ ] **Admin User Guide**

---

## 📱 FLUXO DE TRABALHO - FLUTTER APPS

### **SEMANA 1 (04-10 Jul)** - Setup e Estrutura

#### **Dia 1-2: Projeto Base**
- [ ] **Flutter Setup**
  ```bash
  flutter create engchat_mobile
  cd engchat_mobile
  
  # Dependencies
  flutter pub add riverpod flutter_riverpod
  flutter pub add socket_io_client http dio
  flutter pub add firebase_core firebase_messaging
  flutter pub add image_picker file_picker
  ```
- [ ] **Estrutura de Pastas**
  ```
  lib/
  ├── core/
  │   ├── constants/
  │   ├── utils/
  │   └── services/
  ├── features/
  │   ├── auth/
  │   ├── chat/
  │   └── contacts/
  ├── shared/
  │   ├── widgets/
  │   ├── models/
  │   └── providers/
  └── main.dart
  ```

#### **Dia 3-4: Autenticação**
- [ ] **Auth Service**
  ```dart
  // lib/core/services/auth_service.dart
  class AuthService {
    Future<User?> login(String email, String password);
    Future<void> logout();
    Future<User?> getCurrentUser();
  }
  ```
- [ ] **Riverpod Providers**
  ```dart
  // lib/shared/providers/auth_provider.dart
  final authProvider = StateNotifierProvider<AuthNotifier, AuthState>();
  
  // lib/shared/providers/user_provider.dart
  final userProvider = FutureProvider<User?>((ref) async {});
  ```
- [ ] **Auth Screens**
  ```dart
  // lib/features/auth/screens/
  login_screen.dart
  splash_screen.dart
  ```

#### **Dia 5-7: Core Chat Structure**
- [ ] **Models**
  ```dart
  // lib/shared/models/
  user.dart, conversation.dart, message.dart
  contact.dart, attachment.dart
  ```
- [ ] **API Service**
  ```dart
  // lib/core/services/api_service.dart
  class ApiService {
    Future<List<Conversation>> getConversations();
    Future<List<Message>> getMessages(String conversationId);
    Future<Message> sendMessage(String conversationId, String content);
  }
  ```

### **SEMANA 2 (11-17 Jul)** - Chat Interface

#### **Dia 8-10: Chat UI**
- [ ] **Chat Screens**
  ```dart
  // lib/features/chat/screens/
  conversations_screen.dart  // Lista de conversas
  chat_screen.dart          // Interface de chat
  contact_info_screen.dart  // Detalhes do contato
  ```
- [ ] **Chat Widgets**
  ```dart
  // lib/features/chat/widgets/
  message_bubble.dart       // Bolha de mensagem
  typing_indicator.dart     // Indicador digitando
  attachment_viewer.dart    // Visualizador de anexos
  ```

#### **Dia 11-13: Real-time Communication**
- [ ] **Socket Service**
  ```dart
  // lib/core/services/socket_service.dart
  class SocketService {
    void connect(String token);
    void joinRoom(String conversationId);
    Stream<Message> messageStream();
    void sendMessage(Message message);
  }
  ```
- [ ] **Riverpod Integration**
  ```dart
  // lib/shared/providers/chat_provider.dart
  final chatProvider = StateNotifierProvider<ChatNotifier, ChatState>();
  final messagesProvider = StateNotifierProvider.family<MessagesNotifier, 
    AsyncValue<List<Message>>, String>();
  ```

#### **Dia 14: File Handling**
- [ ] **Media Services**
  ```dart
  // lib/core/services/media_service.dart
  class MediaService {
    Future<File?> pickImage();
    Future<File?> recordAudio();
    Future<String> uploadFile(File file);
  }
  ```

### **SEMANA 3 (18-24 Jul)** - Features Avançadas

#### **Dia 15-17: Notifications**
- [ ] **FCM Setup**
  ```dart
  // lib/core/services/notification_service.dart
  class NotificationService {
    Future<void> initialize();
    Future<String?> getToken();
    void handleMessage(RemoteMessage message);
  }
  ```
- [ ] **Local Notifications**
  - Background message handling
  - Notification tap actions
  - Badge counting

#### **Dia 18-20: Agent Features**
- [ ] **Agent Dashboard**
  ```dart
  // lib/features/agent/screens/
  dashboard_screen.dart     // Overview do agente
  queue_screen.dart         // Fila de atendimento
  transfer_screen.dart      // Transferência de conversas
  ```
- [ ] **Quick Actions**
  ```dart
  // lib/features/agent/widgets/
  quick_replies.dart        // Respostas rápidas
  contact_actions.dart      // Ações do contato
  conversation_stats.dart   // Estatísticas
  ```

#### **Dia 21: Offline Support**
- [ ] **Local Storage**
  ```dart
  // lib/core/services/storage_service.dart
  class StorageService {
    Future<void> saveConversations(List<Conversation> conversations);
    Future<List<Conversation>> getOfflineConversations();
    Future<void> syncPendingMessages();
  }
  ```

### **SEMANA 4 (25-31 Jul)** - Polimento e Deploy

#### **Dia 22-24: Platform Specific**
- [ ] **Android Optimizations**
  - Background services
  - Notification channels
  - Permission handling
- [ ] **iOS Optimizations**
  - Background app refresh
  - CallKit integration prep
  - iOS specific permissions
- [ ] **Windows Desktop**
  - Desktop-specific UI adaptations
  - Window management
  - System tray integration

#### **Dia 25-27: Testing & Polish**
- [ ] **Widget Tests**
  ```dart
  // test/widget_test.dart
  testWidgets('Chat screen displays messages', (tester) async {});
  ```
- [ ] **Integration Tests**
  ```dart
  // integration_test/app_test.dart
  testWidgets('Full chat flow', (tester) async {});
  ```
- [ ] **Performance Optimization**
  - Image caching
  - List optimization
  - Memory management

#### **Dia 28-30: Build & Deploy**
- [ ] **Build Preparation**
  ```bash
  # Android
  flutter build appbundle --release
  
  # iOS
  flutter build ipa --release
  
  # Windows
  flutter build windows --release
  ```
- [ ] **Store Preparation**
  - App icons, screenshots
  - Store descriptions
  - Privacy policy
- [ ] **Internal Testing**
  - TestFlight (iOS)
  - Internal testing (Android)

---

## 🔄 WORKFLOW PARALELO

### **Dependências Entre Projetos:**

#### **Laravel → Flutter**
- **Dia 7:** API endpoints básicos → Flutter pode começar integração
- **Dia 10:** WebSocket events → Flutter real-time implementation
- **Dia 14:** WhatsApp integration → Flutter pode testar flow completo

#### **Flutter → Laravel**
- **Dia 15:** Flutter auth flow → Laravel pode ajustar API responses
- **Dia 20:** Mobile UX feedback → Laravel admin interface improvements

### **Sincronização Diária:**
- **Daily Stand-up:** 09:00 UTC (updates, blockers, dependencies)
- **Integration Testing:** 18:00 UTC (test Laravel + Flutter together)

---

## 📊 MARCOS PRINCIPAIS (MILESTONES)

### **Marco 1 - Dia 7:** 🎯
- ✅ Laravel API básica funcionando
- ✅ Flutter apps buildando e rodando
- ✅ Autenticação end-to-end

### **Marco 2 - Dia 14:** 💬
- ✅ Chat básico funcionando
- ✅ WhatsApp integration live
- ✅ Real-time messaging

### **Marco 3 - Dia 21:** 🏢
- ✅ Admin panel operacional
- ✅ Agent features completas
- ✅ Bot básico funcionando

### **Marco 4 - Dia 30:** 🚀
- ✅ Sistema completo em produção
- ✅ Apps buildados para deploy
- ✅ Documentação completa

---

## 🔧 FERRAMENTAS DE DESENVOLVIMENTO

### **Laravel Stack:**
```bash
# Development
Laravel Herd / Valet
Tinker, Telescope, Debugbar
PHPUnit, Pest
Laravel Pint (code style)

# Database
TablePlus / Sequel Pro
Redis Desktop Manager
```

### **Flutter Stack:**
```bash
# Development
Flutter Inspector
Dart DevTools
Flutter pub deps

# Testing
Flutter Driver
Golden tests
```

### **Shared Tools:**
```bash
# Version Control
Git + Conventional Commits

# Documentation
Notion / GitBook

# Communication
Slack / Discord for daily updates
```

---

## ⚠️ RISCOS E MITIGAÇÕES

### **Riscos Técnicos:**
1. **WhatsApp API delays** → Usar simulador para desenvolvimento paralelo
2. **WebSocket performance** → Load testing desde dia 14
3. **Flutter build issues** → Setup CI/CD desde dia 7

### **Riscos de Prazo:**
1. **Scope creep** → Manter foco no MVP, features extras para v2
2. **Integration delays** → Daily sync meetings obrigatórios
3. **Performance issues** → Testing continuo, não deixar para final

---

## 📋 CHECKLIST FINAL (Dia 30)

### **Laravel Backend:**
- [ ] API completa documentada
- [ ] WhatsApp integration funcionando
- [ ] Admin panel operacional
- [ ] Bot básico ativo
- [ ] Deploy em produção
- [ ] Backup automatizado
- [ ] Monitoring ativo

### **Flutter Apps:**
- [ ] Android app built (.aab)
- [ ] iOS app built (.ipa)
- [ ] Windows app built (.exe)
- [ ] Real-time chat funcionando
- [ ] Push notifications ativas
- [ ] Offline support básico
- [ ] Performance otimizada

### **Integração:**
- [ ] End-to-end testing completo
- [ ] Load testing (50+ usuarios simultâneos)
- [ ] Security audit básico
- [ ] Documentação de usuário
- [ ] Training materials prontos

---

**Próximos Passos:** Após MVP, iniciar desenvolvimento das melhorias futuras (videoconferência, IA, CRM completo) baseado no feedback dos usuários reais.