<template>
    <AuthenticatedLayout :user="$page.props.auth?.user">
        <Head title="Drafts" />
        <div class="py-12 bg-gray-50 dark:bg-gray-900 min-h-screen">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="mb-6 flex justify-between items-center">
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">My Drafts</h1>
                    <Link
                        :href="route('posts.create')"
                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
                    >
                        Create New Post
                    </Link>
                </div>

                <div v-if="drafts && drafts.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div
                        v-for="draft in drafts"
                        :key="draft.id"
                        class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition-shadow relative group"
                    >
                        <Link :href="route('posts.edit', draft.id)" class="block">
                            <div class="aspect-video bg-gray-200 dark:bg-gray-700 relative">
                                <img
                                    v-if="draft.thumbnail_url"
                                    :src="draft.thumbnail_url"
                                    :alt="draft.title"
                                    class="w-full h-full object-cover"
                                />
                                <div v-else class="w-full h-full flex items-center justify-center text-gray-400">
                                    No Thumbnail
                                </div>
                                <div class="absolute top-2 left-2 bg-gray-500 text-white px-2 py-1 rounded text-xs font-semibold">
                                    Draft
                                </div>
                                <div
                                    v-if="draft.is_paid"
                                    class="absolute top-2 right-2 bg-yellow-500 text-white px-2 py-1 rounded text-sm font-semibold"
                                >
                                    ${{ draft.price }}
                                </div>
                                <div
                                    v-else
                                    class="absolute top-2 right-2 bg-green-500 text-white px-2 py-1 rounded text-sm font-semibold"
                                >
                                    Free
                                </div>
                                <button
                                    @click.stop.prevent="deleteDraft(draft.id, $event)"
                                    class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-red-500 hover:bg-red-600 text-white p-3 rounded-full shadow-lg transition-colors z-10 opacity-0 group-hover:opacity-100"
                                    title="Delete Draft"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                            <div class="p-4">
                                <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">
                                    {{ draft.title }}
                                </h3>
                                <p class="text-gray-600 dark:text-gray-300 text-sm mb-2 line-clamp-2">
                                    {{ draft.description || 'No description' }}
                                </p>
                                <div class="flex items-center justify-between">
                                    <span v-if="draft.category" class="text-xs bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 px-2 py-1 rounded">
                                        {{ draft.category }}
                                    </span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ formatDate(draft.updated_at) }}
                                    </span>
                                </div>
                            </div>
                        </Link>
                    </div>
                </div>

                <div v-else class="text-center py-12">
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No drafts</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Get started by creating a new post.
                        </p>
                        <div class="mt-6">
                            <Link
                                :href="route('posts.create')"
                                class="inline-flex items-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700"
                            >
                                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                Create Post
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import axios from 'axios';

const props = defineProps({
    drafts: {
        type: Array,
        default: () => [],
    },
});

const formatDate = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric' 
    });
};

const deleteDraft = async (draftId, event) => {
    // Prevent any navigation
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }
    
    if (!confirm('Are you sure you want to delete this draft? This action cannot be undone.')) {
        return;
    }

    try {
        await axios.delete(`/api/posts/${draftId}`);
        router.reload();
    } catch (error) {
        console.error('Error deleting draft:', error);
        alert('Failed to delete draft. Please try again.');
    }
};
</script>

