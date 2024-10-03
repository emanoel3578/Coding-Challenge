<?php

namespace App\Http\Controllers;

use App\Dtos\StorePromptConversationDto;
use App\Exceptions\UserNotFoundException;
use App\Services\Interfaces\IStorePromptConversationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Response as InertiaResponse;
use Throwable;

class StorePromptController extends Controller
{
    public function __construct(
        private IStorePromptConversationService $storePromptConversationService
    ) {
    
    }

    public function store(Request $request): RedirectResponse|InertiaResponse
    {
        try {
            $user = Auth::user();
            $inputDto = new StorePromptConversationDto(
                $user->id
            );
            $this->storePromptConversationService->execute($inputDto);

            return to_route('prompts.list');
        } catch (UserNotFoundException $userNotFoundException) {
            return $this->renderInertiaError($userNotFoundException);
        } catch (Throwable $th) {
            return $this->renderInertiaError($th);
        }
    }
}
