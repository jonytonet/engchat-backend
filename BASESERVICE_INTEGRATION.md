# Integração BaseService e BaseRepository - EngChat

## Visão Geral

O projeto EngChat agora possui uma integração completa entre `BaseService` e `BaseRepository` que fornece funcionalidades avançadas de busca, filtragem e operações em lote, mantendo os princípios SOLID e DDD.

## Arquitetura

### BaseRepository
O `BaseRepository` fornece métodos avançados como:
- `advancedSearch()` - Busca avançada com filtros
- `searchLike()` - Busca com LIKE e operadores
- `findAllFieldsAnd()` - Busca em todos os campos
- `autocompleteSearch()` - Busca para autocomplete
- `updateBatch()` - Atualização em lote
- `findByIn()` - Busca por array de valores

### BaseService
O `BaseService` fornece uma camada de abstração que permite:
- `getDados()` - Método principal para busca com diferentes estratégias
- Delegação de métodos do repositório
- Funcionalidades de ordenação e paginação

### Integração nos Serviços

#### ConversationService
```php
class ConversationService
{
    // Funcionalidades específicas de negócio + acesso ao BaseRepository
    
    public function searchConversations(Request $request): mixed
    {
        $relations = ['contact', 'category', 'channel'];
        return $this->conversationRepository->advancedSearch($request, $relations);
    }
    
    public function autocompleteConversations(Request $request): mixed
    {
        $select = ['id', 'subject', 'status'];
        $conditions = ['subject'];
        return $this->conversationRepository->autocompleteSearch($request, $select, $conditions);
    }
    
    public function batchUpdate(array $ids, array $data): bool
    {
        return $this->conversationRepository->batchUpdate($ids, $data);
    }
}
```

#### ContactService
```php
class ContactService
{
    // Funcionalidades específicas de negócio + acesso ao BaseRepository
    
    public function searchContacts(Request $request): mixed
    {
        return $this->contactRepository->advancedSearch($request);
    }
    
    public function autocompleteContacts(Request $request): mixed
    {
        $select = ['id', 'name', 'email', 'phone'];
        $conditions = ['name', 'email'];
        return $this->contactRepository->autocompleteSearch($request, $select, $conditions);
    }
    
    public function bulkBlock(array $contactIds, int $blockedBy, string $reason): bool
    {
        return $this->contactRepository->batchUpdate($contactIds, [
            'is_blocked' => true,
            'blocked_by' => $blockedBy,
            'blocked_reason' => $reason,
            'blocked_at' => now()->toDateTimeString(),
        ]);
    }
}
```

## Implementação nos Repositórios

### Interfaces
As interfaces foram estendidas para incluir métodos avançados:

```php
interface ConversationRepositoryInterface
{
    // Métodos padrão...
    
    // Métodos avançados
    public function advancedSearch(Request $request, array $relations = []): mixed;
    public function searchWithFilters(array $filters, array $relations = []): mixed;
    public function autocompleteSearch(Request $request, array $select = [], array $conditions = []): mixed;
    public function batchUpdate(array $ids, array $data): bool;
    public function getStatsByDateRange(string $startDate, string $endDate): array;
}
```

### Implementações Eloquent
Os repositórios Eloquent usam composition com BaseRepository:

```php
class EloquentConversationRepository implements ConversationRepositoryInterface
{
    protected $baseRepository;

    public function __construct(Application $app)
    {
        $this->baseRepository = new class($app) extends BaseRepository {
            protected $fieldSearchable = [
                'status', 'priority', 'subject', 'contact_id', 'channel_id', 'category_id', 'assigned_to'
            ];
            
            public function getFieldsSearchable(): array
            {
                return $this->fieldSearchable;
            }
            
            public function model(): string
            {
                return Conversation::class;
            }
        };
    }
    
    // Métodos avançados que delegam para BaseRepository
    public function advancedSearch(HttpRequest $request, array $relations = []): mixed
    {
        return $this->baseRepository->advancedSearch($request, $relations);
    }
    
    // Método __call para delegar outros métodos
    public function __call($method, $arguments)
    {
        return $this->baseRepository->$method(...$arguments);
    }
}
```

## Uso nos Controllers

### Exemplos de uso nos controllers API:

```php
class ConversationController extends Controller
{
    public function search(Request $request)
    {
        $conversations = $this->conversationService->searchConversations($request);
        return ConversationResource::collection($conversations);
    }
    
    public function autocomplete(Request $request)
    {
        $conversations = $this->conversationService->autocompleteConversations($request);
        return response()->json($conversations);
    }
    
    public function batchUpdate(Request $request)
    {
        $ids = $request->input('ids');
        $data = $request->input('data');
        
        $result = $this->conversationService->batchUpdate($ids, $data);
        
        return response()->json(['success' => $result]);
    }
}
```

## Parâmetros de Busca Avançada

### BaseService getDados()
O método `getDados()` aceita diferentes tipos de busca baseado nos parâmetros:

```javascript
// Busca avançada
GET /api/conversations?search=status=open,priority=high&relations=contact,category

// Busca LIKE
GET /api/conversations?searchLike=subject=Bug&searchLikeType=OR&relations=contact

// Busca em todos os campos
GET /api/conversations?relations=contact&order=created_at&direction=DESC&limit=20

// Autocomplete
GET /api/conversations/autocomplete?term=urgent
```

### Parâmetros Suportados:
- `search` - Busca avançada com filtros específicos
- `searchLike` - Busca com LIKE e operadores
- `relations` - Relacionamentos a incluir
- `order` - Campo para ordenação
- `direction` - Direção da ordenação (ASC/DESC)
- `limit` - Limite de registros por página
- `fields` - Campos específicos a retornar

## Benefícios da Integração

1. **Reutilização de Código**: BaseRepository fornece funcionalidades avançadas para todos os repositórios
2. **Consistência**: Mesma interface para buscas avançadas em todos os serviços
3. **Flexibilidade**: Diferentes estratégias de busca baseadas em parâmetros
4. **Performance**: Métodos otimizados para operações em lote
5. **Manutenibilidade**: Separação clara entre lógica de negócio e acesso a dados
6. **SOLID**: Princípios mantidos com dependency injection e interfaces

## Próximos Passos

1. Implementar testes unitários para os novos métodos
2. Adicionar cache para consultas frequentes
3. Implementar logs para operações em lote
4. Criar middleware para validação de parâmetros de busca
5. Documentar APIs com Swagger/OpenAPI
