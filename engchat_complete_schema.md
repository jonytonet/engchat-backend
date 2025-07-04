# EngChat - Schema Completo do Banco de Dados


## 🎯 SCHEMA COMPLETO PROPOSTO

### 1. 👥 Gestão de Usuários e Permissões

```sql
-- ATUALIZAR: users (adicionar campos faltando)
ALTER TABLE users ADD COLUMN avatar VARCHAR(255) NULL AFTER email;
ALTER TABLE users ADD COLUMN status ENUM('online', 'offline', 'busy', 'away') DEFAULT 'offline' AFTER avatar;
ALTER TABLE users ADD COLUMN role_id BIGINT UNSIGNED NULL AFTER status;
ALTER TABLE users ADD COLUMN department_id BIGINT UNSIGNED NULL AFTER role_id;
ALTER TABLE users ADD COLUMN manager_id BIGINT UNSIGNED NULL AFTER department_id;
ALTER TABLE users ADD COLUMN last_activity TIMESTAMP NULL AFTER manager_id;
ALTER TABLE users ADD COLUMN timezone VARCHAR(50) DEFAULT 'America/Sao_Paulo' AFTER last_activity;
ALTER TABLE users ADD COLUMN language VARCHAR(10) DEFAULT 'pt-BR' AFTER timezone;
ALTER TABLE users ADD COLUMN is_active BOOLEAN DEFAULT true AFTER language;
ALTER TABLE users ADD INDEX idx_status (status);
ALTER TABLE users ADD INDEX idx_role_id (role_id);
ALTER TABLE users ADD INDEX idx_department_id (department_id);
ALTER TABLE users ADD INDEX idx_last_activity (last_activity);

-- CRIAR: roles
CREATE TABLE roles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    description TEXT NULL,
    permissions JSON NULL,
    can_transfer BOOLEAN DEFAULT true,
    can_close_tickets BOOLEAN DEFAULT true,
    max_simultaneous_chats INT DEFAULT 5,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX idx_name (name)
);

-- CRIAR: departments
CREATE TABLE departments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT NULL,
    manager_id BIGINT UNSIGNED NULL,
    is_active BOOLEAN DEFAULT true,
    working_hours JSON NULL,
    auto_assignment_enabled BOOLEAN DEFAULT true,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (manager_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_manager_id (manager_id),
    INDEX idx_is_active (is_active)
);

-- CRIAR: user_categories (especialidades dos usuários)
CREATE TABLE user_categories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    category_id BIGINT UNSIGNED NOT NULL,
    priority_level INT DEFAULT 1,
    is_specialist BOOLEAN DEFAULT false,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_category (user_id, category_id),
    INDEX idx_priority_level (priority_level)
);
```

### 2. 📋 Categorias e Classificação

```sql
-- CRIAR: categories
CREATE TABLE categories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT NULL,
    color VARCHAR(20) DEFAULT '#667eea',
    parent_id BIGINT UNSIGNED NULL,
    priority INT DEFAULT 1,
    estimated_time INT NULL COMMENT 'Tempo estimado em minutos',
    auto_responses JSON NULL,
    requires_specialist BOOLEAN DEFAULT false,
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL,
    INDEX idx_parent_id (parent_id),
    INDEX idx_priority (priority),
    INDEX idx_is_active (is_active)
);

-- CRIAR: category_keywords
CREATE TABLE category_keywords (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id BIGINT UNSIGNED NOT NULL,
    keyword VARCHAR(100) NOT NULL,
    weight INT DEFAULT 1,
    is_active BOOLEAN DEFAULT true,
    language VARCHAR(10) DEFAULT 'pt-BR',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    INDEX idx_category_id (category_id),
    INDEX idx_keyword (keyword),
    INDEX idx_weight (weight)
);
```

### 3. 👤 Contatos e Clientes (ATUALIZAR)

```sql
-- ATUALIZAR: contacts (adicionar campos faltando)
ALTER TABLE contacts ADD COLUMN company VARCHAR(255) NULL AFTER display_name;
ALTER TABLE contacts ADD COLUMN document VARCHAR(50) NULL AFTER company;
ALTER TABLE contacts ADD COLUMN tags JSON NULL AFTER document;
ALTER TABLE contacts ADD COLUMN priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium' AFTER tags;
ALTER TABLE contacts ADD COLUMN blacklisted BOOLEAN DEFAULT false AFTER priority;
ALTER TABLE contacts ADD COLUMN blacklist_reason TEXT NULL AFTER blacklisted;
ALTER TABLE contacts ADD COLUMN preferred_language VARCHAR(10) DEFAULT 'pt-BR' AFTER blacklist_reason;
ALTER TABLE contacts ADD COLUMN timezone VARCHAR(50) DEFAULT 'America/Sao_Paulo' AFTER preferred_language;
ALTER TABLE contacts ADD COLUMN last_interaction TIMESTAMP NULL AFTER timezone;
ALTER TABLE contacts ADD COLUMN total_interactions INT DEFAULT 0 AFTER last_interaction;
ALTER TABLE contacts ADD INDEX idx_priority (priority);
ALTER TABLE contacts ADD INDEX idx_blacklisted (blacklisted);
ALTER TABLE contacts ADD INDEX idx_last_interaction (last_interaction);

-- CRIAR: contact_custom_fields
CREATE TABLE contact_custom_fields (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    contact_id BIGINT UNSIGNED NOT NULL,
    field_name VARCHAR(100) NOT NULL,
    field_value TEXT NULL,
    field_type ENUM('text', 'number', 'date', 'boolean', 'json') DEFAULT 'text',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (contact_id) REFERENCES contacts(id) ON DELETE CASCADE,
    INDEX idx_contact_id (contact_id),
    INDEX idx_field_name (field_name)
);

-- CRIAR: contact_notes
CREATE TABLE contact_notes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    contact_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    note TEXT NOT NULL,
    is_private BOOLEAN DEFAULT false,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (contact_id) REFERENCES contacts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_contact_id (contact_id),
    INDEX idx_user_id (user_id),
    INDEX idx_created_at (created_at)
);
```

### 4. 💬 Conversas e Mensagens (CRÍTICO PARA MVP)

```sql
-- CRIAR: conversations (separar de messages)
CREATE TABLE conversations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    contact_id BIGINT UNSIGNED NOT NULL,
    channel_id BIGINT UNSIGNED NOT NULL,
    assigned_to BIGINT UNSIGNED NULL,
    category_id BIGINT UNSIGNED NULL,
    status ENUM('open', 'pending', 'closed') DEFAULT 'open',
    priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
    satisfaction_rating INT NULL,
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    closed_at TIMESTAMP NULL,
    first_response_time INT NULL COMMENT 'Tempo em segundos',
    resolution_time INT NULL COMMENT 'Tempo em segundos',
    tags JSON NULL,
    is_bot_handled BOOLEAN DEFAULT false,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (contact_id) REFERENCES contacts(id) ON DELETE CASCADE,
    FOREIGN KEY (channel_id) REFERENCES channels(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    INDEX idx_contact_id (contact_id),
    INDEX idx_channel_id (channel_id),
    INDEX idx_assigned_to (assigned_to),
    INDEX idx_status (status),
    INDEX idx_priority (priority),
    INDEX idx_started_at (started_at)
);

-- ATUALIZAR: messages (adicionar conversation_id e outros campos)
ALTER TABLE messages ADD COLUMN conversation_id BIGINT UNSIGNED NULL AFTER id;
ALTER TABLE messages ADD COLUMN sender_type ENUM('user', 'contact', 'bot', 'system') DEFAULT 'user' AFTER conversation_id;
ALTER TABLE messages ADD COLUMN message_type ENUM('text', 'image', 'audio', 'video', 'document', 'location', 'contact') DEFAULT 'text' AFTER sender_type;
ALTER TABLE messages ADD COLUMN content TEXT NULL AFTER message_type;
ALTER TABLE messages ADD COLUMN metadata JSON NULL AFTER content;
ALTER TABLE messages ADD COLUMN delivered_at TIMESTAMP NULL AFTER is_read;
ALTER TABLE messages ADD COLUMN reply_to_id BIGINT UNSIGNED NULL AFTER delivered_at;
ALTER TABLE messages ADD COLUMN is_internal BOOLEAN DEFAULT false AFTER reply_to_id;
ALTER TABLE messages ADD FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE CASCADE;
ALTER TABLE messages ADD FOREIGN KEY (reply_to_id) REFERENCES messages(id) ON DELETE SET NULL;
ALTER TABLE messages ADD INDEX idx_conversation_id (conversation_id);
ALTER TABLE messages ADD INDEX idx_sender_type (sender_type);
ALTER TABLE messages ADD INDEX idx_message_type (message_type);
ALTER TABLE messages ADD INDEX idx_is_internal (is_internal);

-- CRIAR: message_attachments
CREATE TABLE message_attachments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    message_id BIGINT UNSIGNED NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size BIGINT NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    thumbnail_path VARCHAR(500) NULL,
    duration INT NULL COMMENT 'Duração em segundos para áudio/vídeo',
    is_scanned BOOLEAN DEFAULT false,
    scan_result JSON NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (message_id) REFERENCES messages(id) ON DELETE CASCADE,
    INDEX idx_message_id (message_id),
    INDEX idx_mime_type (mime_type)
);

-- CRIAR: conversation_transfers
CREATE TABLE conversation_transfers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    conversation_id BIGINT UNSIGNED NOT NULL,
    from_user_id BIGINT UNSIGNED NULL,
    to_user_id BIGINT UNSIGNED NOT NULL,
    reason TEXT NULL,
    notes TEXT NULL,
    transferred_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE CASCADE,
    FOREIGN KEY (from_user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (to_user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_conversation_id (conversation_id),
    INDEX idx_from_user_id (from_user_id),
    INDEX idx_to_user_id (to_user_id),
    INDEX idx_transferred_at (transferred_at)
);
```

### 5. 📺 Canais de Atendimento (CRÍTICO PARA MVP)

```sql
-- CRIAR: channels
CREATE TABLE channels (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    type ENUM('whatsapp', 'telegram', 'web', 'facebook', 'instagram') NOT NULL,
    configuration JSON NULL,
    is_active BOOLEAN DEFAULT true,
    priority INT DEFAULT 1,
    working_hours JSON NULL,
    auto_response_enabled BOOLEAN DEFAULT true,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX idx_type (type),
    INDEX idx_is_active (is_active),
    INDEX idx_priority (priority)
);

-- CRIAR: channel_integrations
CREATE TABLE channel_integrations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    channel_id BIGINT UNSIGNED NOT NULL,
    integration_type VARCHAR(50) NOT NULL,
    api_key TEXT NULL,
    webhook_url VARCHAR(500) NULL,
    settings JSON NULL,
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (channel_id) REFERENCES channels(id) ON DELETE CASCADE,
    INDEX idx_channel_id (channel_id),
    INDEX idx_integration_type (integration_type)
);
```

### 6. 🤖 Bot e Automação (CRÍTICO PARA MVP)

```sql
-- CRIAR: bot_flows
CREATE TABLE bot_flows (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    trigger_type ENUM('welcome', 'keyword', 'schedule', 'event') NOT NULL,
    conditions JSON NULL,
    actions JSON NULL,
    is_active BOOLEAN DEFAULT true,
    priority INT DEFAULT 1,
    success_rate DECIMAL(5,2) DEFAULT 0,
    usage_count INT DEFAULT 0,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX idx_trigger_type (trigger_type),
    INDEX idx_is_active (is_active),
    INDEX idx_priority (priority)
);

-- CRIAR: auto_responses
CREATE TABLE auto_responses (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id BIGINT UNSIGNED NULL,
    trigger_keyword VARCHAR(255) NOT NULL,
    response_text TEXT NOT NULL,
    response_type ENUM('text', 'template', 'redirect') DEFAULT 'text',
    conditions JSON NULL,
    is_active BOOLEAN DEFAULT true,
    usage_count INT DEFAULT 0,
    success_rate DECIMAL(5,2) DEFAULT 0,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    INDEX idx_category_id (category_id),
    INDEX idx_trigger_keyword (trigger_keyword),
    INDEX idx_is_active (is_active)
);

-- CRIAR: message_templates
CREATE TABLE message_templates (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    content TEXT NOT NULL,
    category VARCHAR(50) NULL,
    variables JSON NULL,
    is_global BOOLEAN DEFAULT false,
    created_by BIGINT UNSIGNED NOT NULL,
    usage_count INT DEFAULT 0,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_created_by (created_by),
    INDEX idx_category (category),
    INDEX idx_is_global (is_global)
);
```

### 7. 📊 Métricas e Auditoria

```sql
-- CRIAR: conversation_metrics
CREATE TABLE conversation_metrics (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    conversation_id BIGINT UNSIGNED NOT NULL,
    first_response_time INT NULL COMMENT 'Segundos',
    resolution_time INT NULL COMMENT 'Segundos',
    total_messages INT DEFAULT 0,
    agent_messages INT DEFAULT 0,
    customer_messages INT DEFAULT 0,
    transfers_count INT DEFAULT 0,
    satisfaction_score INT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE CASCADE,
    INDEX idx_conversation_id (conversation_id),
    INDEX idx_first_response_time (first_response_time),
    INDEX idx_resolution_time (resolution_time)
);

-- CRIAR: agent_metrics
CREATE TABLE agent_metrics (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    date DATE NOT NULL,
    active_time INT DEFAULT 0 COMMENT 'Segundos online',
    conversations_handled INT DEFAULT 0,
    avg_response_time INT NULL COMMENT 'Segundos',
    satisfaction_avg DECIMAL(3,2) NULL,
    transfers_received INT DEFAULT 0,
    transfers_made INT DEFAULT 0,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_date (user_id, date),
    INDEX idx_user_id (user_id),
    INDEX idx_date (date)
);

-- CRIAR: system_logs
CREATE TABLE system_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(50) NULL,
    entity_id BIGINT UNSIGNED NULL,
    old_values JSON NULL,
    new_values JSON NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_action (action),
    INDEX idx_entity (entity_type, entity_id),
    INDEX idx_created_at (created_at)
);
```

### 8. 📧 Sistema de Mala Direta (FUTURO)

```sql
-- CRIAR: broadcast_campaigns
CREATE TABLE broadcast_campaigns (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    message_template TEXT NOT NULL,
    target_criteria JSON NULL,
    scheduled_at TIMESTAMP NULL,
    status ENUM('draft', 'scheduled', 'running', 'completed', 'cancelled') DEFAULT 'draft',
    sent_count INT DEFAULT 0,
    delivered_count INT DEFAULT 0,
    read_count INT DEFAULT 0,
    created_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_created_by (created_by),
    INDEX idx_status (status),
    INDEX idx_scheduled_at (scheduled_at)
);

-- CRIAR: broadcast_recipients
CREATE TABLE broadcast_recipients (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    campaign_id BIGINT UNSIGNED NOT NULL,
    contact_id BIGINT UNSIGNED NOT NULL,
    status ENUM('pending', 'sent', 'delivered', 'read', 'failed') DEFAULT 'pending',
    sent_at TIMESTAMP NULL,
    delivered_at TIMESTAMP NULL,
    read_at TIMESTAMP NULL,
    error_message TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (campaign_id) REFERENCES broadcast_campaigns(id) ON DELETE CASCADE,
    FOREIGN KEY (contact_id) REFERENCES contacts(id) ON DELETE CASCADE,
    INDEX idx_campaign_id (campaign_id),
    INDEX idx_contact_id (contact_id),
    INDEX idx_status (status)
);
```

### 9. 📹 Sistema de Reuniões (FUTURO)

```sql
-- CRIAR: video_rooms
CREATE TABLE video_rooms (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    room_code VARCHAR(50) UNIQUE NOT NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    max_participants INT DEFAULT 10,
    is_persistent BOOLEAN DEFAULT false,
    password VARCHAR(255) NULL,
    settings JSON NULL,
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_created_by (created_by),
    INDEX idx_room_code (room_code),
    INDEX idx_is_active (is_active)
);

-- CRIAR: video_sessions
CREATE TABLE video_sessions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    room_id BIGINT UNSIGNED NOT NULL,
    conversation_id BIGINT UNSIGNED NULL,
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ended_at TIMESTAMP NULL,
    participants JSON NULL,
    recording_url VARCHAR(500) NULL,
    quality_rating INT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (room_id) REFERENCES video_rooms(id) ON DELETE CASCADE,
    FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE SET NULL,
    INDEX idx_room_id (room_id),
    INDEX idx_conversation_id (conversation_id),
    INDEX idx_started_at (started_at)
);

-- CRIAR: meeting_participants
CREATE TABLE meeting_participants (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    session_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NULL,
    contact_id BIGINT UNSIGNED NULL,
    role ENUM('host', 'participant', 'viewer') DEFAULT 'participant',
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    left_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (session_id) REFERENCES video_sessions(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (contact_id) REFERENCES contacts(id) ON DELETE SET NULL,
    INDEX idx_session_id (session_id),
    INDEX idx_user_id (user_id),
    INDEX idx_contact_id (contact_id)
);
```

---

## 🚨 MIGRATIONS PRIORITÁRIAS PARA MVP (30 DIAS)

### Ordem de Implementação Sugerida:

1. **CRITICAL** - roles, departments (permissões)
2. **CRITICAL** - categories, category_keywords (classificação)
3. **CRITICAL** - channels, channel_integrations (WhatsApp)
4. **CRITICAL** - conversations (separar de messages)
5. **CRITICAL** - bot_flows, auto_responses (bot básico)
6. **IMPORTANT** - message_templates, conversation_transfers
7. **IMPORTANT** - conversation_metrics, agent_metrics
8. **NICE TO HAVE** - contact_custom_fields, contact_notes
9. **FUTURE** - broadcast_campaigns, video_rooms

### Dados Iniciais Necessários:

```sql
-- Roles básicas
INSERT INTO roles (name, description, max_simultaneous_chats) VALUES
('admin', 'Administrador do sistema', 999),
('manager', 'Gerente de atendimento', 20),
('agent', 'Atendente', 5);

-- Categorias básicas
INSERT INTO categories (name, description, color, priority) VALUES
('Vendas', 'Consultas sobre vendas e orçamentos', '#28a745', 2),
('Suporte', 'Suporte técnico e problemas', '#dc3545', 1),
('Financeiro', 'Questões financeiras e pagamentos', '#ffc107', 2),
('Emergência', 'Atendimento de emergência 24/7', '#ff0000', 3),
('Geral', 'Consultas gerais', '#6c757d', 1);

-- Canal WhatsApp básico
INSERT INTO channels (name, type, configuration, is_active) VALUES
('WhatsApp Business', 'whatsapp', '{"phone": "554133808848", "api_version": "v17.0"}', true);

-- Bot flow de boas-vindas
INSERT INTO bot_flows (name, trigger_type, actions, is_active) VALUES
('Boas-vindas', 'welcome', '{"message": "Olá! Bem-vindo ao EngChat. Como posso ajudá-lo hoje?", "show_menu": true}', true);
```

---

## 📝 OBSERVAÇÕES IMPORTANTES

### Problemas Identificados nas Migrations Atuais:

1. **Migration vazia**: `2025_03_17_193418_add_whatsapp_fields_to_messages_table.php` está vazia
2. **Falta estrutura de conversas**: Messages deveria ter conversation_id
3. **Falta sistema de permissões**: Não há roles nem departments
4. **Falta categorização**: Sem categories para classificar atendimentos
5. **Falta canais**: Sem estrutura para WhatsApp/outros canais
6. **Falta bot**: Sem estrutura para automação básica

### Sugestões de Melhorias:

1. **Implementar todas as tabelas CRITICAL** para o MVP funcionar
2. **Corrigir migration vazia** do WhatsApp
3. **Adicionar índices** para performance
4. **Implementar foreign keys** para integridade
5. **Adicionar campos JSON** para flexibilidade
6. **Criar seeders** com dados iniciais

Este schema completo garante que o EngChat tenha toda a estrutura necessária para ser uma plataforma robusta de atendimento multicanal com chat em tempo real, bot básico e sistema de permissões adequado.
