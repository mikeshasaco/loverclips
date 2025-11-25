<script setup>
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';

const props = defineProps({
    user: {
        type: Object,
        default: null,
    },
    categories: {
        type: Array,
        default: () => [],
    },
});

const showingNavigationDropdown = ref(false);

const logout = () => {
    router.post(route('logout'));
};
</script>

<template>
    <nav class="border-b border-gray-100 bg-white dark:border-gray-700 dark:bg-gray-800">
        <!-- Primary Navigation Menu -->
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 justify-between">
                <div class="flex">
                    <!-- Logo -->
                    <div class="flex shrink-0 items-center">
                        <Link :href="route('home')">
                            <ApplicationLogo
                                class="block h-9 w-auto fill-current text-gray-800 dark:text-gray-200"
                            />
                        </Link>
                    </div>

                    <!-- Navigation Links -->
                    <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex sm:items-center">
                        <NavLink
                            :href="route('home')"
                            :active="route().current('home')"
                        >
                            Home
                        </NavLink>
                        
                        <!-- Categories Dropdown -->
                        <div class="relative" v-if="categories && categories.length > 0">
                            <Dropdown align="left" width="48">
                                <template #trigger>
                                    <button
                                        type="button"
                                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition duration-150 ease-in-out"
                                    >
                                        Categories
                                        <svg
                                            class="ml-2 -mr-0.5 h-4 w-4"
                                            xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 20 20"
                                            fill="currentColor"
                                        >
                                            <path
                                                fill-rule="evenodd"
                                                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                clip-rule="evenodd"
                                            />
                                        </svg>
                                    </button>
                                </template>
                                <template #content>
                                    <DropdownLink
                                        :href="route('home')"
                                    >
                                        All Categories
                                    </DropdownLink>
                                    <div class="border-t border-gray-200 dark:border-gray-700"></div>
                                    <DropdownLink
                                        v-for="category in categories"
                                        :key="category"
                                        :href="`${route('home')}?category=${encodeURIComponent(category)}`"
                                    >
                                        {{ category }}
                                    </DropdownLink>
                                </template>
                            </Dropdown>
                        </div>
                    </div>
                </div>

                <!-- Right Side -->
                <div class="hidden sm:ms-6 sm:flex sm:items-center">
                    <!-- Create Post Button (only for authenticated users) -->
                    <Link
                        v-if="user"
                        :href="route('posts.create')"
                        class="inline-flex items-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-sm font-medium text-white transition duration-150 ease-in-out hover:bg-blue-700 focus:outline-none me-4"
                    >
                        <svg class="w-4 h-4 me-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Create Post
                    </Link>
                    
                    <!-- Authenticated User Menu -->
                    <div v-if="user" class="relative ms-3">
                        <Dropdown align="right" width="48">
                            <template #trigger>
                                <span class="inline-flex rounded-md">
                                    <button
                                        type="button"
                                        class="inline-flex items-center rounded-md border border-transparent bg-white px-3 py-2 text-sm font-medium leading-4 text-gray-500 transition duration-150 ease-in-out hover:text-gray-700 focus:outline-none dark:bg-gray-800 dark:text-gray-400 dark:hover:text-gray-300"
                                    >
                                        {{ user.username || user.name }}

                                        <svg
                                            class="-me-0.5 ms-2 h-4 w-4"
                                            xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 20 20"
                                            fill="currentColor"
                                        >
                                            <path
                                                fill-rule="evenodd"
                                                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                clip-rule="evenodd"
                                            />
                                        </svg>
                                    </button>
                                </span>
                            </template>

                            <template #content>
                                <DropdownLink
                                    :href="route('profile.show', user.username || user.id)"
                                >
                                    Profile
                                </DropdownLink>
                                <DropdownLink
                                    :href="route('posts.drafts', user.username || user.id)"
                                >
                                    Drafts
                                </DropdownLink>
                                <DropdownLink
                                    :href="route('settings.edit', user.username || user.id)"
                                >
                                    Settings
                                </DropdownLink>
                                <DropdownLink
                                    :href="route('logout')"
                                    method="post"
                                    as="button"
                                >
                                    Log Out
                                </DropdownLink>
                            </template>
                        </Dropdown>
                    </div>

                    <!-- Guest User Menu -->
                    <div v-else class="flex items-center space-x-4">
                        <Link
                            :href="route('login')"
                            class="text-sm font-medium text-gray-500 transition duration-150 ease-in-out hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300"
                        >
                            Sign In
                        </Link>
                        <Link
                            :href="route('register')"
                            class="inline-flex items-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-sm font-medium text-white transition duration-150 ease-in-out hover:bg-blue-700 focus:outline-none"
                        >
                            Sign Up
                        </Link>
                    </div>
                </div>

                <!-- Hamburger -->
                <div class="-me-2 flex items-center sm:hidden">
                    <button
                        @click="showingNavigationDropdown = !showingNavigationDropdown"
                        class="inline-flex items-center justify-center rounded-md p-2 text-gray-400 transition duration-150 ease-in-out hover:bg-gray-100 hover:text-gray-500 focus:bg-gray-100 focus:text-gray-500 focus:outline-none dark:text-gray-500 dark:hover:bg-gray-900 dark:hover:text-gray-400 dark:focus:bg-gray-900 dark:focus:text-gray-400"
                    >
                        <svg
                            class="h-6 w-6"
                            stroke="currentColor"
                            fill="none"
                            viewBox="0 0 24 24"
                        >
                            <path
                                :class="{
                                    hidden: showingNavigationDropdown,
                                    'inline-flex': !showingNavigationDropdown,
                                }"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16"
                            />
                            <path
                                :class="{
                                    hidden: !showingNavigationDropdown,
                                    'inline-flex': showingNavigationDropdown,
                                }"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"
                            />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Responsive Navigation Menu -->
        <div
            :class="{
                block: showingNavigationDropdown,
                hidden: !showingNavigationDropdown,
            }"
            class="sm:hidden"
        >
            <div class="space-y-1 pb-3 pt-2">
                <ResponsiveNavLink
                    :href="route('home')"
                    :active="route().current('home')"
                >
                    Home
                </ResponsiveNavLink>
                
                <!-- Categories in Mobile Menu -->
                <div v-if="categories && categories.length > 0" class="px-4 py-2">
                    <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">
                        Categories
                    </div>
                    <ResponsiveNavLink :href="route('home')" class="text-sm">
                        All Categories
                    </ResponsiveNavLink>
                    <ResponsiveNavLink
                        v-for="category in categories"
                        :key="category"
                        :href="`${route('home')}?category=${encodeURIComponent(category)}`"
                        class="text-sm"
                    >
                        {{ category }}
                    </ResponsiveNavLink>
                </div>
                
                <Link
                    v-if="user"
                    :href="route('posts.create')"
                    class="mx-4 mt-2 block rounded-md bg-blue-600 px-3 py-2 text-base font-medium text-white hover:bg-blue-700 text-center"
                >
                    <svg class="w-5 h-5 inline me-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Create Post
                </Link>
            </div>

            <!-- Responsive Settings Options (Authenticated) -->
            <div
                v-if="user"
                class="border-t border-gray-200 pb-1 pt-4 dark:border-gray-600"
            >
                <div class="px-4">
                    <div
                        class="text-base font-medium text-gray-800 dark:text-gray-200"
                    >
                        {{ user.username || user.name }}
                    </div>
                    <div class="text-sm font-medium text-gray-500">
                        {{ user.email }}
                    </div>
                </div>

                <div class="mt-3 space-y-1">
                    <ResponsiveNavLink :href="route('profile.show', user.username || user.id)">
                        Profile
                    </ResponsiveNavLink>
                    <ResponsiveNavLink :href="route('posts.drafts', user.username || user.id)">
                        Drafts
                    </ResponsiveNavLink>
                    <ResponsiveNavLink :href="route('settings.edit', user.username || user.id)">
                        Settings
                    </ResponsiveNavLink>
                    <ResponsiveNavLink
                        :href="route('logout')"
                        method="post"
                        as="button"
                    >
                        Log Out
                    </ResponsiveNavLink>
                </div>
            </div>

            <!-- Responsive Guest Options -->
            <div
                v-else
                class="border-t border-gray-200 pb-1 pt-4 dark:border-gray-600"
            >
                <div class="mt-3 space-y-1 px-4">
                    <ResponsiveNavLink :href="route('login')">
                        Sign In
                    </ResponsiveNavLink>
                    <ResponsiveNavLink :href="route('register')">
                        Sign Up
                    </ResponsiveNavLink>
                </div>
            </div>
        </div>
    </nav>
</template>

