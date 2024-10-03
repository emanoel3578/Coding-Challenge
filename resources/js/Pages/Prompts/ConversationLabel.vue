<script setup>
import { getConversationTypeLabel } from '../../helpers.js';
import { CONVERSATION_QUESTION, CONVERSATION_ANSWER, CONVERSATION_MODIFIER } from '../../constants.js';

const props = defineProps({
    conversation: {
        type: Object,
        required: true
    }
});

const getLabel = () => {
    if (getConversationTypeLabel(props.conversation) === CONVERSATION_QUESTION) {
        return 'Question:';
    } else if (getConversationTypeLabel(props.conversation) == CONVERSATION_ANSWER) {
        return 'Answer:';
    } else if (getConversationTypeLabel(props.conversation) == CONVERSATION_MODIFIER) {
        return 'Modifier:';
    }
    return 'Answer:';
};

const getClass = () => {
    if (getConversationTypeLabel(props.conversation) === CONVERSATION_QUESTION) {
        return 'text-base font-italic text-blue-600';
    } else if (getConversationTypeLabel(props.conversation) == CONVERSATION_ANSWER) {
        return 'font-italic text-base text-green-600';
    } else if (getConversationTypeLabel(props.conversation) == CONVERSATION_MODIFIER) {
        return 'font-italic text-base text-blue-600';
    }
    return 'font-italic text-base text-green-600';
}
</script>

<template>
    <span :class="getClass()">{{ getLabel() }}</span>
</template>
