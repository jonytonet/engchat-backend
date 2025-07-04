# 📝 MODELS DOCUMENTATION - EngChat

**Projeto:** EngChat MVP  
**Data:** 2025-07-04  

---

## 🏗️ MODELS IMPLEMENTADOS

### ✅ **Modelos Principais Criados/Atualizados:**

#### 1. **User** (`app/Models/User.php`)
- ✅ **Documentação Swagger completa**
- ✅ **Campos atualizados**: avatar, status, role_id, department_id, manager_id, last_activity, timezone, language, is_active
- ✅ **Relationships**: role, department, manager, managedUsers, categories, assignedConversations, messages, transfersMade, transfersReceived, agentMetrics
- ✅ **Scopes**: active, byStatus, online, byRole
- ✅ **Methods**: canTakeMoreConversations(), hasPermission(), getAvatarUrlAttribute, getIsOnlineAttribute
- ✅ **Traits**: HasFactory, Notifiable, HasApiTokens

#### 2. **Role** (`app/Models/Role.php`)
- ✅ **Documentação Swagger completa**
- ✅ **Campos**: name, description, permissions (JSON), can_transfer, can_close_tickets, max_simultaneous_chats
- ✅ **Relationships**: users
- ✅ **Scopes**: canTransfer, canCloseTickets
- ✅ **Methods**: hasPermission(), addPermission(), removePermission(), isAdmin(), isManager(), isAgent()

#### 3. **Department** (`app/Models/Department.php`)
- ✅ **Documentação Swagger completa**
- ✅ **Campos**: name, description, manager_id, is_active, working_hours (JSON), auto_assignment_enabled
- ✅ **Relationships**: manager, users, activeUsers, onlineUsers
- ✅ **Scopes**: active, withAutoAssignment
- ✅ **Methods**: isWithinWorkingHours(), getAvailableAgents(), getNextAgent(), getStats()

#### 4. **Category** (`app/Models/Category.php`)
- ✅ **Documentação Swagger completa**
- ✅ **Campos atualizados**: name, description, color, parent_id, priority, estimated_time, auto_responses (JSON), requires_specialist, is_active
- ✅ **Relationships**: parent, children, conversations, specialists, users, keywords, autoResponses
- ✅ **Scopes**: active, root, byPriority, requiresSpecialist, byPriorityOrder
- ✅ **Methods**: isActive(), requiresSpecialist(), isRoot(), hasChildren(), getAvailableSpecialists(), getFullPathAttribute, getStats(), getAverageResolutionTime(), autoAssignConversation()

#### 5. **Channel** (`app/Models/Channel.php`)
- ✅ **Documentação Swagger completa**
- ✅ **Campos atualizados**: name, type (enum), configuration (JSON), is_active, priority, working_hours (JSON), auto_response_enabled
- ✅ **Relationships**: conversations, integrations
- ✅ **Scopes**: active, byType, withAutoResponse, byPriority
- ✅ **Methods**: isActive(), isWhatsApp(), isTelegram(), isWeb(), hasAutoResponse(), isWithinWorkingHours(), getConfig(), setConfig(), getStats(), getPrimaryIntegration(), sendMessage()

#### 6. **Contact** (`app/Models/Contact.php`)
- ✅ **Documentação Swagger completa**
- ✅ **Campos atualizados**: name, email, phone, display_name, company, document, tags (JSON), priority (enum), blacklisted, blacklist_reason, preferred_language, timezone, last_interaction, total_interactions
- ✅ **Relationships**: conversations, messages, customFields, notes, latestConversation, activeConversations
- ✅ **Scopes**: notBlacklisted, blacklisted, byPriority, vip, search
- ✅ **Methods**: isBlacklisted(), isVip(), hasActiveConversations(), blacklist(), unblacklist(), getDisplayNameAttribute, updateLastInteraction(), addTag(), removeTag(), getStats(), getPreferredContactMethod()

---

## 🔧 FUNCIONALIDADES IMPLEMENTADAS

### **Swagger Documentation:**
- ✅ Todos os modelos têm documentação `@OA\Schema` completa
- ✅ Propriedades com tipos, formatos, exemplos e descrições
- ✅ Campos obrigatórios e opcionais definidos
- ✅ Enums e arrays documentados corretamente

### **Relationships Eloquent:**
- ✅ **One-to-Many**: User → Conversations, Category → Conversations, etc.
- ✅ **Many-to-Many**: User ↔ Categories (com pivot table user_categories)
- ✅ **Self-referencing**: Category → parent/children, User → manager/managedUsers
- ✅ **Polymorphic**: Message pode ser de User ou Contact

### **Scopes Avançados:**
- ✅ **Status filtering**: active, online, blacklisted
- ✅ **Search scopes**: busca por múltiplos campos
- ✅ **Business logic scopes**: vip, requiresSpecialist, withAutoAssignment

### **Business Logic Methods:**
- ✅ **Permission system**: hasPermission() nos Users e Roles
- ✅ **Assignment logic**: canTakeMoreConversations(), getNextAgent()
- ✅ **Working hours**: isWithinWorkingHours() para Departments e Channels
- ✅ **Statistics**: getStats() para todos os modelos principais
- ✅ **Auto-assignment**: autoAssignConversation() para Categories

### **Data Management:**
- ✅ **JSON fields**: permissions, configuration, tags, working_hours
- ✅ **Enums**: status, priority, channel type
- ✅ **Timestamps**: created_at, updated_at, last_activity, last_interaction
- ✅ **Soft deletes**: Categories, Channels, Contacts

---

## 🚨 MODELS PENDENTES (Próximos Passos)

### **Críticos para MVP:**

#### 1. **Conversation** (Já existe, precisa atualizar)
- conversation_id, contact_id, channel_id, assigned_to, category_id
- status, priority, satisfaction_rating, started_at, closed_at
- first_response_time, resolution_time, tags, is_bot_handled

#### 2. **Message** (Já existe, precisa atualizar)
- conversation_id, sender_type, message_type, content, metadata
- delivered_at, reply_to_id, is_internal

#### 3. **Novos Models a Criar:**
- **ChannelIntegration**: integrations table
- **CategoryKeyword**: category_keywords table
- **ContactCustomField**: contact_custom_fields table
- **ContactNote**: contact_notes table
- **UserCategory**: user_categories pivot model
- **ConversationTransfer**: conversation_transfers table
- **MessageAttachment**: message_attachments table
- **AutoResponse**: auto_responses table
- **BotFlow**: bot_flows table
- **MessageTemplate**: message_templates table
- **ConversationMetric**: conversation_metrics table
- **AgentMetric**: agent_metrics table

### **Para Futuras Versões:**
- **BroadcastCampaign**: broadcast_campaigns table
- **BroadcastRecipient**: broadcast_recipients table
- **VideoRoom**: video_rooms table
- **VideoSession**: video_sessions table
- **MeetingParticipant**: meeting_participants table

---

## 📊 ARQUITETURA IMPLEMENTADA

### **Design Patterns:**
- ✅ **Repository Pattern**: Ready for repositories layer
- ✅ **Factory Pattern**: HasFactory em todos os models
- ✅ **Observer Pattern**: Ready for model observers
- ✅ **Strategy Pattern**: Channel-specific implementations

### **SOLID Principles:**
- ✅ **Single Responsibility**: Cada model tem responsabilidade específica
- ✅ **Open/Closed**: Extensível via relationships e scopes
- ✅ **Liskov Substitution**: Interfaces consistentes
- ✅ **Interface Segregation**: Methods específicos por funcionalidade
- ✅ **Dependency Inversion**: Ready for service layer

### **DDD Concepts:**
- ✅ **Entities**: User, Contact, Conversation, etc.
- ✅ **Value Objects**: Enums (status, priority, etc.)
- ✅ **Aggregates**: User + Role + Department
- ✅ **Domain Services**: Business logic methods in models

---

## 🔍 PRÓXIMAS AÇÕES

### **Imediatas:**
1. ✅ **Instalar Laravel Sanctum**: `composer require laravel/sanctum`
2. ✅ **Criar models restantes**: Conversation, Message updates
3. ✅ **Criar pivot models**: UserCategory, MessageAttachment, etc.
4. ✅ **Executar migrations**: Testar estrutura do banco
5. ✅ **Criar factories**: Para testing e seeding

### **Médio Prazo:**
1. ✅ **Model Observers**: Para logs e eventos automáticos
2. ✅ **Custom Casts**: Para campos JSON complexos
3. ✅ **Policy Classes**: Para authorization
4. ✅ **API Resources**: Para serialização Swagger
5. ✅ **Integration Tests**: Para relationships e business logic

---

**Status**: ✅ **6/6 Modelos Principais Implementados**  
**Próximo**: Criar modelos restantes e executar migrations  
**Tempo Estimado**: 2-3 horas para completar todos os models
