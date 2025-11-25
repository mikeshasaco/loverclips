<script setup>
import { onMounted, ref, useAttrs } from 'vue';

const model = defineModel({
    type: String,
    required: true,
});

const input = ref(null);
const attrs = useAttrs();

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <input
        class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:focus:border-indigo-600 dark:focus:ring-indigo-600 disabled:bg-gray-100 disabled:text-gray-500 disabled:cursor-not-allowed dark:disabled:bg-gray-800 dark:disabled:text-gray-400"
        v-model="model"
        ref="input"
        v-bind="attrs"
    />
</template>
