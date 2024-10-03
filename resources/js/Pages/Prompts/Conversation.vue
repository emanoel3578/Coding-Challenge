<script setup>
import { watch, ref, onMounted } from 'vue';
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, useForm } from "@inertiajs/vue3";
import ConversationLabel from './ConversationLabel.vue';

const props = defineProps({
    conversation: {
        type: Object,
        required: true
    },
    errors: {
        type: Object,
        required: false
    }
});

onMounted(() => {
    watch(() => props.conversation, (newValue) => {
        form.interactions_id = newValue[0]?.prompt_interaction_id;
    }, { immediate: true });
});

const form = useForm({
    question_text: null,
    modifier_text: null,
    interactions_id: null,
});

const submit = () => {
    form.clearErrors();

    if (!form.question_text) {
        form.setError('question_text', 'Question is required');
        return;
    }

    form.post('/prompts/process');
};
</script>

<template>

    <Head title="Conversation" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Conversation</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                    <div class="h-96 overflow-y-auto">
                        <div v-if="conversation.length > 0">
                            <div v-for="item in conversation" :key="item.prompt_interaction_history_id" class="mb-4">
                                <ConversationLabel :conversation="item" />
                                <span class="font-italic text-base">
                                    <p>
                                        {{ item.content }}
                                    </p>
                                </span>
                            </div>
                        </div>
                        <p v-else class="text-base text-bold text-gray-950">
                            No conversation entries yet.
                        </p>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm p-6">
                    <form @submit.prevent="submit">
                        <div class="mb-4">
                            <label for="question" class="block text-sm font-medium text-bold">Question *:</label>
                            <div v-if="form.errors.question_text" class="text-red-400">{{ form.errors.question_text }}</div>
                            <input v-model="form.question_text" id="question" type="text"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                        </div>

                        <div class="mb-4">
                            <label for="modifier" class="block text-sm font-medium text-gray-700">Modifier:</label>
                            <div v-if="form.errors.modifier_text" class="text-red-400">{{ form.errors.modifier_text }}</div>
                            <input v-model="form.modifier_text" id="modifier" type="text"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                        </div>

                        <input type="hidden" name="interactions_id" v-model="form.interactions_id" />

                        <button :disabled="form.processing" type="submit"
                            class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                            Send
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
