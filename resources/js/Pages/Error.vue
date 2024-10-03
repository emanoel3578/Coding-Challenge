<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({ status: Number, message: String, stack: Object });
const showStack = ref(false);

const toggleStack = () => {
    showStack.value = !showStack.value;
};
</script>

<template>
    <Head title="Something went wrong" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Ops! Something went wrong</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="flex flex-col justify-center items-center p-6 gap-2">
                        <div class="flex gap-2 justify-center">
                            <h1> {{ status }} </h1>
                            <h3> {{ message }} </h3>
                        </div>

                        <button @click="toggleStack" class="bg-blue-500 text-white px-3 py-1 rounded-md text-sm flex items-center gap-1">
                            <span>See stack</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div v-if="stack && showStack" class="mt-4 w-full">
                            <pre class="text-sm text-gray-600 bg-gray-100 p-4 rounded-md w-full">
                                {{ stack }}
                            </pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
