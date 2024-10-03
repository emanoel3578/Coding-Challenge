<?php

namespace App\Enums;

enum OpenAiInteractionsRoleEnum: string
{
    case USER = 'user';
    case ASSISTANT = 'assistant';
    case SYSTEM = 'system';
}
