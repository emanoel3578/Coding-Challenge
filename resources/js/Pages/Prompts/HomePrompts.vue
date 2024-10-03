<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import ConversationLabel from './ConversationLabel.vue';

defineProps({ cards: Object })

const truncateContent = (content) => {
    if (content.length > 100) {
        return content.substring(0, 100) + '...';
    }
    return content;
};
</script>

<template>
    <Head title="Prompts Home" />

    <AuthenticatedLayout>
      <template #header>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Prompts</h2>
      </template>

      <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 flex flex-col gap-4">
            <div class="mt-6 ml-auto">
                <Link
                    href="/prompts/conversation"
                    class="flex items-center gap-2 rounded-lg py-2 px-4 text-xs font-bold text-pink-500 bg-pink-100 hover:bg-pink-200 transition-colors"
                    method="get"
                    as="button"
                    type="button"
                >
                    Start new conversation
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="2"
                        stroke="currentColor"
                        class="h-4 w-4"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M17.25 8.25L21 12m0 0l-3.75 3.75M21 12H3"
                        />
                    </svg>
                </Link>
            </div>
            <div v-if="cards && cards.length > 0" class="grid grid-cols-3 gap-4">
                <div
                    v-for="card in cards"
                    :key="card.prompt_interaction_id"
                    class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6"
                >
                    <h5 class="text-xl font-semibold text-pink-500 mb-4">
                        Conversation #{{ card.prompt_interaction_id }}
                    </h5>

                    <div class="space-y-4">
                        <div
                            v-for="conversation in card.conversation"
                            :key="conversation.prompt_interaction_history_id"
                            class="p-4 border-b last:border-none"
                        >
                            <ConversationLabel :conversation="conversation" />
                            <span class="font-italic text-base">
                                <p>
                                    {{ truncateContent(conversation.content) }}
                                </p>
                            </span>
                        </div>
                    </div>

                    <div class="mt-6">
                        <Link
                            :href="`/prompts/conversation/${Number(card.prompt_interaction_id)}`"
                            class="flex items-center gap-2 rounded-lg py-2 px-4 text-xs font-bold text-pink-500 bg-pink-100 hover:bg-pink-200 transition-colors"
                            method="get"
                            as="button"
                            type="button"
                        >
                            Keep chatting
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="2"
                                stroke="currentColor"
                                class="h-4 w-4"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M17.25 8.25L21 12m0 0l-3.75 3.75M21 12H3"
                                />
                            </svg>
                        </Link>
                    </div>
                </div>
            </div>
            <div v-else class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <p class="text-base text-bold text-gray-950">
                    No conversation entries yet.
                </p>
            </div>
        </div>
      </div>
    </AuthenticatedLayout>
</template>
