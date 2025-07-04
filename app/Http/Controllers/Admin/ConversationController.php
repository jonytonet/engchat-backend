<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\DTOs\CreateConversationDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateConversationRequest;
use App\Http\Requests\UpdateConversationRequest;
use App\Services\ConversationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ConversationController extends Controller
{
    public function __construct(
        private readonly ConversationService $conversationService
    ) {}

    /**
     * Lista todas as conversas (view administrativa)
     */
    public function index(Request $request): View
    {
        $conversations = $this->conversationService->getAll(
            filters: $request->only(['status', 'priority', 'channel_id', 'search']),
            perPage: $request->get('per_page', 25)
        );

        return view('admin.conversations.index', compact('conversations'));
    }

    /**
     * Exibe formulário para criar nova conversa
     */
    public function create(): View
    {
        return view('admin.conversations.create');
    }

    /**
     * Armazena uma nova conversa
     */
    public function store(CreateConversationRequest $request): RedirectResponse
    {
        $dto = CreateConversationDTO::fromRequest($request);
        $conversation = $this->conversationService->create($dto);

        return redirect()
            ->route('admin.conversations.show', $conversation->id)
            ->with('success', 'Conversa criada com sucesso!');
    }

    /**
     * Exibe uma conversa específica (view administrativa)
     */
    public function show(int $id): View
    {
        $conversation = $this->conversationService->findById($id);
        $messages = $this->conversationService->getMessages($id);

        return view('admin.conversations.show', compact('conversation', 'messages'));
    }

    /**
     * Exibe formulário para editar conversa
     */
    public function edit(int $id): View
    {
        $conversation = $this->conversationService->findById($id);

        return view('admin.conversations.edit', compact('conversation'));
    }

    /**
     * Atualiza uma conversa
     */
    public function update(UpdateConversationRequest $request, int $id): RedirectResponse
    {
        $conversation = $this->conversationService->update($id, $request->validated());

        return redirect()
            ->route('admin.conversations.show', $conversation->id)
            ->with('success', 'Conversa atualizada com sucesso!');
    }

    /**
     * Remove uma conversa (soft delete)
     */
    public function destroy(int $id): RedirectResponse
    {
        $this->conversationService->delete($id);

        return redirect()
            ->route('admin.conversations.index')
            ->with('success', 'Conversa removida com sucesso!');
    }

    /**
     * Dashboard com estatísticas
     */
    public function dashboard(): View
    {
        $statistics = $this->conversationService->getDashboardStatistics();

        return view('admin.conversations.dashboard', compact('statistics'));
    }

    /**
     * Atribui múltiplas conversas a um agente
     */
    public function bulkAssign(Request $request): RedirectResponse
    {
        $request->validate([
            'conversation_ids' => 'required|array',
            'conversation_ids.*' => 'exists:conversations,id',
            'agent_id' => 'required|exists:users,id'
        ]);

        $this->conversationService->bulkAssign(
            conversationIds: $request->get('conversation_ids'),
            agentId: $request->get('agent_id'),
            assignedBy: auth()->id()
        );

        return redirect()
            ->back()
            ->with('success', 'Conversas atribuídas com sucesso!');
    }

    /**
     * Fecha múltiplas conversas
     */
    public function bulkClose(Request $request): RedirectResponse
    {
        $request->validate([
            'conversation_ids' => 'required|array',
            'conversation_ids.*' => 'exists:conversations,id'
        ]);

        $this->conversationService->bulkClose(
            conversationIds: $request->get('conversation_ids'),
            closedBy: auth()->id()
        );

        return redirect()
            ->back()
            ->with('success', 'Conversas fechadas com sucesso!');
    }

    /**
     * Exporta conversas para CSV/Excel
     */
    public function export(Request $request): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $filters = $request->only(['status', 'priority', 'channel_id', 'date_from', 'date_to']);

        return $this->conversationService->exportConversations($filters);
    }
}
