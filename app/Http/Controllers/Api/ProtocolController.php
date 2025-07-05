<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ProtocolService;
use App\DTOs\CreateProtocolDTO;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

/**
 * Protocol Controller
 * 
 * API para gerenciar protocolos de atendimento.
 * Segue padrões SOLID: SRP, DIP, validação de entrada.
 */
class ProtocolController extends Controller
{
    public function __construct(
        private ProtocolService $protocolService
    ) {}

    /**
     * Lista todos os protocolos
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->validate([
                'status' => 'nullable|in:open,closed',
                'contact_id' => 'nullable|integer|exists:contacts,id',
                'protocol_number' => 'nullable|string',
                'per_page' => 'nullable|integer|min:1|max:100'
            ]);

            $protocols = $this->protocolService->listProtocols($filters);

            return response()->json([
                'success' => true,
                'data' => $protocols->items(),
                'pagination' => [
                    'current_page' => $protocols->currentPage(),
                    'last_page' => $protocols->lastPage(),
                    'per_page' => $protocols->perPage(),
                    'total' => $protocols->total(),
                ]
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Dados inválidos',
                'errors' => $e->errors()
            ], 422);
        }
    }

    /**
     * Cria um novo protocolo
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'contact_id' => 'required|integer|exists:contacts,id',
                'description' => 'nullable|string|max:1000'
            ]);

            $dto = CreateProtocolDTO::fromArray($validated);
            $protocol = $this->protocolService->createProtocol($dto);

            return response()->json([
                'success' => true,
                'data' => $protocol->toArray(),
                'message' => 'Protocolo criado com sucesso'
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Dados inválidos',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Exibe um protocolo específico
     */
    public function show(int $id): JsonResponse
    {
        try {
            $protocol = $this->protocolService->findProtocolById($id);

            if (!$protocol) {
                return response()->json([
                    'success' => false,
                    'error' => 'Protocolo não encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $protocol->toArray()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Atualiza um protocolo
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'status' => 'nullable|in:open,closed',
                'description' => 'nullable|string|max:1000',
                'notes' => 'nullable|string|max:2000'
            ]);

            $protocol = $this->protocolService->updateProtocol($id, $validated);

            if (!$protocol) {
                return response()->json([
                    'success' => false,
                    'error' => 'Protocolo não encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $protocol->toArray(),
                'message' => 'Protocolo atualizado com sucesso'
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Dados inválidos',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Fecha um protocolo
     */
    public function close(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'notes' => 'nullable|string|max:2000'
            ]);

            $protocol = $this->protocolService->closeProtocol($id, $validated['notes'] ?? null);

            if (!$protocol) {
                return response()->json([
                    'success' => false,
                    'error' => 'Protocolo não encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $protocol->toArray(),
                'message' => 'Protocolo fechado com sucesso'
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Dados inválidos',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Reabre um protocolo
     */
    public function reopen(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'notes' => 'nullable|string|max:2000'
            ]);

            $protocol = $this->protocolService->reopenProtocol($id, $validated['notes'] ?? null);

            if (!$protocol) {
                return response()->json([
                    'success' => false,
                    'error' => 'Protocolo não encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $protocol->toArray(),
                'message' => 'Protocolo reaberto com sucesso'
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Dados inválidos',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Busca protocolo por número
     */
    public function findByNumber(string $protocolNumber): JsonResponse
    {
        try {
            $protocol = $this->protocolService->findProtocolByNumber($protocolNumber);

            if (!$protocol) {
                return response()->json([
                    'success' => false,
                    'error' => 'Protocolo não encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $protocol->toArray()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Lista protocolos de um contato
     */
    public function byContact(int $contactId): JsonResponse
    {
        try {
            $protocols = $this->protocolService->getContactProtocols($contactId);

            return response()->json([
                'success' => true,
                'data' => $protocols
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Estatísticas de protocolos
     */
    public function statistics(): JsonResponse
    {
        try {
            $stats = $this->protocolService->getProtocolStatistics();

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Erro interno do servidor'
            ], 500);
        }
    }
}
