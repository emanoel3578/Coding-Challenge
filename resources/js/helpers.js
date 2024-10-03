import { CONVERSATION_QUESTION, CONVERSATION_ANSWER, CONVERSATION_MODIFIER } from './constants';

export function getConversationTypeLabel(conversation) {
    switch (true) {
      case conversation.type === 'question' && conversation.role === 'user':
        return CONVERSATION_QUESTION;
      case conversation.type === 'answer' && conversation.role === 'assistant':
        return CONVERSATION_ANSWER;
      case conversation.type === 'modifier' && conversation.role === 'user':
        return CONVERSATION_MODIFIER;
      default:
        return '';
    }
}
