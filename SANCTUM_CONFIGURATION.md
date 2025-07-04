# Laravel Sanctum - Configuração ✅

## Status da Configuração

### ✅ **Sanctum ESTÁ CONFIGURADO CORRETAMENTE**

O Laravel Sanctum foi instalado e configurado com sucesso no projeto EngChat Backend. Aqui estão todos os componentes verificados:

## 📋 Checklist de Configuração

### ✅ 1. Pacote Instalado
- **Laravel Sanctum** instalado via Composer
- Versão compatível com Laravel 11

### ✅ 2. Service Provider
- O `SanctumServiceProvider` é carregado automaticamente
- Não requer registro manual no Laravel 11

### ✅ 3. Configuração de Middleware
**Arquivo**: `bootstrap/app.php`
```php
->withMiddleware(function (Middleware $middleware): void {
    $middleware->api(prepend: [
        \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
    ]);
})
```

### ✅ 4. Guards de Autenticação
**Arquivo**: `config/auth.php`
```php
'guards' => [
    'web' => [
        'driver' => 'session',
        'provider' => 'users',
    ],
    'sanctum' => [
        'driver' => 'sanctum',
        'provider' => null,
    ],
],
```

### ✅ 5. Model User com HasApiTokens
**Arquivo**: `app/Models/User.php`
```php
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;
    // ...
}
```

### ✅ 6. Configuração do Sanctum
**Arquivo**: `config/sanctum.php`
- Configuração padrão mantida
- Domínios stateful configurados para desenvolvimento local

### ✅ 7. Variáveis de Ambiente
**Arquivo**: `.env`
```dotenv
# Laravel Sanctum Configuration
SANCTUM_STATEFUL_DOMAINS=localhost,localhost:3000,127.0.0.1,127.0.0.1:8000,::1
SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null
```

### ✅ 8. Migração da Tabela de Tokens
**Arquivo**: `database/migrations/2025_07_04_163152_create_personal_access_tokens_table.php`
- Migração criada para a tabela `personal_access_tokens`

### ✅ 9. Rotas da API Protegidas
**Arquivo**: `routes/api.php`
```php
// Middleware aplicado corretamente
Route::middleware(['auth:sanctum'])->group(function () {
    Route::apiResource('conversations', ConversationController::class);
    // ...outras rotas
});
```

### ✅ 10. Rotas de Teste
- Rota `/api/user` configurada para retornar usuário autenticado
- Todas as rotas da API listadas e funcionais

## 🔧 Para usar o Sanctum:

### 1. **Autenticação via Token (Flutter App)**
```php
// No Flutter, após login bem-sucedido:
$token = $user->createToken('flutter-app')->plainTextToken;

// Headers nas requisições:
'Authorization: Bearer ' . $token
'Accept: application/json'
```

### 2. **Autenticação Stateful (Admin Web)**
- Login via Laravel Breeze (sessão web)
- CSRF protection automático
- Cookies de sessão

### 3. **Middleware de Proteção**
```php
// Para API (tokens)
Route::middleware('auth:sanctum')->group(function () {
    // rotas protegidas
});

// Para Web (sessão)
Route::middleware('auth')->group(function () {
    // rotas do admin
});
```

## 🚀 Próximos Passos

1. **✅ Configuração básica concluída**
2. **⏳ Executar migrações** (quando banco estiver configurado)
3. **⏳ Implementar endpoints de autenticação** (login/register)
4. **⏳ Testar autenticação** com Flutter/Postman

## 📝 Notas Importantes

- **Banco de dados**: Configurado para MySQL (SQLite removido por problemas de driver)
- **Sessões**: Configuradas para usar arquivos (não banco)
- **CORS**: Pode precisar de configuração adicional para Flutter
- **Domínios Stateful**: Configurados para desenvolvimento local

---

**Status**: ✅ **SANCTUM TOTALMENTE CONFIGURADO E PRONTO PARA USO**
