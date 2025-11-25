<script setup>
import Navbar from '@/Components/Navbar.vue';
</script>

<template>
    <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
        <Navbar :user="$page.props.auth?.user" :categories="$page.props.categories || []" />
        
        <!-- For auth pages (login, register) -->
        <div
            v-if="$slots.auth"
            class="flex min-h-screen flex-col items-center pt-6 sm:justify-center sm:pt-0"
        >
            <div
                class="mt-6 w-full overflow-hidden bg-white px-6 py-4 shadow-md sm:max-w-md sm:rounded-lg dark:bg-gray-800"
            >
                <slot name="auth" />
            </div>
        </div>
        
        <!-- For regular pages -->
        <div v-else>
            <slot />
        </div>
    </div>
</template>
