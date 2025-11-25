<template>
    <AuthenticatedLayout :user="$page.props.auth?.user">
        <Head :title="`Edit: ${post.title}`" />
        
        <div class="py-12 bg-gray-50 dark:bg-gray-900 min-h-screen">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                            {{ post.title }}
                        </h1>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            Add up to 10 scenes with videos, banners, and chat responses
                        </p>
                    </div>
                    <div class="flex gap-3">
                        <Link
                            :href="route('posts.show', { username: post.user?.username || post.user?.id, slug: post.slug || post.id })"
                            class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg transition-colors"
                        >
                            Preview
                        </Link>
                        <button
                            @click="publishPost"
                            :disabled="publishing"
                            class="px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-lg transition-colors disabled:opacity-50"
                        >
                            {{ publishing ? 'Publishing...' : 'Post' }}
                        </button>
                    </div>
                </div>

                <!-- Post Thumbnail -->
                <div class="mb-6 bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Post Thumbnail (Rectangle Image) *
                    </label>
                    <div v-if="post.thumbnail_url || thumbnailPreview" class="mb-4">
                        <img
                            :src="thumbnailPreview || post.thumbnail_url"
                            alt="Post thumbnail"
                            class="w-full max-w-2xl aspect-video object-cover rounded-lg"
                        />
                    </div>
                    <input
                        ref="postThumbnailInput"
                        @change="handlePostThumbnailChange"
                        type="file"
                        accept="image/*"
                        class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                    />
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        This thumbnail will appear on the homepage and profile page
                    </p>
                </div>

                <!-- Post Settings (Collapsible) -->
                <div class="mb-6">
                    <button
                        @click="showPostSettings = !showPostSettings"
                        class="w-full bg-white dark:bg-gray-800 shadow rounded-lg p-4 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                    >
                        <span class="font-semibold text-gray-900 dark:text-white">Post Settings</span>
                        <svg
                            :class="['w-5 h-5 text-gray-500 transition-transform', showPostSettings ? 'rotate-180' : '']"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div v-show="showPostSettings" class="mt-2 bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                        <form @submit.prevent="updatePost" class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Title *</label>
                                    <input v-model="postForm.title" type="text" required class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Category</label>
                                    <select v-model="postForm.category" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                        <option value="">Select category</option>
                                        <option value="romance">Romance</option>
                                        <option value="adventure">Adventure</option>
                                        <option value="comedy">Comedy</option>
                                        <option value="drama">Drama</option>
                                        <option value="fantasy">Fantasy</option>
                                        <option value="mystery">Mystery</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Description</label>
                                <textarea v-model="postForm.description" rows="2" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white"></textarea>
                            </div>
                            <div class="flex items-center gap-6">
                                <div class="flex items-center">
                                    <input v-model="postForm.is_paid" type="checkbox" id="is_paid" class="rounded border-gray-300" />
                                    <label for="is_paid" class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">Paid Post</label>
                                </div>
                                <div v-if="postForm.is_paid" class="flex-1 max-w-xs">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Price ($)</label>
                                    <input v-model="postForm.price" type="number" step="0.01" min="0" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                                </div>
                            </div>
                            <div class="flex justify-end">
                                <button type="submit" :disabled="postForm.processing" class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg disabled:opacity-50">
                                    Save Settings
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Scenes Section -->
                <div class="space-y-6">
                    <div class="flex items-center justify-between">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                            Scenes ({{ scenes.length }}/10)
                        </h2>
                        <button
                            v-if="scenes.length < 10"
                            @click="addNewScene"
                            class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg font-semibold transition-colors flex items-center gap-2"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Add Scene
                        </button>
                        <p v-else class="text-sm text-gray-500 dark:text-gray-400">Maximum 10 scenes reached</p>
                    </div>

                    <!-- Scene Cards -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div
                            v-for="(scene, index) in scenes"
                            :key="scene.id"
                            class="bg-white dark:bg-gray-800 shadow-lg rounded-xl overflow-hidden"
                        >
                            <!-- Scene Header -->
                            <div class="bg-gradient-to-r from-blue-500 to-purple-600 p-4 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <span class="text-white font-bold text-lg">Scene {{ index + 1 }}</span>
                                    <span
                                        v-if="scene.is_welcome"
                                        class="px-2 py-1 bg-green-500 text-white text-xs font-semibold rounded"
                                    >
                                        Welcome
                                    </span>
                                </div>
                                <div class="flex gap-2">
                                    <button
                                        @click="editScene(scene)"
                                        class="p-1.5 bg-white bg-opacity-20 hover:bg-opacity-30 text-white rounded transition-colors"
                                        title="Edit Scene"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>
                                    <button
                                        @click="deleteScene(scene.id)"
                                        class="p-1.5 bg-white bg-opacity-20 hover:bg-opacity-30 text-white rounded transition-colors"
                                        title="Delete Scene"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <div class="p-6 space-y-4">
                                <!-- Video -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Video
                                    </label>
                                    <div
                                        v-if="scene.video_url"
                                        class="relative aspect-video bg-black rounded-lg overflow-hidden group"
                                    >
                                        <video :src="scene.video_url" class="w-full h-full object-cover" controls></video>
                                        <button
                                            @click="editScene(scene)"
                                            class="absolute top-2 right-2 bg-black bg-opacity-50 hover:bg-opacity-70 text-white px-3 py-1.5 rounded text-sm font-semibold opacity-0 group-hover:opacity-100 transition-opacity"
                                        >
                                            Change Video
                                        </button>
                                    </div>
                                    <div
                                        v-else
                                        @click="editScene(scene)"
                                        class="aspect-video border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg flex items-center justify-center cursor-pointer hover:border-blue-500 transition-colors bg-gray-50 dark:bg-gray-700"
                                    >
                                        <div class="text-center">
                                            <svg class="w-16 h-16 mx-auto text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                            </svg>
                                            <p class="text-sm text-gray-500 dark:text-gray-400 font-semibold">+ Add Video</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Chat Message -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        AI Girlfriend's Message
                                    </label>
                                    <div class="bg-blue-50 dark:bg-blue-900 rounded-lg p-3 border-l-4 border-blue-500">
                                        <p class="text-sm text-gray-800 dark:text-gray-200">
                                            {{ scene.display_text || 'No message set. Click edit to add.' }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Reply Options -->
                                <div>
                                    <div class="flex items-center justify-between mb-2">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            User Reply Options ({{ scene.options?.length || 0 }})
                                        </label>
                                        <button
                                            @click="addOption(scene)"
                                            class="text-xs text-blue-500 hover:text-blue-600 font-semibold"
                                        >
                                            + Add Option
                                        </button>
                                    </div>
                                    <div v-if="scene.options && scene.options.length > 0" class="space-y-2">
                                        <div
                                            v-for="option in scene.options"
                                            :key="option.id"
                                            class="bg-gray-50 dark:bg-gray-700 rounded-lg p-2 flex items-center justify-between"
                                        >
                                            <div class="flex-1">
                                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ option.option_text }}</p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                                    → {{ option.next_scene ? `Scene ${scenes.findIndex(s => s.id === option.next_scene.id) + 1}` : 'End' }}
                                                </p>
                                            </div>
                                            <button
                                                @click="editOption(option, scene)"
                                                class="text-xs text-blue-500 hover:text-blue-600 mr-2"
                                            >
                                                Edit
                                            </button>
                                        </div>
                                    </div>
                                    <p v-else class="text-xs text-gray-400 dark:text-gray-500 italic">No reply options yet</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-if="scenes.length === 0" class="bg-white dark:bg-gray-800 shadow rounded-lg p-12 text-center">
                        <svg class="w-24 h-24 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">No Scenes Yet</h3>
                        <p class="text-gray-500 dark:text-gray-400 mb-6">Add your first scene to get started!</p>
                        <button
                            @click="addNewScene"
                            class="px-6 py-3 bg-blue-500 hover:bg-blue-600 text-white rounded-lg font-semibold transition-colors"
                        >
                            Add First Scene
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Scene Modal -->
        <div
            v-if="showSceneModal"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
            @click.self="closeSceneModal"
        >
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-3xl w-full max-h-[90vh] overflow-y-auto">
                <div class="p-6">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">
                        {{ editingScene ? 'Edit Scene' : 'Add New Scene' }}
                    </h2>

                    <form @submit.prevent="saveScene" class="space-y-6">
                        <!-- Video Upload -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Video (MP4, MOV, AVI) *
                            </label>
                            <input
                                ref="videoInput"
                                @change="handleVideoChange"
                                type="file"
                                accept="video/*"
                                :required="!editingScene || !sceneForm.video_url"
                                class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                            />
                            <div v-if="sceneForm.video_url && !sceneForm.video" class="mt-4">
                                <video :src="sceneForm.video_url" class="w-full max-w-md rounded-lg" controls></video>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                15-30 second video clip for this scene
                            </p>
                        </div>

                        <!-- Title -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Scene Title *
                            </label>
                            <input
                                v-model="sceneForm.title"
                                type="text"
                                required
                                class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                placeholder="e.g., Welcome, First Meeting, etc."
                            />
                        </div>

                        <!-- Display Text (Chat Message) -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                AI Girlfriend's Chat Message *
                            </label>
                            <textarea
                                v-model="sceneForm.display_text"
                                rows="3"
                                required
                                class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                placeholder="This is the message that will appear in the chat when this scene plays..."
                            ></textarea>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                What the AI girlfriend will "say" in the chat
                            </p>
                        </div>

                        <!-- Welcome Scene -->
                        <div class="flex items-center">
                            <input
                                v-model="sceneForm.is_welcome"
                                type="checkbox"
                                id="is_welcome"
                                class="rounded border-gray-300"
                            />
                            <label for="is_welcome" class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                Set as Welcome Scene (starting point)
                            </label>
                        </div>

                        <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                            <button
                                type="button"
                                @click="closeSceneModal"
                                class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 rounded-lg transition-colors"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                :disabled="savingScene"
                                class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg disabled:opacity-50 transition-colors"
                            >
                                {{ savingScene ? 'Saving...' : (editingScene ? 'Update Scene' : 'Add Scene') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Option Modal -->
        <div
            v-if="showOptionModal"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
            @click.self="closeOptionModal"
        >
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-lg w-full">
                <div class="p-6">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">
                        {{ editingOption ? 'Edit Reply Option' : 'Add Reply Option' }}
                    </h2>

                    <form @submit.prevent="saveOption" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Reply Text (What user will click) *
                            </label>
                            <input
                                v-model="optionForm.option_text"
                                type="text"
                                required
                                class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                placeholder="e.g., 'I love you too', 'Let's go on a date'"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Next Scene
                            </label>
                            <select
                                v-model="optionForm.next_scene_id"
                                class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                            >
                                <option :value="null">End of story</option>
                                <option
                                    v-for="s in scenes.filter(s => s.id !== optionForm.scene_id)"
                                    :key="s.id"
                                    :value="s.id"
                                >
                                    Scene {{ scenes.findIndex(sc => sc.id === s.id) + 1 }}: {{ s.title }}
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Display Order *
                            </label>
                            <input
                                v-model.number="optionForm.order"
                                type="number"
                                min="1"
                                required
                                class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                            />
                        </div>

                        <div class="flex items-center">
                            <input
                                v-model="optionForm.requires_tokens"
                                type="checkbox"
                                id="requires_tokens"
                                class="rounded border-gray-300"
                            />
                            <label for="requires_tokens" class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                Premium Option (Requires tokens)
                            </label>
                        </div>

                        <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                            <button
                                type="button"
                                @click="closeOptionModal"
                                class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 rounded-lg transition-colors"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                :disabled="savingOption"
                                class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg disabled:opacity-50 transition-colors"
                            >
                                {{ savingOption ? 'Saving...' : (editingOption ? 'Update' : 'Add') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { Head, Link, useForm, router, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import axios from 'axios';

const props = defineProps({
    post: Object,
});

const page = usePage();
const user = page.props.auth?.user;

const showPostSettings = ref(false);
const scenes = ref(props.post.scenes || []);
const showSceneModal = ref(false);
const editingScene = ref(null);
const showOptionModal = ref(false);
const editingOption = ref(null);
const currentSceneForOption = ref(null);
const savingScene = ref(false);
const savingOption = ref(false);
const publishing = ref(false);
const videoInput = ref(null);
const postThumbnailInput = ref(null);
const thumbnailPreview = ref(null);

const postForm = useForm({
    title: props.post.title,
    description: props.post.description || '',
    category: props.post.category || '',
    thumbnail: null,
    price: props.post.price || null,
    is_paid: props.post.is_paid || false,
});

const sceneForm = reactive({
    id: null,
    video: null,
    video_url: null,
    title: '',
    display_text: '',
    tip_prompt: '',
    is_welcome: false,
});

const optionForm = reactive({
    id: null,
    scene_id: null,
    option_text: '',
    next_scene_id: null,
    order: 1,
    ai_intent_key: '',
    requires_tokens: false,
});

const updatePost = () => {
    postForm.put(route('posts.update', props.post.id), {
        forceFormData: true,
        onSuccess: () => {
            router.reload({ only: ['post'] });
            thumbnailPreview.value = null;
            if (postThumbnailInput.value) postThumbnailInput.value.value = '';
        },
    });
};

const publishPost = async () => {
    publishing.value = true;
    try {
        // Create form with all current data plus is_published = true
        const formData = {
            title: postForm.title,
            description: postForm.description || '',
            category: postForm.category || '',
            price: postForm.price || null,
            is_paid: postForm.is_paid || false,
            is_published: true, // Explicitly set to true
        };
        
        // Add thumbnail file if it exists
        if (postForm.thumbnail) {
            formData.thumbnail = postForm.thumbnail;
        }
        
        const publishForm = useForm(formData);
        
        // Save and publish in one request
        await publishForm.put(route('posts.update', props.post.id), {
            forceFormData: true,
            onSuccess: () => {
                // Redirect to profile page after successful publish
                if (user?.username) {
                    router.visit(route('profile.show', user.username));
                } else if (user?.id) {
                    router.visit(route('profile.show', user.id));
                } else {
                    router.visit(route('home'));
                }
            },
            onError: (errors) => {
                console.error('Publish errors:', errors);
                alert('Failed to publish post. Please try again.');
                publishing.value = false;
            },
        });
    } catch (error) {
        console.error('Error publishing post:', error);
        alert('Failed to publish post. Please try again.');
        publishing.value = false;
    }
};

const handlePostThumbnailChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        postForm.thumbnail = file;
        const reader = new FileReader();
        reader.onload = (e) => {
            thumbnailPreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};


const loadScenes = async () => {
    try {
        const response = await axios.get(`/api/posts/${props.post.id}/scenes`);
        scenes.value = response.data;
    } catch (error) {
        console.error('Error loading scenes:', error);
    }
};

const addNewScene = () => {
    editingScene.value = null;
    sceneForm.id = null;
    sceneForm.video = null;
    sceneForm.video_url = null;
    sceneForm.title = '';
    sceneForm.display_text = '';
    sceneForm.tip_prompt = '';
    sceneForm.is_welcome = scenes.value.length === 0; // First scene is welcome by default
    showSceneModal.value = true;
};

const editScene = (scene) => {
    editingScene.value = scene;
    sceneForm.id = scene.id;
    sceneForm.video = null;
    sceneForm.video_url = scene.video_url;
    sceneForm.title = scene.title;
    sceneForm.display_text = scene.display_text || '';
    sceneForm.tip_prompt = scene.tip_prompt || '';
    sceneForm.is_welcome = scene.is_welcome || false;
    showSceneModal.value = true;
};

const handleVideoChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        sceneForm.video = file;
    }
};


const saveScene = async () => {
    savingScene.value = true;
    try {
        const formData = new FormData();
        if (sceneForm.video) {
            formData.append('video', sceneForm.video);
        }
        formData.append('title', sceneForm.title);
        formData.append('display_text', sceneForm.display_text);
        formData.append('tip_prompt', sceneForm.tip_prompt || '');
        formData.append('is_welcome', sceneForm.is_welcome);

        if (editingScene.value) {
            await axios.put(`/api/scenes/${editingScene.value.id}`, formData, {
                headers: { 'Content-Type': 'multipart/form-data' },
            });
        } else {
            await axios.post(`/api/posts/${props.post.id}/scenes`, formData, {
                headers: { 'Content-Type': 'multipart/form-data' },
            });
        }

        await loadScenes();
        closeSceneModal();
    } catch (error) {
        console.error('Error saving scene:', error);
        alert('Failed to save scene. Please try again.');
    } finally {
        savingScene.value = false;
    }
};

const deleteScene = async (sceneId) => {
    if (!confirm('Are you sure you want to delete this scene? This cannot be undone.')) {
        return;
    }

    try {
        await axios.delete(`/api/scenes/${sceneId}`);
        await loadScenes();
    } catch (error) {
        console.error('Error deleting scene:', error);
        alert('Failed to delete scene. Please try again.');
    }
};

const addOption = (scene) => {
    currentSceneForOption.value = scene;
    optionForm.scene_id = scene.id;
    optionForm.id = null;
    optionForm.option_text = '';
    optionForm.next_scene_id = null;
    optionForm.order = (scene.options?.length || 0) + 1;
    optionForm.ai_intent_key = '';
    optionForm.requires_tokens = false;
    showOptionModal.value = true;
};

const editOption = (option, scene) => {
    editingOption.value = option;
    currentSceneForOption.value = scene;
    optionForm.id = option.id;
    optionForm.scene_id = scene.id;
    optionForm.option_text = option.option_text;
    optionForm.next_scene_id = option.next_scene_id;
    optionForm.order = option.order;
    optionForm.ai_intent_key = option.ai_intent_key || '';
    optionForm.requires_tokens = option.requires_tokens || false;
    showOptionModal.value = true;
};

const saveOption = async () => {
    savingOption.value = true;
    try {
        const data = {
            option_text: optionForm.option_text,
            next_scene_id: optionForm.next_scene_id,
            order: optionForm.order,
            ai_intent_key: optionForm.ai_intent_key || null,
            requires_tokens: optionForm.requires_tokens,
        };

        if (editingOption.value) {
            await axios.put(`/api/options/${editingOption.value.id}`, data);
        } else {
            await axios.post(`/api/scenes/${optionForm.scene_id}/options`, data);
        }

        await loadScenes();
        closeOptionModal();
    } catch (error) {
        console.error('Error saving option:', error);
        alert('Failed to save option. Please try again.');
    } finally {
        savingOption.value = false;
    }
};

const deleteOption = async (optionId) => {
    if (!confirm('Are you sure you want to delete this option?')) {
        return;
    }

    try {
        await axios.delete(`/api/options/${optionId}`);
        await loadScenes();
    } catch (error) {
        console.error('Error deleting option:', error);
        alert('Failed to delete option. Please try again.');
    }
};

const closeSceneModal = () => {
    showSceneModal.value = false;
    editingScene.value = null;
    sceneForm.id = null;
    sceneForm.video = null;
    sceneForm.video_url = null;
    sceneForm.title = '';
    sceneForm.display_text = '';
    sceneForm.tip_prompt = '';
    sceneForm.is_welcome = false;
    if (videoInput.value) videoInput.value.value = '';
};

const closeOptionModal = () => {
    showOptionModal.value = false;
    editingOption.value = null;
    currentSceneForOption.value = null;
    optionForm.id = null;
    optionForm.scene_id = null;
    optionForm.option_text = '';
    optionForm.next_scene_id = null;
    optionForm.order = 1;
    optionForm.ai_intent_key = '';
    optionForm.requires_tokens = false;
};

onMounted(() => {
    loadScenes();
});
</script>
