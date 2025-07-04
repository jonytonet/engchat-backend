<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\DTOs\CreateConversationDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateConversationRequest;
use App\Http\Requests\UpdateConversationRequest;
use App\Http\Resources\ConversationResource;
use App\Services\ConversationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ConversationController extends Controller
{
    public function __construct(
        private readonly ConversationService $conversationService
    ) {}

    /**
     * @OA\Get(
     *     path="/api/conversations",
     *     summary="List conversations",
     *     description="Get a list of conversations for the authenticated user",
     *     operationId="getConversations",
     *     tags={"Conversations"},
     *     security={{ "sanctum": {} }},
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by conversation status",
     *         required=false,
     *         @OA\Schema(type="string", enum={"open", "pending", "closed"})
     *     ),
     *     @OA\Parameter(
     *         name="priority",
     *         in="query",
     *         description="Filter by priority",
     *         required=false,
     *         @OA\Schema(type="string", enum={"low", "medium", "high", "urgent"})
     *     ),
     *     @OA\Parameter(
     *         name="channel_id",
     *         in="query",
     *         description="Filter by channel ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page",
     *         required=false,
     *         @OA\Schema(type="integer", default=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Conversation")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     *
     * Lista todas as conversas do usuário autenticado
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $conversations = $this->conversationService->getUserConversations(
            userId: $request->user()->id,
            filters: $request->only(['status', 'priority', 'channel_id']),
            perPage: $request->get('per_page', 15)
        );

        return ConversationResource::collection($conversations);
    }

    /**
     * @OA\Post(
     *     path="/api/conversations",
     *     summary="Create a new conversation",
     *     description="Creates a new conversation",
     *     operationId="createConversation",
     *     tags={"Conversations"},
     *     security={{ "sanctum": {} }},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"contact_id", "channel_id", "subject"},
     *             @OA\Property(property="contact_id", type="integer", description="Contact ID"),
     *             @OA\Property(property="channel_id", type="integer", description="Channel ID"),
     *             @OA\Property(property="subject", type="string", description="Conversation subject"),
     *             @OA\Property(property="priority", type="string", enum={"low", "medium", "high", "urgent"}, description="Priority level"),
     *             @OA\Property(property="category_id", type="integer", description="Category ID"),
     *             @OA\Property(property="description", type="string", description="Initial message/description")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Conversation created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Conversation")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     *
     * Cria uma nova conversa
     */
    public function store(CreateConversationRequest $request): JsonResponse
    {
        $dto = CreateConversationDTO::fromRequest($request);
        $conversation = $this->conversationService->create($dto);

        return ConversationResource::make($conversation)
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Exibe uma conversa específica
     */
    public function show(int $id): JsonResponse
    {
        $conversation = $this->conversationService->findById($id);

        return ConversationResource::make($conversation)->response();
    }

    /**
     * Atualiza uma conversa
     */
    public function update(UpdateConversationRequest $request, int $id): JsonResponse
    {
        $conversation = $this->conversationService->update($id, $request->validated());

        return ConversationResource::make($conversation)->response();
    }

    /**
     * Atribui uma conversa a um agente
     */
    public function assign(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'agent_id' => 'required|exists:users,id'
        ]);

        $conversation = $this->conversationService->assignToAgent(
            conversationId: $id,
            agentId: $request->get('agent_id'),
            assignedBy: $request->user()->id
        );

        return ConversationResource::make($conversation)->response();
    }

    /**
     * Fecha uma conversa
     */
    public function close(Request $request, int $id): JsonResponse
    {
        $conversation = $this->conversationService->close(
            conversationId: $id,
            closedBy: $request->user()->id
        );

        return ConversationResource::make($conversation)->response();
    }

    /**
     * Reabre uma conversa
     */
    public function reopen(Request $request, int $id): JsonResponse
    {
        $conversation = $this->conversationService->reopen(
            conversationId: $id,
            reopenedBy: $request->user()->id
        );

        return ConversationResource::make($conversation)->response();
    }

    /**
     * Busca conversas por contato
     */
    public function byContact(int $contactId): AnonymousResourceCollection
    {
        $conversations = $this->conversationService->findByContact($contactId);

        return ConversationResource::collection($conversations);
    }

    /**
     * Estatísticas das conversas do agente
     */
    public function statistics(Request $request): JsonResponse
    {
        $statistics = $this->conversationService->getAgentStatistics(
            $request->user()->id
        );

        return response()->json($statistics);
    }
}
