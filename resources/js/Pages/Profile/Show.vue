<template>
    <AuthenticatedLayout v-if="$page.props.auth?.user" :user="$page.props.auth.user">
        <Head :title="profileUser.username || profileUser.name" />
        <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
            <!-- Success Message Banner -->
            <Transition
                enter-active-class="transition ease-out duration-300"
                enter-from-class="opacity-0 transform -translate-y-2"
                enter-to-class="opacity-100 transform translate-y-0"
                leave-active-class="transition ease-in duration-200"
                leave-from-class="opacity-100 transform translate-y-0"
                leave-to-class="opacity-0 transform -translate-y-2"
            >
                <div
                    v-if="props.success"
                    class="bg-green-500 text-white px-4 py-3 shadow-lg"
                >
                    <div class="max-w-7xl mx-auto flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span class="font-medium">{{ props.success }}</span>
                        </div>
                    </div>
                </div>
            </Transition>
            
            <!-- Banner -->
            <div class="relative h-64 bg-gradient-to-r from-blue-500 to-purple-600">
                <img
                    v-if="profileUser.profile?.banner_url"
                    :src="profileUser.profile.banner_url"
                    alt="Banner"
                    class="w-full h-full object-cover"
                />
                <div class="absolute inset-0 bg-black bg-opacity-20"></div>
                
                <!-- Profile Avatar and Edit Button -->
                <div class="absolute bottom-0 left-8 transform translate-y-1/2 flex items-end gap-4">
                    <div class="relative">
                        <img
                            v-if="profileUser.profile?.avatar_url"
                            :src="profileUser.profile.avatar_url"
                            :alt="profileUser.name"
                            class="w-32 h-32 rounded-full border-4 border-white dark:border-gray-800 object-cover bg-white"
                        />
                        <div
                            v-else
                            class="w-32 h-32 rounded-full border-4 border-white dark:border-gray-800 bg-gray-300 dark:bg-gray-700 flex items-center justify-center text-4xl font-bold text-gray-600 dark:text-gray-400"
                        >
                            {{ profileUser.name.charAt(0).toUpperCase() }}
                        </div>
                        <Link
                            v-if="isOwnProfile"
                            :href="route('settings.edit', profileUser.username || profileUser.id)"
                            class="absolute bottom-0 right-0 bg-blue-500 hover:bg-blue-600 text-white rounded-full p-2 shadow-lg"
                            title="Edit Profile"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Profile Content -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-20 pb-12">
                <!-- User Info -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 mb-6">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-4 mb-4">
                                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                                    @{{ profileUser.username || profileUser.name }}
                                </h1>
                                <button
                                    v-if="!isOwnProfile && $page.props.auth?.user"
                                    @click="toggleFollow"
                                    :disabled="following"
                                    class="px-4 py-2 rounded-lg font-semibold transition-colors disabled:opacity-50"
                                    :class="isFollowing ? 'bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-gray-600' : 'bg-blue-500 text-white hover:bg-blue-600'"
                                >
                                    {{ following ? '...' : (isFollowing ? 'Following' : 'Follow') }}
                                </button>
                            </div>
                            <div class="flex items-center gap-6 mb-4">
                                <div class="flex items-center gap-2">
                                    <span class="font-semibold text-gray-900 dark:text-white">{{ followersCount || 0 }}</span>
                                    <span class="text-gray-600 dark:text-gray-400">followers</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="font-semibold text-gray-900 dark:text-white">{{ followingCount || 0 }}</span>
                                    <span class="text-gray-600 dark:text-gray-400">following</span>
                                </div>
                            </div>
                            <p v-if="profileUser.profile?.location" class="text-gray-600 dark:text-gray-400 mb-4">
                                📍 {{ profileUser.profile.location }}
                            </p>
                            <p v-if="profileUser.profile?.bio" class="text-gray-700 dark:text-gray-300">
                                {{ profileUser.profile.bio }}
                            </p>
                            <p v-else class="text-gray-500 dark:text-gray-400 italic">
                                No bio yet.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Posts Section -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                            Posts
                        </h2>
                        <span v-if="profileUser.posts && profileUser.posts.length > 0" class="text-sm text-gray-500 dark:text-gray-400">
                            {{ profileUser.posts.length }} post{{ profileUser.posts.length !== 1 ? 's' : '' }}
                        </span>
                    </div>
                    <div v-if="profileUser.posts && profileUser.posts.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <Link
                            v-for="post in profileUser.posts"
                            :key="post.id"
                            :href="route('posts.show', { username: profileUser.username || profileUser.id, slug: post.slug || post.id })"
                            class="relative rounded-lg overflow-hidden hover:shadow-xl transition-shadow"
                            style="width: 300px; height: 450px; margin: 0 auto;"
                        >
                            <img
                                v-if="post.thumbnail_url"
                                :src="post.thumbnail_url"
                                :alt="post.title"
                                class="absolute inset-0 w-full h-full object-cover"
                            />
                            <div v-else class="absolute inset-0 w-full h-full flex items-center justify-center text-gray-400 bg-gray-200 dark:bg-gray-600">
                                No Thumbnail
                            </div>
                            
                            <!-- Text Overlay -->
                            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent flex flex-col justify-end p-4">
                                <h3 class="text-lg font-bold text-white mb-2 line-clamp-2 drop-shadow-lg">
                                    {{ post.title }}
                                </h3>
                                <p v-if="post.description" class="text-sm text-white/90 mb-2 line-clamp-2 drop-shadow">
                                    {{ post.description }}
                                </p>
                                <div class="flex items-center justify-between mt-auto">
                                    <span v-if="post.category" class="text-xs bg-blue-500/80 text-white px-2 py-1 rounded backdrop-blur-sm">
                                        {{ post.category }}
                                    </span>
                                    <div class="flex gap-2">
                                        <div
                                            v-if="post.is_paid"
                                            class="bg-yellow-500 text-white px-2 py-1 rounded text-xs font-semibold"
                                        >
                                            ${{ post.price }}
                                        </div>
                                        <div
                                            v-else
                                            class="bg-green-500 text-white px-2 py-1 rounded text-xs font-semibold"
                                        >
                                            Free
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Edit Link for Owner -->
                            <Link
                                v-if="isOwnProfile"
                                @click.stop
                                :href="route('posts.edit', post.id)"
                                class="absolute top-2 right-2 bg-blue-500 hover:bg-blue-600 text-white px-2 py-1 rounded text-xs font-semibold z-10"
                            >
                                Edit
                            </Link>
                        </Link>
                    </div>
                    <div v-else class="text-center py-12 text-gray-500 dark:text-gray-400">
                        <p>No posts yet.</p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
    <GuestLayout v-else>
        <Head :title="profileUser.username || profileUser.name" />
        <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
            <!-- Success Message Banner -->
            <Transition
                enter-active-class="transition ease-out duration-300"
                enter-from-class="opacity-0 transform -translate-y-2"
                enter-to-class="opacity-100 transform translate-y-0"
                leave-active-class="transition ease-in duration-200"
                leave-from-class="opacity-100 transform translate-y-0"
                leave-to-class="opacity-0 transform -translate-y-2"
            >
                <div
                    v-if="props.success"
                    class="bg-green-500 text-white px-4 py-3 shadow-lg"
                >
                    <div class="max-w-7xl mx-auto flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span class="font-medium">{{ props.success }}</span>
                        </div>
                    </div>
                </div>
            </Transition>
            
            <!-- Banner -->
            <div class="relative h-64 bg-gradient-to-r from-blue-500 to-purple-600">
                <img
                    v-if="profileUser.profile?.banner_url"
                    :src="profileUser.profile.banner_url"
                    alt="Banner"
                    class="w-full h-full object-cover"
                />
                <div class="absolute inset-0 bg-black bg-opacity-20"></div>
                
                <!-- Profile Avatar -->
                <div class="absolute bottom-0 left-8 transform translate-y-1/2">
                    <img
                        v-if="profileUser.profile?.avatar_url"
                        :src="profileUser.profile.avatar_url"
                        :alt="profileUser.name"
                        class="w-32 h-32 rounded-full border-4 border-white dark:border-gray-800 object-cover bg-white"
                    />
                    <div
                        v-else
                        class="w-32 h-32 rounded-full border-4 border-white dark:border-gray-800 bg-gray-300 dark:bg-gray-700 flex items-center justify-center text-4xl font-bold text-gray-600 dark:text-gray-400"
                    >
                        {{ profileUser.name.charAt(0).toUpperCase() }}
                    </div>
                </div>
            </div>

            <!-- Profile Content -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-20 pb-12">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 mb-6">
                    <div class="flex items-center gap-4 mb-4">
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                            @{{ profileUser.username || profileUser.name }}
                        </h1>
                        <button
                            v-if="!isOwnProfile && $page.props.auth?.user"
                            @click="toggleFollow"
                            :disabled="following"
                            class="px-4 py-2 rounded-lg font-semibold transition-colors disabled:opacity-50"
                            :class="isFollowing ? 'bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-gray-600' : 'bg-blue-500 text-white hover:bg-blue-600'"
                        >
                            {{ following ? '...' : (isFollowing ? 'Following' : 'Follow') }}
                        </button>
                    </div>
                    <div class="flex items-center gap-6 mb-4">
                        <div class="flex items-center gap-2">
                            <span class="font-semibold text-gray-900 dark:text-white">{{ followersCount || 0 }}</span>
                            <span class="text-gray-600 dark:text-gray-400">followers</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="font-semibold text-gray-900 dark:text-white">{{ followingCount || 0 }}</span>
                            <span class="text-gray-600 dark:text-gray-400">following</span>
                        </div>
                    </div>
                    <p v-if="profileUser.profile?.location" class="text-gray-600 dark:text-gray-400 mb-4">
                        📍 {{ profileUser.profile.location }}
                    </p>
                    <p v-if="profileUser.profile?.bio" class="text-gray-700 dark:text-gray-300">
                        {{ profileUser.profile.bio }}
                    </p>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                            Posts
                        </h2>
                        <span v-if="profileUser.posts && profileUser.posts.length > 0" class="text-sm text-gray-500 dark:text-gray-400">
                            {{ profileUser.posts.length }} post{{ profileUser.posts.length !== 1 ? 's' : '' }}
                        </span>
                    </div>
                    <div v-if="profileUser.posts && profileUser.posts.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <Link
                            v-for="post in profileUser.posts"
                            :key="post.id"
                            :href="route('posts.show', { username: profileUser.username || profileUser.id, slug: post.slug || post.id })"
                            class="bg-gray-100 dark:bg-gray-700 rounded-lg overflow-hidden hover:shadow-xl transition-shadow"
                        >
                            <div class="relative" style="width: 300px; height: 450px; margin: 0 auto;">
                                <img
                                    v-if="post.thumbnail_url"
                                    :src="post.thumbnail_url"
                                    :alt="post.title"
                                    class="w-full h-full object-cover"
                                />
                                <div v-else class="w-full h-full flex items-center justify-center text-gray-400 bg-gray-200 dark:bg-gray-600">
                                    No Thumbnail
                                </div>
                                
                                <!-- Text Overlay -->
                                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent flex flex-col justify-end p-4">
                                    <h3 class="text-lg font-bold text-white mb-2 line-clamp-2 drop-shadow-lg">
                                        {{ post.title }}
                                    </h3>
                                    <p v-if="post.description" class="text-sm text-white/90 mb-2 line-clamp-2 drop-shadow">
                                        {{ post.description }}
                                    </p>
                                    <div class="flex items-center justify-between mt-auto">
                                        <span v-if="post.category" class="text-xs bg-blue-500/80 text-white px-2 py-1 rounded backdrop-blur-sm">
                                            {{ post.category }}
                                        </span>
                                        <div class="flex gap-2">
                                            <div
                                                v-if="post.is_paid"
                                                class="bg-yellow-500 text-white px-2 py-1 rounded text-xs font-semibold"
                                            >
                                                ${{ post.price }}
                                            </div>
                                            <div
                                                v-else
                                                class="bg-green-500 text-white px-2 py-1 rounded text-xs font-semibold"
                                            >
                                                Free
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </Link>
                    </div>
                    <div v-else class="text-center py-12 text-gray-500 dark:text-gray-400">
                        <p>No posts yet.</p>
                    </div>
                </div>
            </div>
        </div>
    </GuestLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import axios from 'axios';

const props = defineProps({
    profileUser: Object,
    isOwnProfile: Boolean,
    followersCount: {
        type: Number,
        default: 0,
    },
    followingCount: {
        type: Number,
        default: 0,
    },
    isFollowing: {
        type: Boolean,
        default: false,
    },
    success: {
        type: String,
        default: null,
    },
});

const page = usePage();
const isFollowing = ref(props.isFollowing);
const followersCount = ref(props.followersCount);
const following = ref(false);

const toggleFollow = async () => {
    if (following.value) return;
    
    following.value = true;
    try {
        const userId = props.profileUser.id;
        const csrfToken = document.head.querySelector('meta[name="csrf-token"]')?.content;
        
        if (isFollowing.value) {
            // Unfollow
            await axios.delete(`/api/users/${userId}/follow`, {
                headers: {
                    'X-CSRF-TOKEN': csrfToken || '',
                    'Accept': 'application/json',
                },
                withCredentials: true,
            });
            isFollowing.value = false;
        } else {
            // Follow
            await axios.post(`/api/users/${userId}/follow`, {}, {
                headers: {
                    'X-CSRF-TOKEN': csrfToken || '',
                    'Accept': 'application/json',
                },
                withCredentials: true,
            });
            isFollowing.value = true;
        }
        
        // Update followers count
        const checkResponse = await axios.get(`/api/users/${userId}/follow`, {
            headers: {
                'Accept': 'application/json',
            },
            withCredentials: true,
        });
        followersCount.value = checkResponse.data.followers_count || 0;
    } catch (error) {
        console.error('Error toggling follow:', error);
        alert('Failed to update follow status. Please try again.');
    } finally {
        following.value = false;
    }
};
</script>

