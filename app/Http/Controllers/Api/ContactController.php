<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\DTOs\CreateContactDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateContactRequest;
use App\Http\Requests\UpdateContactRequest;
use App\Http\Resources\ContactResource;
use App\Services\ContactService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ContactController extends Controller
{
    public function __construct(
        private readonly ContactService $contactService
    ) {}

    /**
     * Lista todos os contatos com filtros avançados
     * Usa o BaseService para buscas complexas
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        // Se houver parâmetros de busca avançada, usar BaseService
        if ($request->has(['search', 'searchLike', 'relations'])) {
            $contacts = $this->contactService->searchContacts($request);
        } else {
            // Senão, usar método tradicional com filtros simples
            $contacts = $this->contactService->getAll(
                filters: $request->only(['name', 'phone', 'email', 'is_blocked']),
                perPage: $request->get('per_page', 15)
            );
        }

        return ContactResource::collection($contacts);
    }

    /**
     * Cria um novo contato
     */
    public function store(CreateContactRequest $request): JsonResponse
    {
        $dto = CreateContactDTO::fromRequest($request);
        $contact = $this->contactService->create($dto);

        return ContactResource::make($contact)
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Exibe um contato específico
     */
    public function show(int $id): JsonResponse
    {
        $contact = $this->contactService->findById($id);

        if (!$contact) {
            return response()->json(['message' => 'Contato não encontrado'], 404);
        }

        return ContactResource::make($contact)->response();
    }

    /**
     * Atualiza um contato
     */
    public function update(UpdateContactRequest $request, int $id): JsonResponse
    {
        $contact = $this->contactService->update($id, $request->validated());

        return ContactResource::make($contact)->response();
    }

    /**
     * Remove um contato
     */
    public function destroy(int $id): JsonResponse
    {
        $this->contactService->delete($id);

        return response()->json(['message' => 'Contato removido com sucesso']);
    }

    /**
     * Busca para autocomplete
     */
    public function autocomplete(Request $request): JsonResponse
    {
        $contacts = $this->contactService->autocomplete($request);

        return response()->json($contacts);
    }

    /**
     * Busca avançada de contatos
     */
    public function search(Request $request): AnonymousResourceCollection
    {
        $contacts = $this->contactService->advancedSearch($request);

        return ContactResource::collection($contacts);
    }

    /**
     * Bloqueia um contato
     */
    public function block(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        $contact = $this->contactService->block(
            contactId: $id,
            blockedBy: $request->user()->id,
            reason: $request->get('reason')
        );

        return ContactResource::make($contact)->response();
    }

    /**
     * Desbloqueia um contato
     */
    public function unblock(int $id): JsonResponse
    {
        $contact = $this->contactService->unblock($id);

        return ContactResource::make($contact)->response();
    }

    /**
     * Lista contatos bloqueados
     */
    public function blocked(): AnonymousResourceCollection
    {
        $contacts = $this->contactService->getBlocked();

        return ContactResource::collection($contacts);
    }

    /**
     * Estatísticas de contatos
     */
    public function statistics(): JsonResponse
    {
        $statistics = $this->contactService->getStatistics();

        return response()->json($statistics);
    }

    /**
     * Bloqueia contatos em lote
     */
    public function bulkBlock(Request $request): JsonResponse
    {
        $request->validate([
            'contact_ids' => 'required|array',
            'contact_ids.*' => 'exists:contacts,id',
            'reason' => 'required|string|max:500'
        ]);

        $affected = $this->contactService->bulkBlock(
            contactIds: $request->get('contact_ids'),
            blockedBy: $request->user()->id,
            reason: $request->get('reason')
        );

        $count = count($request->get('contact_ids'));
        $message = $affected
            ? "{$count} contatos bloqueados com sucesso"
            : "Erro ao bloquear contatos";

        return response()->json([
            'message' => $message,
            'success' => $affected
        ]);
    }

    /**
     * Desbloqueia contatos em lote
     */
    public function bulkUnblock(Request $request): JsonResponse
    {
        $request->validate([
            'contact_ids' => 'required|array',
            'contact_ids.*' => 'exists:contacts,id'
        ]);

        $affected = $this->contactService->bulkUnblock(
            $request->get('contact_ids')
        );

        $count = count($request->get('contact_ids'));
        $message = $affected
            ? "{$count} contatos desbloqueados com sucesso"
            : "Erro ao desbloquear contatos";

        return response()->json([
            'message' => $message,
            'success' => $affected
        ]);
    }

    /**
     * Busca contatos por telefone
     */
    public function findByPhone(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => 'required|string'
        ]);

        $contact = $this->contactService->findByPhone($request->get('phone'));

        if (!$contact) {
            return response()->json(['message' => 'Contato não encontrado'], 404);
        }

        return ContactResource::make($contact)->response();
    }

    /**
     * Busca contatos por email
     */
    public function findByEmail(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $contact = $this->contactService->findByEmail($request->get('email'));

        if (!$contact) {
            return response()->json(['message' => 'Contato não encontrado'], 404);
        }

        return ContactResource::make($contact)->response();
    }
}
