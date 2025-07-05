# 🚀 Inicialização do Sistema EngChat com Bot e Filas

## 📋 Pré-requisitos

1. **Docker Desktop** instalado e rodando
2. **Git** para clone do repositório
3. **WSL2** habilitado (Windows)

## 🐳 Passo a Passo com Docker

### 1. Iniciar Docker Desktop

```bash
# Verificar se Docker está rodando
docker --version
docker ps
```

### 2. Subir os containers do EngChat

```bash
# Navegar para o diretório do projeto
cd c:\projects\EngeChat\engchat-backend

# Subir todos os serviços (MariaDB, Redis, RabbitMQ, etc.)
.\sail.bat up

# Aguardar todos os containers estarem prontos
.\sail.bat ps
```

### 3. Configurar ambiente e banco de dados

```bash
# Gerar chave da aplicação
.\sail.bat artisan key:generate

# Executar migrations (criar todas as tabelas)
.\sail.bat artisan migrate

# Popular dados iniciais (roles, departamentos, templates de fila)
.\sail.bat artisan db:seed --class=QueueSystemSeeder

# Executar todos os seeders
.\sail.bat artisan db:seed
```

### 4. Verificar instalação

```bash
# Listar tabelas criadas
.\sail.bat artisan tinker
# DB::connection()->getSchemaBuilder()->getTableListing()

# Verificar se dados foram inseridos
# User::count()
# Department::count()
# MessageTemplate::count()
```

## 🔧 Serviços Disponíveis

Após os containers estarem rodando:

-   🌍 **EngChat App**: http://localhost:8000
-   📚 **Swagger API**: http://localhost:8000/api/documentation
-   🗄️ **MariaDB**: localhost:3306
-   🔴 **Redis**: localhost:6379
-   🐰 **RabbitMQ**: http://localhost:15672 (engchat/secret)
-   📧 **Mailpit**: http://localhost:8025
-   🔗 **WebSocket**: ws://localhost:8080

## 🔄 Estrutura de Filas Implementada

### **Tabelas Criadas:**

-   ✅ `bot_conversations` - Controle do bot
-   ✅ `conversation_queue` - Sistema de filas
-   ✅ `agent_availability` - Disponibilidade dos agentes
-   ✅ `queue_notifications` - Notificações automáticas
-   ✅ `message_templates` - Templates de mensagens
-   ✅ `queue_rules` - Regras por departamento
-   ✅ `conversation_assignments` - Histórico de atribuições

### **Dados Iniciais:**

-   👥 **Roles**: admin, manager, agent, specialist
-   🏢 **Departamentos**: Atendimento Geral, Suporte Técnico, Vendas
-   📝 **Templates**: Entrada na fila, Posição atualizada, Agente conectado
-   ⚙️ **Regras**: Configurações específicas por departamento

## 🤖 Fluxo do Bot e Filas

### **1. Pré-atendimento do Bot:**

```
Mensagem → Bot coleta dados → Classifica → Direciona para departamento
```

### **2. Sistema de Filas:**

```
Sem agente disponível → Entra na fila → Notificações de posição → Agente atribuído
```

### **3. Notificações Automáticas:**

```
"Você é o 4º na fila" → "Você é o 2º na fila" → "Agente conectado!"
```

## 🧪 Testando o Sistema

### **Verificar Modelos:**

```bash
.\sail.bat artisan tinker

# Testar modelos
$bot = new App\Models\BotConversation();
$queue = new App\Models\ConversationQueue();
$agent = new App\Models\AgentAvailability();

# Verificar relacionamentos
$conversation = App\Models\Conversation::first();
$queue = $conversation->queue;
$bot = $conversation->botConversation;
```

### **Testar Templates:**

```bash
# Verificar templates da fila
$template = App\Models\MessageTemplate::where('template_code', 'queue_welcome')->first();
echo $template->processTemplate(['João', '3', '5']);
```

### **Simular Fluxo de Fila:**

```bash
# Criar entrada na fila
$queueEntry = App\Models\ConversationQueue::create([
    'conversation_id' => 1,
    'department_id' => 1,
    'priority' => 'medium',
    'queue_position' => 1,
    'estimated_wait_time' => 5
]);

# Verificar posição
echo $queueEntry->queue_position;
```

## 🛠️ Comandos Úteis

### **Gerenciamento:**

```bash
# Parar containers
.\sail.bat down

# Rebuild se necessário
.\sail.bat build --no-cache

# Ver logs
.\sail.bat logs

# Acessar container
.\sail.bat shell
```

### **Banco de Dados:**

```bash
# Reset completo
.\sail.bat artisan migrate:fresh --seed

# Executar migration específica
.\sail.bat artisan migrate --path=/database/migrations/2025_07_05_120000_create_bot_conversations_table.php

# Reverter última migration
.\sail.bat artisan migrate:rollback
```

### **Queues e Jobs:**

```bash
# Iniciar workers para filas
.\sail.bat artisan queue:work

# WebSocket para notificações em tempo real
.\sail.bat artisan reverb:start
```

## 🚨 Troubleshooting

### **Container não sobe:**

```bash
# Verificar se todas as portas estão livres
netstat -an | findstr ":3306"
netstat -an | findstr ":8000"

# Forçar rebuild
.\sail.bat down
docker system prune -f
.\sail.bat up --build
```

### **Migration falha:**

```bash
# Verificar conexão com banco
.\sail.bat artisan tinker
# DB::connection()->getPdo()

# Verificar se MariaDB está rodando
.\sail.bat logs mariadb
```

### **Templates não carregam:**

```bash
# Executar seeder específico
.\sail.bat artisan db:seed --class=QueueSystemSeeder

# Verificar se foram criados
.\sail.bat artisan tinker
# App\Models\MessageTemplate::count()
```

---

## ✅ Sistema Pronto!

Após seguir esses passos, você terá:

-   ✅ Sistema de chat com bot funcional
-   ✅ Filas de atendimento automáticas
-   ✅ Notificações "Você é o Xº na fila"
-   ✅ Disponibilidade de agentes em tempo real
-   ✅ Templates de mensagens configurados
-   ✅ Regras de departamento ativas

**🔗 Próximos passos:** Desenvolver controllers, services e a interface web!
