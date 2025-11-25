<template>
    <AuthenticatedLayout v-if="$page.props.auth?.user" :user="$page.props.auth.user">
        <Head title="Home" />
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Category Header (only show when category is selected) -->
                <div v-if="selectedCategory" class="mb-6">
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                        {{ selectedCategory }}
                    </h1>
                    <p class="text-gray-600 dark:text-gray-400 mt-2">
                        {{ posts.length }} {{ posts.length === 1 ? 'video' : 'videos' }}
                    </p>
                </div>
                
                <!-- Latest Posts Slider (only show on root URL, no category) -->
                <div v-if="!selectedCategory && latestPosts && latestPosts.length > 0" class="relative mb-8">
                    <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-6 relative">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Latest Posts</h2>
                        
                        <!-- Left Arrow -->
                        <button
                            @click="scrollLeft"
                            :disabled="!canScrollLeft"
                            class="absolute left-2 top-1/2 -translate-y-1/2 z-10 bg-white dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 rounded-full p-3 shadow-lg transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                            :class="{ 'opacity-50 cursor-not-allowed': !canScrollLeft }"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>
                        
                        <!-- Right Arrow -->
                        <button
                            @click="scrollRight"
                            :disabled="!canScrollRight"
                            class="absolute right-2 top-1/2 -translate-y-1/2 z-10 bg-white dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 rounded-full p-3 shadow-lg transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                            :class="{ 'opacity-50 cursor-not-allowed': !canScrollRight }"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                        
                        <!-- Scrollable Container -->
                        <div
                            ref="sliderContainer"
                            @scroll="updateScrollButtons"
                            class="flex gap-6 overflow-x-auto scrollbar-hide scroll-smooth pb-4"
                            style="scrollbar-width: none; -ms-overflow-style: none;"
                        >
                            <Link
                                v-for="post in latestPosts"
                                :key="post.id"
                                :href="route('posts.show', { username: post.user?.username || post.user?.id, slug: post.slug || post.id })"
                                class="relative rounded-lg overflow-hidden hover:shadow-xl transition-shadow cursor-pointer flex-shrink-0"
                                style="width: 300px; height: 450px;"
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
                                        <div class="flex flex-col gap-1">
                                            <span v-if="post.category" class="text-xs bg-blue-500/80 text-white px-2 py-1 rounded backdrop-blur-sm w-fit">
                                                {{ post.category }}
                                            </span>
                                            <Link
                                                @click.stop
                                                :href="route('profile.show', post.user?.username || post.user?.id)"
                                                class="text-xs text-white/80 hover:text-white hover:underline transition-colors w-fit"
                                            >
                                                By @{{ post.user?.username || post.user?.name }}
                                            </Link>
                                        </div>
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
                            </Link>
                        </div>
                    </div>
                </div>
                
                <!-- Posts Grid (for category view or when no latest posts) -->
                <div v-if="posts && posts.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <Link
                        v-for="post in posts"
                        :key="post.id"
                        :href="route('posts.show', { username: post.user?.username || post.user?.id, slug: post.slug || post.id })"
                        class="relative rounded-lg overflow-hidden hover:shadow-xl transition-shadow cursor-pointer"
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
                                <div class="flex flex-col gap-1">
                                    <span v-if="post.category" class="text-xs bg-blue-500/80 text-white px-2 py-1 rounded backdrop-blur-sm w-fit">
                                        {{ post.category }}
                                    </span>
                                    <Link
                                        @click.stop
                                        :href="route('profile.show', post.user?.username || post.user?.id)"
                                        class="text-xs text-white/80 hover:text-white hover:underline transition-colors w-fit"
                                    >
                                        By @{{ post.user?.username || post.user?.name }}
                                    </Link>
                                </div>
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
                    </Link>
                </div>
                
                <div v-if="(!posts || posts.length === 0) && (!latestPosts || latestPosts.length === 0)" class="text-center py-12">
                    <p class="text-gray-500 dark:text-gray-400">No posts available yet.</p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
    <GuestLayout v-else>
        <Head title="Home" />
        <div class="py-12 bg-gray-100 dark:bg-gray-900 min-h-screen">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Category Header (only show when category is selected) -->
                <div v-if="selectedCategory" class="mb-6">
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                        {{ selectedCategory }}
                    </h1>
                    <p class="text-gray-600 dark:text-gray-400 mt-2">
                        {{ posts.length }} {{ posts.length === 1 ? 'video' : 'videos' }}
                    </p>
                </div>
                
                <!-- Latest Posts Slider (only show on root URL, no category) -->
                <div v-if="!selectedCategory && latestPosts && latestPosts.length > 0" class="relative mb-8">
                    <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-6 relative">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Latest Posts</h2>
                        
                        <!-- Left Arrow -->
                        <button
                            @click="scrollLeft"
                            :disabled="!canScrollLeft"
                            class="absolute left-2 top-1/2 -translate-y-1/2 z-10 bg-white dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 rounded-full p-3 shadow-lg transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                            :class="{ 'opacity-50 cursor-not-allowed': !canScrollLeft }"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>
                        
                        <!-- Right Arrow -->
                        <button
                            @click="scrollRight"
                            :disabled="!canScrollRight"
                            class="absolute right-2 top-1/2 -translate-y-1/2 z-10 bg-white dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 rounded-full p-3 shadow-lg transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                            :class="{ 'opacity-50 cursor-not-allowed': !canScrollRight }"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                        
                        <!-- Scrollable Container -->
                        <div
                            ref="sliderContainer"
                            @scroll="updateScrollButtons"
                            class="flex gap-6 overflow-x-auto scrollbar-hide scroll-smooth pb-4"
                            style="scrollbar-width: none; -ms-overflow-style: none;"
                        >
                            <Link
                                v-for="post in latestPosts"
                                :key="post.id"
                                :href="route('posts.show', { username: post.user?.username || post.user?.id, slug: post.slug || post.id })"
                                class="relative rounded-lg overflow-hidden hover:shadow-xl transition-shadow cursor-pointer flex-shrink-0"
                                style="width: 300px; height: 450px;"
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
                                        <div class="flex flex-col gap-1">
                                            <span v-if="post.category" class="text-xs bg-blue-500/80 text-white px-2 py-1 rounded backdrop-blur-sm w-fit">
                                                {{ post.category }}
                                            </span>
                                            <Link
                                                @click.stop
                                                :href="route('profile.show', post.user?.username || post.user?.id)"
                                                class="text-xs text-white/80 hover:text-white hover:underline transition-colors w-fit"
                                            >
                                                By @{{ post.user?.username || post.user?.name }}
                                            </Link>
                                        </div>
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
                            </Link>
                        </div>
                    </div>
                </div>
                
                <!-- Posts Grid (for category view or when no latest posts) -->
                <div v-if="posts && posts.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <Link
                        v-for="post in posts"
                        :key="post.id"
                        :href="route('posts.show', { username: post.user?.username || post.user?.id, slug: post.slug || post.id })"
                        class="relative rounded-lg overflow-hidden hover:shadow-xl transition-shadow cursor-pointer"
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
                                <div class="flex flex-col gap-1">
                                    <span v-if="post.category" class="text-xs bg-blue-500/80 text-white px-2 py-1 rounded backdrop-blur-sm w-fit">
                                        {{ post.category }}
                                    </span>
                                    <Link
                                        @click.stop
                                        :href="route('profile.show', post.user?.username || post.user?.id)"
                                        class="text-xs text-white/80 hover:text-white hover:underline transition-colors w-fit"
                                    >
                                        By @{{ post.user?.username || post.user?.name }}
                                    </Link>
                                </div>
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
                    </Link>
                </div>
                
                <div v-if="(!posts || posts.length === 0) && (!latestPosts || latestPosts.length === 0)" class="text-center py-12">
                    <p class="text-gray-500 dark:text-gray-400">No posts available yet.</p>
                </div>
            </div>
        </div>
    </GuestLayout>
</template>

<script setup>
import { ref, onMounted, onUnmounted, nextTick } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';

const props = defineProps({
    latestPosts: {
        type: Array,
        default: () => [],
    },
    posts: {
        type: Array,
        default: () => [],
    },
    selectedCategory: {
        type: String,
        default: null,
    },
});

const sliderContainer = ref(null);
const canScrollLeft = ref(false);
const canScrollRight = ref(true);

const updateScrollButtons = () => {
    if (!sliderContainer.value) return;
    
    const { scrollLeft, scrollWidth, clientWidth } = sliderContainer.value;
    canScrollLeft.value = scrollLeft > 0;
    canScrollRight.value = scrollLeft < scrollWidth - clientWidth - 10;
};

const scrollLeft = () => {
    if (!sliderContainer.value) return;
    sliderContainer.value.scrollBy({
        left: -320,
        behavior: 'smooth'
    });
};

const scrollRight = () => {
    if (!sliderContainer.value) return;
    sliderContainer.value.scrollBy({
        left: 320,
        behavior: 'smooth'
    });
};

onMounted(() => {
    nextTick(() => {
        updateScrollButtons();
        window.addEventListener('resize', updateScrollButtons);
    });
});

onUnmounted(() => {
    window.removeEventListener('resize', updateScrollButtons);
});

const viewPost = (post) => {
    const username = post.user?.username || post.user?.id;
    const slug = post.slug || post.id;
    router.visit(route('posts.show', { username, slug }));
};
</script>

<style scoped>
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
</style>

