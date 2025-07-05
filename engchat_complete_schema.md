# EngChat - Estrutura Completa para Chat com Filas e Bot

**Data:** 2025-07-05 18:00:00  
**Usuario:** jonytonet

---

## ✅ **IMPLEMENTADO RECENTEMENTE:**

### 7. 📋 **protocols** - Sistema de Protocolos (IMPLEMENTADO ✅)

```sql
-- IMPLEMENTADO: protocols (sistema de protocolos de atendimento)
CREATE TABLE protocols (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    protocol_number VARCHAR(20) NOT NULL UNIQUE COMMENT 'Número único do protocolo (formato: PROT-YYYYMMDD-XXXX)',
    
    -- Relacionamentos
    conversation_id BIGINT UNSIGNED NOT NULL,
    contact_id BIGINT UNSIGNED NOT NULL,
    assigned_agent_id BIGINT UNSIGNED NULL,
    category_id BIGINT UNSIGNED NULL,
    department_id BIGINT UNSIGNED NULL,
    
    -- Conteúdo do protocolo
    subject VARCHAR(255) NOT NULL COMMENT 'Assunto/resumo do protocolo',
    description TEXT NULL COMMENT 'Descrição detalhada',
    
    -- Status e prioridade
    status ENUM('open', 'in_progress', 'pending', 'resolved', 'closed', 'cancelled') DEFAULT 'open',
    priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
    
    -- Controle de tempo
    opened_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    closed_at TIMESTAMP NULL,
    due_date TIMESTAMP NULL COMMENT 'Data limite para resolução',
    
    -- Metadados
    tags JSON NULL COMMENT 'Tags do protocolo',
    metadata JSON NULL COMMENT 'Dados adicionais',
    internal_notes TEXT NULL COMMENT 'Notas internas do agente',
    
    -- Auditoria
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    
    FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE CASCADE,
    FOREIGN KEY (contact_id) REFERENCES contacts(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_agent_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL,
    
    INDEX idx_protocol_number (protocol_number),
    INDEX idx_conversation_id (conversation_id),
    INDEX idx_contact_id (contact_id),
    INDEX idx_assigned_agent_id (assigned_agent_id),
    INDEX idx_status (status),
    INDEX idx_priority (priority),
    INDEX idx_opened_at (opened_at),
    INDEX idx_due_date (due_date)
);
```

**Funcionalidades Implementadas:**
- ✅ **CRUD Completo** - Create, Read, Update, Delete
- ✅ **Geração Automática** - Número de protocolo único (PROT-20250705-0001)
- ✅ **Gestão de Status** - open, in_progress, pending, resolved, closed, cancelled
- ✅ **Sistema de Prioridades** - low, medium, high, urgent
- ✅ **Vinculação Completa** - Conversa, contato, agente, categoria, departamento
- ✅ **API RESTful** - Endpoints para todas as operações
- ✅ **Filtros e Busca** - Por status, prioridade, contato, agente, data
- ✅ **Estatísticas** - Contadores por status, tempo médio de resolução
- ✅ **Soft Deletes** - Exclusão lógica com timestamp
- ✅ **Arquitetura SOLID** - Repository + Service + DTOs + Controller

**DTOs Implementados:**
- ✅ `ProtocolDTO` - Transferência completa de dados
- ✅ `CreateProtocolDTO` - Criação de novos protocolos
- ✅ `UpdateProtocolDTO` - Atualização de protocolos existentes

**Endpoints API:**
- ✅ `GET /api/protocols` - Listagem com filtros e paginação
- ✅ `POST /api/protocols` - Criação de protocolo
- ✅ `GET /api/protocols/{id}` - Detalhes do protocolo
- ✅ `PUT /api/protocols/{id}` - Atualização completa
- ✅ `DELETE /api/protocols/{id}` - Exclusão (soft delete)
- ✅ `PATCH /api/protocols/{id}/close` - Fechamento
- ✅ `PATCH /api/protocols/{id}/reopen` - Reabertura
- ✅ `GET /api/protocols/contact/{contactId}` - Por contato
- ✅ `GET /api/protocols/statistics` - Estatísticas gerais
- ✅ `GET /api/protocols/number/{number}` - Busca por número

---

## 🔍 **ANÁLISE DO FLUXO DE ATENDIMENTO:**

### **Fluxo Atual Necessário:**

1. 🤖 **Bot faz pré-atendimento** → Coleta dados e classifica
2. 🎯 **Direcionamento automático** → Para agente correto por categoria/departamento
3. 📋 **Sistema de filas** → Se não há agente disponível
4. 🔄 **Transferências** → Entre departamentos se necessário
5. ⏱️ **Mensagens de espera** → "Você é o 4º na fila"
6. 📊 **Controle de disponibilidade** → Agentes online/offline/busy

---

## 🚨 **TABELAS FALTANDO (CRÍTICAS):**

### 1. 🤖 **bot_conversations** - Controle do Bot

```sql
-- CRIAR: bot_conversations (controle de conversas do bot)
CREATE TABLE bot_conversations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    conversation_id BIGINT UNSIGNED NOT NULL,
    contact_id BIGINT UNSIGNED NOT NULL,

    -- Estado do bot
    current_step VARCHAR(100) NOT NULL COMMENT 'welcome, collect_name, classify, etc',
    bot_flow_id BIGINT UNSIGNED NULL,

    -- Dados coletados pelo bot
    collected_data JSON NULL COMMENT 'Dados coletados durante o fluxo',
    classification_result JSON NULL COMMENT 'Resultado da classificação automática',
    confidence_score DECIMAL(5,2) NULL COMMENT 'Confiança da classificação',

    -- Status do handoff
    requires_human BOOLEAN DEFAULT false,
    handoff_reason VARCHAR(255) NULL,
    attempted_classifications JSON NULL COMMENT 'Tentativas de classificação',

    -- Controle de fluxo
    is_completed BOOLEAN DEFAULT false,
    completed_at TIMESTAMP NULL,
    escalated_at TIMESTAMP NULL,

    -- Metadados
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_interaction_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE CASCADE,
    FOREIGN KEY (contact_id) REFERENCES contacts(id) ON DELETE CASCADE,
    FOREIGN KEY (bot_flow_id) REFERENCES bot_flows(id) ON DELETE SET NULL,

    INDEX idx_conversation_id (conversation_id),
    INDEX idx_contact_id (contact_id),
    INDEX idx_current_step (current_step),
    INDEX idx_is_completed (is_completed),
    INDEX idx_requires_human (requires_human)
);
```

### 2. 📋 **conversation_queue** - Sistema de Filas

```sql
-- CRIAR: conversation_queue (fila de atendimento)
CREATE TABLE conversation_queue (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    conversation_id BIGINT UNSIGNED NOT NULL,

    -- Informações da fila
    department_id BIGINT UNSIGNED NOT NULL,
    category_id BIGINT UNSIGNED NULL,
    priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',

    -- Posição e timing
    queue_position INT NOT NULL,
    estimated_wait_time INT NULL COMMENT 'Tempo estimado em minutos',

    -- Tentativas de atribuição
    assignment_attempts INT DEFAULT 0,
    last_assignment_attempt TIMESTAMP NULL,

    -- Controle de notificações
    last_position_notification TIMESTAMP NULL,
    notification_count INT DEFAULT 0,

    -- Status
    status ENUM('waiting', 'assigned', 'expired', 'cancelled') DEFAULT 'waiting',
    assigned_to BIGINT UNSIGNED NULL,
    assigned_at TIMESTAMP NULL,

    -- Metadados
    entered_queue_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    removed_from_queue_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE CASCADE,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,

    UNIQUE KEY unique_conversation_queue (conversation_id),
    INDEX idx_department_id (department_id),
    INDEX idx_category_id (category_id),
    INDEX idx_priority (priority),
    INDEX idx_queue_position (queue_position),
    INDEX idx_status (status),
    INDEX idx_entered_queue_at (entered_queue_at)
);
```

### 3. 📊 **agent_availability** - Disponibilidade dos Agentes

```sql
-- CRIAR: agent_availability (controle de disponibilidade em tempo real)
CREATE TABLE agent_availability (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    agent_id BIGINT UNSIGNED NOT NULL,

    -- Status atual
    current_status ENUM('online', 'offline', 'busy', 'away', 'break') DEFAULT 'offline',
    previous_status ENUM('online', 'offline', 'busy', 'away', 'break') NULL,

    -- Capacidade atual
    max_conversations INT DEFAULT 5,
    current_conversations_count INT DEFAULT 0,
    available_slots INT GENERATED ALWAYS AS (max_conversations - current_conversations_count) STORED,

    -- Departamentos disponíveis
    available_departments JSON NULL COMMENT 'IDs dos departamentos que pode atender',
    preferred_categories JSON NULL COMMENT 'Categorias de preferência',

    -- Controle de pausas
    break_reason VARCHAR(255) NULL,
    break_start_time TIMESTAMP NULL,
    estimated_return_time TIMESTAMP NULL,

    -- Auto-assignment
    auto_accept_conversations BOOLEAN DEFAULT true,
    accept_transfers BOOLEAN DEFAULT true,

    -- Timestamps importantes
    last_status_change TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    shift_start_time TIMESTAMP NULL,
    shift_end_time TIMESTAMP NULL,

    -- Metadados
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    FOREIGN KEY (agent_id) REFERENCES agents(id) ON DELETE CASCADE,

    UNIQUE KEY unique_agent_availability (agent_id),
    INDEX idx_current_status (current_status),
    INDEX idx_available_slots (available_slots),
    INDEX idx_auto_accept (auto_accept_conversations),
    INDEX idx_last_activity (last_activity)
);
```

### 4. 📢 **queue_notifications** - Notificações da Fila

```sql
-- CRIAR: queue_notifications (mensagens automáticas da fila)
CREATE TABLE queue_notifications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    conversation_id BIGINT UNSIGNED NOT NULL,
    queue_id BIGINT UNSIGNED NOT NULL,

    -- Tipo de notificação
    notification_type ENUM('position_update', 'wait_time_update', 'agent_assigned', 'queue_timeout') NOT NULL,

    -- Conteúdo da notificação
    message_template_id BIGINT UNSIGNED NULL,
    custom_message TEXT NULL,
    variables JSON NULL COMMENT 'Variáveis para o template',

    -- Dados da fila no momento
    queue_position_at_time INT NOT NULL,
    estimated_wait_time INT NULL,
    total_queue_size INT NOT NULL,

    -- Status de envio
    status ENUM('pending', 'sent', 'failed') DEFAULT 'pending',
    sent_at TIMESTAMP NULL,
    error_message TEXT NULL,

    -- Controle
    scheduled_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE CASCADE,
    FOREIGN KEY (queue_id) REFERENCES conversation_queue(id) ON DELETE CASCADE,
    FOREIGN KEY (message_template_id) REFERENCES message_templates(id) ON DELETE SET NULL,

    INDEX idx_conversation_id (conversation_id),
    INDEX idx_queue_id (queue_id),
    INDEX idx_notification_type (notification_type),
    INDEX idx_status (status),
    INDEX idx_scheduled_at (scheduled_at)
);
```

### 5. 🔄 **conversation_assignments** - Histórico de Atribuições

```sql
-- CRIAR: conversation_assignments (histórico de atribuições)
CREATE TABLE conversation_assignments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    conversation_id BIGINT UNSIGNED NOT NULL,

    -- Atribuição
    assigned_to BIGINT UNSIGNED NOT NULL,
    assigned_by BIGINT UNSIGNED NULL COMMENT 'NULL se foi automático',
    assignment_type ENUM('automatic', 'manual', 'transfer', 'escalation') NOT NULL,

    -- Motivo e contexto
    assignment_reason VARCHAR(255) NULL,
    queue_position_before INT NULL,
    wait_time_before INT NULL COMMENT 'Tempo de espera antes da atribuição em minutos',

    -- Dados do agente na atribuição
    agent_status_at_assignment VARCHAR(50) NULL,
    agent_conversations_count INT NULL,
    agent_department_id BIGINT UNSIGNED NULL,

    -- Status da atribuição
    status ENUM('active', 'transferred', 'completed', 'abandoned') DEFAULT 'active',

    -- Timestamps
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    accepted_at TIMESTAMP NULL COMMENT 'Quando o agente aceitou',
    completed_at TIMESTAMP NULL,

    -- Resultado
    completion_reason VARCHAR(255) NULL,
    customer_satisfaction INT NULL COMMENT '1-5 rating',

    -- Metadatas
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (agent_department_id) REFERENCES departments(id) ON DELETE SET NULL,

    INDEX idx_conversation_id (conversation_id),
    INDEX idx_assigned_to (assigned_to),
    INDEX idx_assignment_type (assignment_type),
    INDEX idx_status (status),
    INDEX idx_assigned_at (assigned_at)
);
```

### 6. ⚙️ **queue_rules** - Regras de Fila por Departamento

```sql
-- CRIAR: queue_rules (regras de funcionamento das filas)
CREATE TABLE queue_rules (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    department_id BIGINT UNSIGNED NOT NULL,

    -- Configurações da fila
    max_queue_size INT DEFAULT 100,
    max_wait_time_minutes INT DEFAULT 60,

    -- Intervalos de notificação
    first_notification_after_minutes INT DEFAULT 2,
    notification_interval_minutes INT DEFAULT 5,
    max_notifications INT DEFAULT 10,

    -- Templates de mensagem
    welcome_template_id BIGINT UNSIGNED NULL,
    position_update_template_id BIGINT UNSIGNED NULL,
    timeout_template_id BIGINT UNSIGNED NULL,
    assigned_template_id BIGINT UNSIGNED NULL,

    -- Regras de priorização
    priority_rules JSON NULL COMMENT 'Regras para priorização automática',
    vip_priority_enabled BOOLEAN DEFAULT true,

    -- Horários de funcionamento
    working_hours JSON NULL,
    out_of_hours_template_id BIGINT UNSIGNED NULL,

    -- Auto-assignment
    auto_assignment_enabled BOOLEAN DEFAULT true,
    assignment_algorithm ENUM('round_robin', 'least_busy', 'skill_based') DEFAULT 'least_busy',

    -- Escalação
    escalation_enabled BOOLEAN DEFAULT true,
    escalation_time_minutes INT DEFAULT 15,
    escalation_department_id BIGINT UNSIGNED NULL,

    -- Metadados
    is_active BOOLEAN DEFAULT true,
    created_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE CASCADE,
    FOREIGN KEY (welcome_template_id) REFERENCES message_templates(id) ON DELETE SET NULL,
    FOREIGN KEY (position_update_template_id) REFERENCES message_templates(id) ON DELETE SET NULL,
    FOREIGN KEY (timeout_template_id) REFERENCES message_templates(id) ON DELETE SET NULL,
    FOREIGN KEY (assigned_template_id) REFERENCES message_templates(id) ON DELETE SET NULL,
    FOREIGN KEY (out_of_hours_template_id) REFERENCES message_templates(id) ON DELETE SET NULL,
    FOREIGN KEY (escalation_department_id) REFERENCES departments(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,

    UNIQUE KEY unique_department_rules (department_id),
    INDEX idx_department_id (department_id),
    INDEX idx_is_active (is_active)
);
```

---

## 🔄 **ATUALIZAÇÕES EM TABELAS EXISTENTES:**

### **Atualizar `conversations`:**

```sql
-- Adicionar campos de controle de fila
ALTER TABLE conversations ADD COLUMN queue_entry_time TIMESTAMP NULL AFTER tags;
ALTER TABLE conversations ADD COLUMN bot_handoff_time TIMESTAMP NULL AFTER queue_entry_time;
ALTER TABLE conversations ADD COLUMN first_human_response_time TIMESTAMP NULL AFTER bot_handoff_time;
ALTER TABLE conversations ADD COLUMN current_queue_position INT NULL AFTER first_human_response_time;

-- Índices
ALTER TABLE conversations ADD INDEX idx_queue_entry_time (queue_entry_time);
ALTER TABLE conversations ADD INDEX idx_current_queue_position (current_queue_position);
```

### **Atualizar `agents`:**

```sql
-- Adicionar campos de controle de capacidade
ALTER TABLE agents ADD COLUMN is_accepting_new_chats BOOLEAN DEFAULT true AFTER is_active;
ALTER TABLE agents ADD COLUMN preferred_departments JSON NULL AFTER is_accepting_new_chats;
ALTER TABLE agents ADD COLUMN skill_level ENUM('junior', 'pleno', 'senior', 'specialist') DEFAULT 'pleno' AFTER preferred_departments;

-- Índices
ALTER TABLE agents ADD INDEX idx_is_accepting_new_chats (is_accepting_new_chats);
ALTER TABLE agents ADD INDEX idx_skill_level (skill_level);
```

---

## 🎯 **DADOS INICIAIS ESSENCIAIS:**

### **Templates para Fila:**

```sql
INSERT INTO message_templates (
    name, display_name, template_code, language, category, approval_status,
    body_content, parameters, variables_count, is_global, created_by
) VALUES
-- Template de entrada na fila
('Entrada na Fila', 'Mensagem de Espera', 'queue_welcome', 'pt_BR', 'utility', 'approved',
'Olá {{1}}! 👋

Recebemos sua mensagem e em breve um de nossos atendentes estará com você.

📋 Você está na posição **{{2}}** da fila
⏱️ Tempo estimado de espera: **{{3}} minutos**

Aguarde um momento, não saia do chat! 😊',
'[
    {"position": 1, "name": "contact_name", "default_value": "{{contact.name}}", "required": true, "type": "text"},
    {"position": 2, "name": "queue_position", "required": true, "type": "number"},
    {"position": 3, "name": "estimated_wait", "required": true, "type": "number"}
]', 3, true, 1),

-- Template atualização de posição
('Atualização de Posição', 'Update da Fila', 'queue_position_update', 'pt_BR', 'utility', 'approved',
'📍 **Atualização da Fila**

Olá {{1}}!

Você agora está na posição **{{2}}** da fila.
⏱️ Tempo estimado: **{{3}} minutos**

Obrigado pela paciência! 🙏',
'[
    {"position": 1, "name": "contact_name", "default_value": "{{contact.name}}", "required": true, "type": "text"},
    {"position": 2, "name": "queue_position", "required": true, "type": "number"},
    {"position": 3, "name": "estimated_wait", "required": true, "type": "number"}
]', 3, true, 1),

-- Template agente conectado
('Agente Conectado', 'Conexão com Agente', 'agent_connected', 'pt_BR', 'utility', 'approved',
'✅ **Conectado com atendente!**

Olá {{1}}!

{{2}} da equipe {{3}} está agora disponível para atendê-lo.

Como posso ajudá-lo hoje? 😊',
'[
    {"position": 1, "name": "contact_name", "default_value": "{{contact.name}}", "required": true, "type": "text"},
    {"position": 2, "name": "agent_name", "default_value": "{{agent.name}}", "required": true, "type": "text"},
    {"position": 3, "name": "department_name", "default_value": "{{department.name}}", "required": true, "type": "text"}
]', 3, true, 1);
```

### **Regras de Fila Padrão:**

```sql
-- Assumindo department_id = 1 (Atendimento)
INSERT INTO queue_rules (
    department_id, max_queue_size, max_wait_time_minutes,
    first_notification_after_minutes, notification_interval_minutes, max_notifications,
    welcome_template_id, position_update_template_id, assigned_template_id,
    auto_assignment_enabled, assignment_algorithm, created_by
) VALUES (
    1, 50, 30,
    2, 5, 6,
    1, 2, 3,
    true, 'least_busy', 1
);
```

---

## 🔄 **FLUXO COMPLETO IMPLEMENTADO:**

### **1. Bot Pré-Atendimento:**

```
Mensagem → Bot Flow → Coleta Dados → Classifica → Direcionamento
```

### **2. Sistema de Filas:**

```
Sem Agente → Entra Fila → Notificação Posição → Agente Disponível → Atribuição
```

### **3. Notificações Automáticas:**

```
"Você é o 4º na fila" → "Você é o 2º na fila" → "Agente conectado!"
```

### **4. Transferências:**

```
Agente A → Identifica Necessidade → Transfere para Dept B → Nova Fila ou Direto
```

---

## 🚀 **PRIORIDADE DE IMPLEMENTAÇÃO:**

### **🔴 CRÍTICAS (HOJE):**

1. ✅ `bot_conversations` - Controle do bot
2. ✅ `conversation_queue` - Sistema de filas
3. ✅ `agent_availability` - Disponibilidade real-time

### **🟡 IMPORTANTES (ESTA SEMANA):**

4. ✅ `queue_notifications` - Mensagens automáticas
5. ✅ `queue_rules` - Regras por departamento
6. ✅ Templates de fila

### **🟢 ANALYTICS (SEMANA 2):**

7. ✅ `conversation_assignments` - Histórico completo

---

## ✅ **RESPOSTA FINAL:**

**Agora SIM, temos a estrutura 100% completa para criar o chat!** 🎉

Com essas tabelas adicionais, conseguimos implementar:

-   ✅ Bot inteligente com fluxos
-   ✅ Sistema de filas automático
-   ✅ Controle de disponibilidade dos agentes
-   ✅ Notificações "Você é o 4º na fila"
-   ✅ Transferências entre departamentos
-   ✅ Escalação automática
-   ✅ Métricas completas

**Migrations necessárias:** 6 novas + 2 atualizações = **8 migrations**

Agora podemos partir para o desenvolvimento! 🚀
