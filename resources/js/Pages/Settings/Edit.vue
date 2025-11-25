<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeleteUserForm from '@/Pages/Profile/Partials/DeleteUserForm.vue';
import UpdatePasswordForm from '@/Pages/Profile/Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from '@/Pages/Profile/Partials/UpdateProfileInformationForm.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
    profile: {
        type: Object,
        default: null,
    },
    posts: {
        type: Array,
        default: () => [],
    },
});

const activeTab = ref('profile');
</script>

<template>
    <Head title="Settings" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200"
            >
                Settings
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Tabs -->
                <div class="mb-6 border-b border-gray-200 dark:border-gray-700">
                    <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                        <button
                            @click="activeTab = 'profile'"
                            :class="[
                                activeTab === 'profile'
                                    ? 'border-blue-500 text-blue-600 dark:text-blue-400'
                                    : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300',
                                'whitespace-nowrap border-b-2 px-1 py-4 text-sm font-medium',
                            ]"
                        >
                            Profile
                        </button>
                        <button
                            @click="activeTab = 'settings'"
                            :class="[
                                activeTab === 'settings'
                                    ? 'border-blue-500 text-blue-600 dark:text-blue-400'
                                    : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300',
                                'whitespace-nowrap border-b-2 px-1 py-4 text-sm font-medium',
                            ]"
                        >
                            Settings
                        </button>
                    </nav>
                </div>

                <!-- Profile Tab Content -->
                <div v-show="activeTab === 'profile'" class="space-y-6">
                    <div
                        class="bg-white p-4 shadow sm:rounded-lg sm:p-8 dark:bg-gray-800"
                    >
                    <UpdateProfileInformationForm
                        :must-verify-email="mustVerifyEmail"
                        :status="status"
                        :profile="profile"
                        class="max-w-xl"
                    />
                    </div>
                    
                    <!-- User Posts -->
                    <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8 dark:bg-gray-800">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                            My Posts
                        </h3>
                        <div v-if="posts && posts.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <Link
                                v-for="postItem in posts"
                                :key="postItem.id"
                                :href="`/posts/${postItem.id}`"
                                class="bg-gray-100 dark:bg-gray-700 rounded-lg overflow-hidden hover:shadow-lg transition-shadow"
                            >
                                <div class="aspect-video bg-gray-200 dark:bg-gray-600 relative">
                                    <img
                                        v-if="postItem.thumbnail_url"
                                        :src="postItem.thumbnail_url"
                                        :alt="postItem.title"
                                        class="w-full h-full object-cover"
                                    />
                                    <div v-else class="w-full h-full flex items-center justify-center text-gray-400">
                                        No Thumbnail
                                    </div>
                                    <div
                                        v-if="postItem.is_paid"
                                        class="absolute top-2 right-2 bg-yellow-500 text-white px-2 py-1 rounded text-xs font-semibold"
                                    >
                                        ${{ postItem.price }}
                                    </div>
                                    <div
                                        v-else
                                        class="absolute top-2 right-2 bg-green-500 text-white px-2 py-1 rounded text-xs font-semibold"
                                    >
                                        Free
                                    </div>
                                    <div
                                        v-if="!postItem.is_published"
                                        class="absolute top-2 left-2 bg-gray-500 text-white px-2 py-1 rounded text-xs font-semibold"
                                    >
                                        Draft
                                    </div>
                                </div>
                                <div class="p-3">
                                    <h4 class="font-semibold text-gray-900 dark:text-white text-sm mb-1">
                                        {{ postItem.title }}
                                    </h4>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ postItem.category || 'No category' }}
                                    </p>
                                </div>
                            </Link>
                        </div>
                        <div v-else class="text-center py-8 text-gray-500 dark:text-gray-400">
                            <p>No posts yet.</p>
                            <Link
                                :href="route('posts.create')"
                                class="mt-4 inline-block bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
                            >
                                Create Your First Post
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Settings Tab Content -->
                <div v-show="activeTab === 'settings'" class="space-y-6">
                    <div
                        class="bg-white p-4 shadow sm:rounded-lg sm:p-8 dark:bg-gray-800"
                    >
                        <UpdatePasswordForm class="max-w-xl" />
                    </div>

                    <div
                        class="bg-white p-4 shadow sm:rounded-lg sm:p-8 dark:bg-gray-800"
                    >
                        <DeleteUserForm class="max-w-xl" />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

