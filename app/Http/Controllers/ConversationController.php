<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\DTOs\CreateConversationDTO;
use App\Http\Requests\CreateConversationRequest;
use App\Http\Resources\ConversationResource;
use App\Services\ConversationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ConversationController extends Controller
{
    public function __construct(
        private ConversationService $conversationService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only([
            'status', 'priority', 'channel_id',
            'assigned_to', 'search'
        ]);

        // Para MVP, vamos usar uma implementação simples
        // Posteriormente será refatorado para usar o repository via service
        return response()->json([
            'message' => 'Endpoint em desenvolvimento'
        ], Response::HTTP_OK);
    }

    public function store(CreateConversationRequest $request): JsonResponse
    {
        $dto = CreateConversationDTO::fromRequest($request);
        $conversation = $this->conversationService->create($dto);

        return ConversationResource::make($conversation)
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(int $id): JsonResponse
    {
        $conversation = $this->conversationService->findById($id);

        if (!$conversation) {
            return response()->json([
                'message' => 'Conversa não encontrada.'
            ], Response::HTTP_NOT_FOUND);
        }

        return ConversationResource::make($conversation)
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function assign(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'agent_id' => 'required|integer|exists:users,id'
        ]);

        $conversation = $this->conversationService->assignToAgent(
            $id,
            $request->integer('agent_id')
        );

        return ConversationResource::make($conversation)
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function close(Request $request, int $id): JsonResponse
    {
        $closedBy = $request->user()->id;

        $conversation = $this->conversationService->close($id, $closedBy);

        return ConversationResource::make($conversation)
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function reopen(int $id): JsonResponse
    {
        $conversation = $this->conversationService->reopen($id);

        return ConversationResource::make($conversation)
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function statistics(): JsonResponse
    {
        $statistics = $this->conversationService->getStatistics();

        return response()->json([
            'data' => $statistics
        ], Response::HTTP_OK);
    }
}
