<?php

namespace App\Http\Controllers;

use App\Dtos\ProcessPromptDto;
use App\Enums\PromptTypesEnum;
use App\Exceptions\ErrorSavingPromptException;
use App\Exceptions\UserNotFoundException;
use App\Factories\ProcessPromptServiceFactory;
use App\Http\Requests\ProcessPromptRequest;
use App\Services\Interfaces\IProcessPromptService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Response as InertiaResponse;
use Symfony\Component\HttpFoundation\Response;

class ProcessPromptController extends Controller
{
    private IProcessPromptService $processPromptService;

    public function __construct(ProcessPromptServiceFactory $serviceFactory)
    {
        $this->processPromptService = $serviceFactory->make();
    }

    public function process(ProcessPromptRequest $promptRequest): RedirectResponse|InertiaResponse
    {
        try {
            $user = Auth::user();

            $promptRequestValidatedData = $promptRequest->validated();

            $promptTextData = [
                ['text' => $promptRequestValidatedData['question_text'], 'type' => PromptTypesEnum::QUESTION->value],
                ['text' => $promptRequestValidatedData['modifier_text'] ?? null, 'type' => PromptTypesEnum::MODIFIER->value]
            ];

            $promptDto = new ProcessPromptDto(
                $user->id,
                $promptTextData,
                $promptRequestValidatedData['interactions_id'] ?? null,
            );

            $conversationMessagesOutput = $this->processPromptService->execute($promptDto);
            $interactionId = $conversationMessagesOutput->conversationMessages[0]['prompt_interaction_id'];

            return redirect()->route('prompts.show', ['id' => $interactionId]);
        } catch (UserNotFoundException $userNotFoundException) {
            return $this->renderInertiaError($userNotFoundException);
        } catch (ErrorSavingPromptException $errorSavingPromptException) {
            return $this->renderInertiaError($errorSavingPromptException);
        } catch (\Throwable $th) {
            return $this->renderInertiaError($th);
        }
    }
}
