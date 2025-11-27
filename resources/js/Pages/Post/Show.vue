<template>
    <AuthenticatedLayout v-if="$page.props.auth?.user" :user="$page.props.auth.user">
        <Head :title="post.title" />
        <div class="flex flex-col h-screen bg-gray-50 dark:bg-gray-900">
            <!-- Header -->
            <div class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-4 py-3 shadow-sm">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <Link :href="route('home')" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </Link>
                        <div>
                            <h1 class="text-lg font-bold text-gray-900 dark:text-white">{{ post.title }}</h1>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ post.category || 'Uncategorized' }} • By {{ post.user?.name }}
                            </p>
                        </div>
                    </div>
                    <div v-if="post.is_paid && !hasAccess" class="text-right">
                        <p class="text-sm text-gray-600 dark:text-gray-300">${{ post.price }}</p>
                        <button
                            @click="purchasePost"
                            :disabled="purchasing"
                            class="mt-1 bg-blue-500 hover:bg-blue-600 text-white text-sm font-semibold py-1.5 px-4 rounded-lg disabled:opacity-50 transition-colors"
                        >
                            {{ purchasing ? 'Processing...' : 'Purchase' }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Chat Interface -->
            <div v-if="hasAccess" class="flex-1 flex overflow-hidden">
                <!-- Sidebar with followed users' videos -->
                <div 
                    v-show="sidebarOpen"
                    class="w-80 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 overflow-y-auto transition-all duration-300 relative"
                >
                    <!-- Collapse Arrow Button -->
                    <button
                        @click="sidebarOpen = false"
                        class="absolute top-1/2 -right-4 transform -translate-y-1/2 z-20 bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 rounded-r-lg p-2 shadow-lg hover:shadow-xl transition-all text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 hover:border-blue-500"
                        title="Collapse sidebar"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                    <div class="p-4">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Following</h2>
                        <div v-if="followedPosts && followedPosts.length > 0" class="space-y-3">
                            <div
                                v-for="followedPost in followedPosts"
                                :key="followedPost.id"
                                @click="switchToPost(followedPost)"
                                :class="[
                                    'cursor-pointer rounded-lg overflow-hidden border-2 transition-all hover:shadow-lg',
                                    followedPost.id === post.id 
                                        ? 'border-blue-500 shadow-md' 
                                        : 'border-gray-200 dark:border-gray-700 hover:border-blue-300'
                                ]"
                            >
                                <div class="relative aspect-video bg-black">
                                    <img
                                        v-if="followedPost.thumbnail_url"
                                        :src="followedPost.thumbnail_url"
                                        :alt="followedPost.title"
                                        class="w-full h-full object-cover"
                                    />
                                    <div v-else class="w-full h-full flex items-center justify-center text-gray-400">
                                        No Thumbnail
                                    </div>
                                    <div
                                        v-if="followedPost.is_paid"
                                        class="absolute top-2 right-2 bg-yellow-500 text-white px-2 py-1 rounded text-xs font-semibold"
                                    >
                                        ${{ followedPost.price }}
                                    </div>
                                </div>
                                <div class="p-3">
                                    <h3 class="font-semibold text-sm text-gray-900 dark:text-white line-clamp-2">
                                        {{ followedPost.title }}
                                    </h3>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        {{ followedPost.user?.name }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center py-8 text-gray-500 dark:text-gray-400">
                            <p class="text-sm">No posts from users you follow</p>
                            <p class="text-xs mt-2">Start following users to see their videos here</p>
                        </div>
                    </div>
                </div>

                <!-- Chat Messages -->
                <div class="flex-1 flex flex-col overflow-hidden relative">
                    <!-- Sidebar Toggle Button (when collapsed) -->
                    <button
                        v-if="!sidebarOpen"
                        @click="sidebarOpen = true"
                        class="absolute top-4 left-4 z-10 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-2 shadow-lg hover:shadow-xl transition-all text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200"
                        title="Show sidebar"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <div ref="chatContainer" class="flex-1 overflow-y-auto p-4 space-y-4 bg-gray-50 dark:bg-gray-900">
                    <!-- Welcome Message (if no conversation) -->
                    <div v-if="!conversation && !loading" class="flex justify-center">
                        <button
                            @click="startConversation"
                            :disabled="starting"
                            class="bg-blue-500 hover:bg-blue-600 text-white font-semibold py-3 px-6 rounded-full shadow-lg disabled:opacity-50 transition-all hover:scale-105"
                        >
                            {{ starting ? 'Starting...' : 'Start Conversation' }}
                        </button>
                    </div>

                    <!-- Chat Messages -->
                    <div v-for="message in messages" :key="message.id" class="flex flex-col space-y-2">
                        <!-- Girl/System Message -->
                        <div v-if="message.sender_type === 'girl' || message.sender_type === 'system'" class="flex justify-start">
                            <div class="max-w-[85%] md:max-w-md">
                                <!-- Video (if girl message with video) -->
                                <div v-if="message.video_url && message.sender_type === 'girl'" class="mb-2 bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                                    <div class="w-[300px] h-[450px] bg-black relative">
                                        <video
                                            :ref="el => setVideoRef(el, message)"
                                            :src="message.video_url"
                                            class="w-full h-full object-cover"
                                            :controls="!hasTrimInfo(message)"
                                            playsinline
                                            @loadedmetadata="handleVideoLoaded($event, message)"
                                            @timeupdate="handleTimeUpdate($event, message)"
                                            @ended="handleVideoEnded($event, message)"
                                            @seeked="handleVideoSeeked($event, message)"
                                            @play="videoPlayingStates.set(message.id, true)"
                                            @pause="videoPlayingStates.set(message.id, false)"
                                        ></video>
                                        <!-- Custom controls for trimmed videos -->
                                        <div v-if="hasTrimInfo(message)" class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/80 to-transparent p-4 pointer-events-none">
                                            <div class="flex items-center gap-3 text-white pointer-events-auto">
                                                <button
                                                    @click="toggleVideoPlay(message)"
                                                    class="bg-white/20 hover:bg-white/30 rounded-full p-2 transition-colors"
                                                >
                                                    <svg v-if="!isVideoPlaying(message)" class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                                        <path d="M8 5v14l11-7z" />
                                                    </svg>
                                                    <svg v-else class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                                        <path d="M6 4h4v16H6V4zm8 0h4v16h-4V4z" />
                                                    </svg>
                                                </button>
                                                <div class="flex-1">
                                                    <input
                                                        type="range"
                                                        :min="0"
                                                        :max="getTrimDuration(message)"
                                                        :value="getDisplayTime(message)"
                                                        @input="handleSeek($event, message)"
                                                        class="w-full h-1 bg-white/30 rounded-lg appearance-none cursor-pointer slider"
                                                        step="0.1"
                                                    />
                                                </div>
                                                <span class="text-sm font-medium min-w-[80px] text-right">
                                                    {{ formatTrimTime(getDisplayTime(message)) }} / {{ formatTrimTime(getTrimDuration(message)) }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Text Message -->
                                <div class="bg-white dark:bg-gray-800 rounded-2xl rounded-tl-sm px-4 py-2.5 shadow-sm">
                                    <p class="text-gray-800 dark:text-gray-200 text-sm leading-relaxed whitespace-pre-wrap">{{ message.text }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- User Message -->
                        <div v-if="message.sender_type === 'user'" class="flex justify-end">
                            <div class="max-w-[85%] md:max-w-md">
                                <div class="bg-blue-500 text-white rounded-2xl rounded-tr-sm px-4 py-2.5 shadow-sm">
                                    <p class="text-sm leading-relaxed">{{ message.text }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Current Scene Options -->
                    <div v-if="currentSceneOptions && currentSceneOptions.length > 0" class="flex flex-col space-y-2 mt-4">
                        <div class="text-xs text-gray-500 dark:text-gray-400 px-2 mb-1">Choose your response:</div>
                        <button
                            v-for="option in currentSceneOptions"
                            :key="option.id"
                            @click="selectOption(option)"
                            :disabled="replying"
                            class="bg-white dark:bg-gray-800 border-2 border-blue-500 text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-gray-700 font-medium py-3 px-5 rounded-xl text-left transition-all hover:scale-[1.02] disabled:opacity-50 shadow-sm"
                        >
                            {{ option.option_text }}
                            <span v-if="option.requires_tokens" class="ml-2 text-xs bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200 px-2 py-0.5 rounded">
                                Premium
                            </span>
                        </button>
                    </div>

                    <!-- Loading Indicator -->
                    <div v-if="loading || replying" class="flex justify-center">
                        <div class="bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-400 px-4 py-2 rounded-full text-sm">
                            {{ replying ? 'Sending...' : 'Loading...' }}
                        </div>
                    </div>

                    <!-- End of Story -->
                    <div v-if="conversation?.status === 'ended'" class="flex justify-center">
                        <div class="bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-400 px-4 py-2 rounded-full text-sm">
                            End of story. Thanks for watching! 🎉
                        </div>
                    </div>
                    </div>

                    <!-- Input Area -->
                    <div class="border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4">
                        <!-- Input will be handled by scene options above -->
                    </div>
                </div>
            </div>

            <!-- Paywall -->
            <div v-else class="flex-1 flex items-center justify-center p-8">
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-8 text-center max-w-md">
                    <div class="text-4xl mb-4">🔒</div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Unlock This Content</h2>
                    <p class="text-gray-600 dark:text-gray-300 mb-6">
                        Purchase this post for ${{ post.price }} to access all scenes and start chatting.
                    </p>
                    <button
                        @click="purchasePost"
                        :disabled="purchasing"
                        class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-3 px-8 rounded-lg disabled:opacity-50 transition-colors"
                    >
                        {{ purchasing ? 'Processing...' : 'Purchase Now' }}
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
    <GuestLayout v-else>
        <Head :title="post.title" />
        <div class="flex flex-col h-screen bg-gray-100 dark:bg-gray-900">
            <div class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-4 py-3">
                <h1 class="text-lg font-bold text-gray-900 dark:text-white">{{ post.title }}</h1>
            </div>
            <div class="flex-1 flex items-center justify-center p-8">
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-8 text-center max-w-md">
                    <p class="text-gray-600 dark:text-gray-300 mb-4">
                        Please <Link href="/login" class="text-blue-500 hover:text-blue-600 font-semibold">login</Link> to view this content.
                    </p>
                </div>
            </div>
        </div>
    </GuestLayout>
</template>

<script setup>
import { ref, computed, onMounted, nextTick, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import axios from 'axios';

const props = defineProps({
    post: Object,
    hasAccess: Boolean,
    isOwner: Boolean,
    conversation: Object,
    followedPosts: {
        type: Array,
        default: () => [],
    },
});

const chatContainer = ref(null);
const conversation = ref(props.conversation);
const loading = ref(false);
const starting = ref(false);
const replying = ref(false);
const purchasing = ref(false);
const sidebarOpen = ref(true); // Sidebar is open by default
const videoRefs = ref(new Map());
const videoPlayingStates = ref(new Map());
const videoDisplayTimes = ref(new Map());

const messages = computed(() => {
    return conversation.value?.messages || [];
});

const currentSceneOptions = computed(() => {
    if (!conversation.value?.current_scene) return null;
    return conversation.value.current_scene.options || [];
});

const switchToPost = (targetPost) => {
    // Don't switch if it's the same post
    if (targetPost.id === props.post.id) return;
    
    // Navigate to the new post
    const username = targetPost.user?.username || targetPost.user?.id;
    if (username) {
        router.visit(`/@${username}/posts/${targetPost.slug}`);
    }
};

const startConversation = async () => {
    if (starting.value) return;
    
    starting.value = true;
    try {
        const response = await axios.post(`/api/posts/${props.post.id}/conversations/start`);
        conversation.value = response.data.conversation;
        await nextTick();
        scrollToBottom();
    } catch (error) {
        console.error('Error starting conversation:', error);
        alert('Failed to start conversation. Please try again.');
    } finally {
        starting.value = false;
    }
};

const selectOption = async (option) => {
    if (replying.value || !conversation.value) return;
    
    replying.value = true;
    try {
        const response = await axios.post(`/api/conversations/${conversation.value.id}/reply`, {
            option_id: option.id,
        });
        conversation.value = response.data.conversation;
        await nextTick();
        scrollToBottom();
    } catch (error) {
        console.error('Error replying:', error);
        alert('Failed to send reply. Please try again.');
    } finally {
        replying.value = false;
    }
};

const scrollToBottom = () => {
    if (chatContainer.value) {
        chatContainer.value.scrollTop = chatContainer.value.scrollHeight;
    }
};

const purchasePost = async () => {
    if (purchasing.value) return;

    purchasing.value = true;
    try {
        const response = await axios.post('/api/purchase', {
            post_id: props.post.id,
        });

        if (response.data.client_secret) {
            // TODO: Integrate Stripe.js here
            alert('Purchase successful!');
            router.reload();
        }
    } catch (error) {
        console.error('Error purchasing post:', error);
        alert('Failed to process purchase. Please try again.');
    } finally {
        purchasing.value = false;
    }
};

onMounted(() => {
    // Always start fresh - clear any existing conversation
    // The user must click "Start Conversation" to begin
    conversation.value = null;
});

watch(() => conversation.value?.messages, () => {
    nextTick(() => {
        scrollToBottom();
    });
}, { deep: true });

// Video trimming functions
const setVideoRef = (el, message) => {
    if (el) {
        videoRefs.value.set(message.id, el);
    }
};

const hasTrimInfo = (message) => {
    const scene = message.scene;
    // If trimmed_video_url exists, the video is already trimmed - no need for client-side trimming
    if (scene && scene.trimmed_video_url) {
        return false; // Video is already trimmed, use it as-is
    }
    // Otherwise, check if we have trim timestamps for client-side trimming
    return scene && scene.trim_start !== null && scene.trim_start !== undefined && scene.trim_end !== null && scene.trim_end !== undefined;
};

const getTrimStart = (message) => {
    const scene = message.scene;
    return scene ? parseFloat(scene.trim_start || 0) : 0;
};

const getTrimEnd = (message) => {
    const scene = message.scene;
    return scene ? parseFloat(scene.trim_end || 0) : 0;
};

const getTrimDuration = (message) => {
    return getTrimEnd(message) - getTrimStart(message);
};

const getDisplayTime = (message) => {
    // Use cached display time for reactivity, or calculate from video
    const cached = videoDisplayTimes.value.get(message.id);
    if (cached !== undefined) return cached;
    
    const video = videoRefs.value.get(message.id);
    if (!video || !hasTrimInfo(message)) return 0;
    const actualTime = video.currentTime;
    const trimStart = getTrimStart(message);
    return Math.max(0, actualTime - trimStart);
};

const formatTrimTime = (seconds) => {
    const mins = Math.floor(seconds / 60);
    const secs = Math.floor(seconds % 60);
    return `${mins}:${secs.toString().padStart(2, '0')}`;
};

const isVideoPlaying = (message) => {
    return videoPlayingStates.value.get(message.id) || false;
};

const toggleVideoPlay = (message) => {
    const video = videoRefs.value.get(message.id);
    if (!video) return;
    
    if (video.paused) {
        video.play();
        videoPlayingStates.value.set(message.id, true);
    } else {
        video.pause();
        videoPlayingStates.value.set(message.id, false);
    }
};

const handleSeek = (event, message) => {
    const video = videoRefs.value.get(message.id);
    if (!video || !hasTrimInfo(message)) return;
    
    const displayTime = parseFloat(event.target.value);
    const trimStart = getTrimStart(message);
    const actualTime = trimStart + displayTime;
    
    // Clamp to trim range
    const trimEnd = getTrimEnd(message);
    video.currentTime = Math.max(trimStart, Math.min(trimEnd, actualTime));
    videoDisplayTimes.value.set(message.id, displayTime);
};

const handleVideoLoaded = (event, message) => {
    const video = event.target;
    const scene = message.scene;
    
    // Always start at trim_start if trim info exists
    if (hasTrimInfo(message)) {
        const trimStart = getTrimStart(message);
        const trimEnd = getTrimEnd(message);
        const trimDuration = trimEnd - trimStart;
        
        video.currentTime = trimStart;
        videoPlayingStates.value.set(message.id, false);
        videoDisplayTimes.value.set(message.id, 0);
        
        // Debug log
        console.log('Video loaded with trim info:', {
            messageId: message.id,
            sceneId: scene?.id,
            trimStart,
            trimEnd,
            trimDuration: trimDuration.toFixed(2),
            videoDuration: video.duration,
        });
    }
    
    scrollToBottom();
};

const handleTimeUpdate = (event, message) => {
    const video = event.target;
    const scene = message.scene;
    
    // Update display time for reactivity
    if (hasTrimInfo(message)) {
        const actualTime = video.currentTime;
        const trimStart = getTrimStart(message);
        const trimEnd = getTrimEnd(message);
        const trimDuration = trimEnd - trimStart;
        
        // If video goes before trim_start, clamp it
        if (actualTime < trimStart) {
            video.currentTime = trimStart;
            videoDisplayTimes.value.set(message.id, 0);
            return;
        }
        
        // If video goes past trim_end, stop it
        if (actualTime >= trimEnd) {
            video.pause();
            video.currentTime = trimEnd;
            videoPlayingStates.value.set(message.id, false);
            videoDisplayTimes.value.set(message.id, trimDuration);
            
            console.log('Video reached trim_end, stopping:', {
                messageId: message.id,
                sceneId: scene?.id,
                currentTime: actualTime.toFixed(3),
                trimStart: trimStart.toFixed(3),
                trimEnd: trimEnd.toFixed(3),
                trimDuration: trimDuration.toFixed(3),
            });
            return;
        }
        
        // Calculate display time (0 to duration) - only update if within range
        const displayTime = actualTime - trimStart;
        videoDisplayTimes.value.set(message.id, displayTime);
    }
};

const handleVideoSeeked = (event, message) => {
    const video = event.target;
    
    // If user seeks using native controls, clamp to trim range
    if (hasTrimInfo(message)) {
        const trimStart = getTrimStart(message);
        const trimEnd = getTrimEnd(message);
        const actualTime = video.currentTime;
        
        if (actualTime < trimStart) {
            video.currentTime = trimStart;
            videoDisplayTimes.value.set(message.id, 0);
        } else if (actualTime > trimEnd) {
            video.currentTime = trimEnd;
            videoDisplayTimes.value.set(message.id, trimEnd - trimStart);
        } else {
            // Update display time when seeking within range
            videoDisplayTimes.value.set(message.id, actualTime - trimStart);
        }
    }
};

const handleVideoEnded = (event, message) => {
    const video = event.target;
    
    // If scene has trim info, reset to trim_end (not trim_start, since we want to show the end)
    // Actually, reset to trim_start so user can replay
    if (hasTrimInfo(message)) {
        const trimStart = getTrimStart(message);
        const trimEnd = getTrimEnd(message);
        const trimDuration = trimEnd - trimStart;
        
        video.currentTime = trimStart;
        videoPlayingStates.value.set(message.id, false);
        videoDisplayTimes.value.set(message.id, 0);
        
        console.log('Video ended, reset to trim_start:', {
            messageId: message.id,
            trimStart,
            trimEnd,
            trimDuration,
        });
    }
};
</script>

<style scoped>
.slider::-webkit-slider-thumb {
    appearance: none;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    background: white;
    cursor: pointer;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
}

.slider::-moz-range-thumb {
    width: 16px;
    height: 16px;
    border-radius: 50%;
    background: white;
    cursor: pointer;
    border: none;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
}
</style>
