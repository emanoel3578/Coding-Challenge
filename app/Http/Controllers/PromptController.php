<?php

namespace App\Http\Controllers;

use App\Dtos\GetPromptConversationDto;
use App\Exceptions\UserNotFoundException;
use App\Http\Requests\ShowPromptRequest;
use App\Services\Interfaces\IGetAllPromptConversationsService;
use App\Services\Interfaces\IGetSinglePromptConversationService;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class PromptController extends Controller
{
    private const DEFAULT_LIMIT = 10;
    private const DEFAULT_ORDER_BY = 'desc';
    private const DEFAULT_LAST_RECENT_MESSAGES = 4;

    public function __construct(
        private IGetAllPromptConversationsService $getAllPromptConversationsService,
        private IGetSinglePromptConversationService $getSinglePromptConversationService
    ) {
    }

    public function list(ShowPromptRequest $request): Response
    {
        try {
            $user = Auth::user();

            $validated = $request->validated();

            $inputDto = new GetPromptConversationDto(
                $user->id,
                $validated['limit'] ?? self::DEFAULT_LIMIT,
                $validated['order_by'] ?? self::DEFAULT_ORDER_BY,
                $validated['last_recent_messages'] ?? self::DEFAULT_LAST_RECENT_MESSAGES
            );
            $promptsInteractionsFormatted = $this->getAllPromptConversationsService->execute($inputDto);

            return Inertia::render(
                'Prompts/HomePrompts', [
                'cards' => $promptsInteractionsFormatted->conversationMessages
                ]
            );
        } catch (UserNotFoundException $userNotFoundException) {
            return $this->renderInertiaError($userNotFoundException);
        } catch (Throwable $th) {
            return $this->renderInertiaError($th);
        }
    }

    public function show(?int $interactionsId = null): Response
    {
        try {
            $user = Auth::user();

            if ($interactionsId === null) {
                return Inertia::render('Prompts/Conversation', ['conversation' => []]);
            }

            $conversation = $this->getSinglePromptConversationService->execute($user->id, $interactionsId);
            return Inertia::render('Prompts/Conversation', ['conversation' => $conversation->retrieveConversationHistoryByFormat()]);
        } catch (UserNotFoundException $userNotFoundException) {
            return $this->renderInertiaError($userNotFoundException);
        } catch (Throwable $th) {
            return $this->renderInertiaError($th);
        }
    }
}
