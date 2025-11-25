<template>
    <AuthenticatedLayout :user="$page.props.auth?.user">
        <Head title="Create Post" />
        
        <div class="py-12 bg-gray-50 dark:bg-gray-900 min-h-screen">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="flex items-center justify-between mb-6">
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                        Create New Post
                    </h1>
                    <div class="flex gap-3">
                        <Link
                            :href="route('posts.drafts', user?.username || user?.id)"
                            class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg transition-colors"
                        >
                            Drafts
                        </Link>
                        <button
                            @click="previewPost"
                            :disabled="usedClipsCount === 0"
                            class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            Preview
                        </button>
                    </div>
                </div>

                <!-- Post Basic Info -->
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 mb-6">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Post Information</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Title *
                            </label>
                            <input
                                v-model="postForm.title"
                                type="text"
                                required
                                class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                placeholder="Enter post title"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Category
                            </label>
                            <select
                                v-model="postForm.category"
                                class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                            >
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
                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Description
                        </label>
                        <textarea
                            v-model="postForm.description"
                            rows="2"
                            class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                            placeholder="Describe your post"
                        ></textarea>
                    </div>
                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Post Thumbnail (Rectangle Image) *
                        </label>
                        <div
                            @click="() => triggerPostThumbnailUpload()"
                            class="relative aspect-video border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer transition-colors hover:border-blue-500 bg-gray-50 dark:bg-gray-700 flex items-center justify-center group"
                        >
                            <input
                                ref="postThumbnailInput"
                                @change="handlePostThumbnailChange"
                                type="file"
                                accept="image/*"
                                class="hidden"
                            />
                            <img
                                v-if="postForm.thumbnail_preview"
                                :src="postForm.thumbnail_preview"
                                alt="Thumbnail"
                                class="w-full h-full object-cover rounded-lg"
                            />
                            <div v-else class="text-center">
                                <svg class="w-12 h-12 mx-auto text-gray-400 group-hover:text-blue-500 mb-2 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Click to upload thumbnail</p>
                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">This will appear on the homepage and profile page</p>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center gap-6">
                        <div class="flex items-center">
                            <input
                                v-model="postForm.is_paid"
                                type="checkbox"
                                id="is_paid"
                                class="rounded border-gray-300"
                            />
                            <label for="is_paid" class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                Paid Post
                            </label>
                        </div>
                        <div v-if="postForm.is_paid" class="flex-1 max-w-xs">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Price ($)
                            </label>
                            <input
                                v-model="postForm.price"
                                type="number"
                                step="0.01"
                                min="0"
                                class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                            />
                        </div>
                    </div>
                </div>

                <!-- Video Cutter Section -->
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 mb-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                            Video Cutter & Editor
                        </h2>
                        <button
                            v-if="sourceVideo"
                            @click="clearSourceVideo"
                            type="button"
                            class="text-sm text-red-500 hover:text-red-600"
                        >
                            Clear Video
                        </button>
                    </div>

                    <!-- Upload Source Video -->
                    <div v-if="!sourceVideo" class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-8 text-center">
                        <input
                            ref="sourceVideoInput"
                            @change="handleSourceVideoUpload"
                            type="file"
                            accept="video/*"
                            class="hidden"
                        />
                        <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                        <p class="text-gray-600 dark:text-gray-400 mb-2">Upload a video to cut into clips</p>
                        <button
                            @click="() => sourceVideoInput?.click()"
                            type="button"
                            class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg font-semibold transition-colors"
                        >
                            Upload Video
                        </button>
                    </div>

                    <!-- Video Cutter Interface -->
                    <div v-else class="space-y-4">
                        <!-- Video Preview -->
                        <div class="relative">
                            <video
                                ref="videoPlayer"
                                :src="sourceVideoPreview"
                                class="w-full rounded-lg"
                                @loadedmetadata="onVideoLoaded"
                                @timeupdate="onTimeUpdate"
                            ></video>
                            <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black to-transparent p-4">
                                <div class="flex items-center gap-4 text-white">
                                    <button
                                        @click="togglePlay"
                                        class="bg-white bg-opacity-20 hover:bg-opacity-30 rounded-full p-2 transition-colors"
                                    >
                                        <svg v-if="!isPlaying" class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M8 5v14l11-7z" />
                                        </svg>
                                        <svg v-else class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M6 4h4v16H6V4zm8 0h4v16h-4V4z" />
                                        </svg>
                                    </button>
                                    <span class="text-sm">{{ formatDuration(currentTime) }} / {{ formatDuration(videoDuration) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Trim Controls -->
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Trim Video Segment (Max 1 minute per clip)
                                </label>
                                
                                <!-- Visual Timeline -->
                                <div class="mb-4 bg-gray-200 dark:bg-gray-700 rounded-lg p-3 relative" style="height: 60px;">
                                    <div class="absolute inset-0 flex items-center px-2">
                                        <div
                                            class="flex-1 relative h-8 bg-gray-300 dark:bg-gray-600 rounded cursor-pointer"
                                            @click="seekOnTimeline"
                                        >
                                            <!-- Current Time Indicator -->
                                            <div
                                                v-if="videoDuration > 0"
                                                class="absolute w-0.5 h-full bg-white z-10"
                                                :style="{ left: (currentTime / videoDuration * 100) + '%' }"
                                            ></div>
                                            <!-- Trim Range Indicator -->
                                            <div
                                                class="absolute bg-blue-500 h-full rounded opacity-60"
                                                :style="{
                                                    left: (trimStart / videoDuration * 100) + '%',
                                                    width: ((trimEnd - trimStart) / videoDuration * 100) + '%',
                                                }"
                                            ></div>
                                            <!-- Start Handle -->
                                            <div
                                                class="absolute w-3 h-full bg-blue-600 cursor-ew-resize rounded-l z-20 hover:bg-blue-700"
                                                :style="{ left: (trimStart / videoDuration * 100) + '%' }"
                                                @mousedown.stop="startDraggingTrim('start', $event)"
                                            ></div>
                                            <!-- End Handle -->
                                            <div
                                                class="absolute w-3 h-full bg-blue-600 cursor-ew-resize rounded-r z-20 hover:bg-blue-700"
                                                :style="{ left: ((trimEnd / videoDuration * 100) - 0.75) + '%' }"
                                                @mousedown.stop="startDraggingTrim('end', $event)"
                                            ></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="space-y-2">
                                    <div class="flex items-center gap-4">
                                        <div class="flex-1">
                                            <label class="text-xs text-gray-500 dark:text-gray-400 mb-1 block">Start Time</label>
                                            <input
                                                v-model.number="trimStart"
                                                type="range"
                                                :min="0"
                                                :max="videoDuration"
                                                step="0.1"
                                                class="w-full"
                                                @input="updateTrim"
                                            />
                                            <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                <span>{{ formatDuration(trimStart) }}</span>
                                            </div>
                                        </div>
                                        <div class="flex-1">
                                            <label class="text-xs text-gray-500 dark:text-gray-400 mb-1 block">End Time</label>
                                            <input
                                                v-model.number="trimEnd"
                                                type="range"
                                                :min="0"
                                                :max="videoDuration"
                                                step="0.1"
                                                class="w-full"
                                                @input="updateTrim"
                                            />
                                            <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                <span>{{ formatDuration(trimEnd) }}</span>
                                                <span class="text-blue-500 font-semibold">Duration: {{ formatDuration(trimEnd - trimStart) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex gap-2">
                                        <button
                                            @click="setTrimToCurrent"
                                            type="button"
                                            class="px-3 py-1.5 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 rounded text-sm transition-colors"
                                        >
                                            Set to Current
                                        </button>
                                        <button
                                            @click="previewTrim"
                                            type="button"
                                            class="px-3 py-1.5 bg-blue-500 hover:bg-blue-600 text-white rounded text-sm transition-colors"
                                        >
                                            Preview Segment
                                        </button>
                                        <button
                                            @click="createSegment"
                                            type="button"
                                            :disabled="trimEnd - trimStart > 60 || trimEnd <= trimStart"
                                            class="px-3 py-1.5 bg-green-500 hover:bg-green-600 text-white rounded text-sm transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                            </svg>
                                            Create Clip ({{ formatDuration(trimEnd - trimStart) }})
                                        </button>
                                    </div>
                                    <p v-if="trimEnd - trimStart > 60" class="text-xs text-red-500">
                                        ⚠️ Segment exceeds 1 minute limit
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                                        💡 Tip: Use the timeline above to visually set trim points, or use the sliders for precise control
                                    </p>
                                </div>
                            </div>

                            <!-- Created Segments -->
                            <div v-if="videoSegments.length > 0" class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                                    Created Clips ({{ videoSegments.length }}) - Drag to clip boxes below or click "Assign" to choose
                                </label>
                                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-3">
                                    <div
                                        v-for="(segment, segIndex) in videoSegments"
                                        :key="segIndex"
                                        draggable="true"
                                        @dragstart="(e) => startDrag(e, segIndex)"
                                        @dragend="draggedSegmentIndex = null"
                                        class="bg-gray-100 dark:bg-gray-700 rounded-lg p-3 cursor-move hover:bg-gray-200 dark:hover:bg-gray-600 transition-all border-2 border-dashed border-gray-300 dark:border-gray-600 hover:border-blue-500 hover:shadow-lg group"
                                    >
                                        <div class="aspect-video bg-black rounded mb-2 relative overflow-hidden">
                                            <video
                                                :src="segment.preview"
                                                :data-start="segment.start"
                                                :data-end="segment.end"
                                                class="w-full h-full object-cover"
                                                muted
                                                @mouseenter="(e) => { e.target.currentTime = segment.start; e.target.play(); }"
                                                @mouseleave="(e) => { e.target.pause(); e.target.currentTime = segment.start; }"
                                                @timeupdate="(e) => { if (e.target.currentTime >= segment.end) { e.target.pause(); e.target.currentTime = segment.start; } }"
                                            ></video>
                                            <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black to-transparent p-1">
                                                <p class="text-white text-xs text-center font-semibold">{{ formatDuration(segment.duration) }}</p>
                                            </div>
                                            <div class="absolute top-2 right-2 bg-blue-500 text-white text-xs px-2 py-0.5 rounded font-semibold opacity-0 group-hover:opacity-100 transition-opacity">
                                                Drag or Click
                                            </div>
                                        </div>
                                        <p class="text-xs font-semibold text-gray-900 dark:text-white mb-1">
                                            Clip {{ segIndex + 1 }}
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">
                                            {{ formatDuration(segment.start) }} - {{ formatDuration(segment.end) }}
                                        </p>
                                        <div class="flex gap-2">
                                            <button
                                                @click.stop="openAssignModal(segIndex)"
                                                class="flex-1 px-2 py-1 bg-blue-500 hover:bg-blue-600 text-white text-xs rounded transition-colors"
                                            >
                                                Assign
                                            </button>
                                            <button
                                                @click.stop="removeSegment(segIndex)"
                                                class="px-2 py-1 bg-red-500 hover:bg-red-600 text-white text-xs rounded transition-colors"
                                            >
                                                ×
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 10 Clip Boxes -->
                <div class="mb-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                            Video Clips ({{ usedClipsCount }}/10)
                        </h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Max 1 minute per clip
                        </p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4">
                        <div
                            v-for="(scene, index) in scenes"
                            :key="index"
                            class="bg-white dark:bg-gray-800 shadow-lg rounded-xl overflow-hidden border-2 transition-colors"
                            :class="(scene.video || scene.display_text || (scene.options && scene.options.length > 0)) ? 'border-blue-500' : 'border-gray-200 dark:border-gray-700'"
                        >
                            <!-- Clip Number Header -->
                            <div class="bg-gradient-to-r from-blue-500 to-purple-600 p-3 text-center">
                                <span class="text-white font-bold text-lg">Clip {{ index + 1 }}</span>
                                <span
                                    v-if="index === 0"
                                    class="ml-2 px-2 py-0.5 bg-green-500 text-white text-xs font-semibold rounded"
                                >
                                    Start
                                </span>
                            </div>

                            <div class="p-4 space-y-4">
                                <!-- Video Upload Area -->
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Video (Max 1 min)
                                    </label>
                                    <div
                                        @click="() => triggerVideoUpload(index)"
                                        @drop="(e) => handleDrop(e, index)"
                                        @dragover.prevent
                                        @dragenter.prevent="draggedOverIndex = index"
                                        @dragleave.prevent="(e) => { if (e.target === e.currentTarget) draggedOverIndex = null; }"
                                        class="relative aspect-video border-2 border-dashed rounded-lg cursor-pointer transition-colors hover:border-blue-500 bg-gray-50 dark:bg-gray-700 flex items-center justify-center group"
                                        :class="draggedOverIndex === index ? 'border-blue-500 bg-blue-50 dark:bg-blue-900 scale-105' : ''"
                                    >
                                        <div
                                            v-if="draggedOverIndex === index"
                                            class="absolute inset-0 bg-blue-500 bg-opacity-20 rounded-lg flex items-center justify-center z-10"
                                        >
                                            <p class="text-blue-600 dark:text-blue-400 font-semibold">Drop here</p>
                                        </div>
                                        <input
                                            :ref="el => videoInputs[index] = el"
                                            @change="(e) => handleVideoChange(e, index)"
                                            type="file"
                                            accept="video/*"
                                            class="hidden"
                                        />
                                        <div v-if="scene.video_preview" class="absolute inset-0">
                                            <video
                                                :ref="el => setClipVideoRef(el, index)"
                                                :src="scene.video_preview"
                                                class="w-full h-full object-cover rounded-lg"
                                                controls
                                                @loadedmetadata="handleClipVideoLoaded($event, index)"
                                                @timeupdate="handleClipTimeUpdate($event, index)"
                                                @ended="handleClipVideoEnded($event, index)"
                                            ></video>
                                            <div class="absolute top-2 right-2 bg-black bg-opacity-50 text-white text-xs px-2 py-1 rounded">
                                                {{ formatDuration(scene.video_duration) }}
                                            </div>
                                            <button
                                                @click.stop="removeVideo(index)"
                                                class="absolute top-2 left-2 bg-red-500 hover:bg-red-600 text-white p-1.5 rounded-full transition-colors"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        </div>
                                        <div v-else class="text-center">
                                            <svg class="w-12 h-12 mx-auto text-gray-400 group-hover:text-blue-500 mb-2 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                            </svg>
                                            <p class="text-xs text-gray-500 dark:text-gray-400 font-semibold">+ Add Video</p>
                                        </div>
                                    </div>
                                    <p v-if="scene.video_duration > 60" class="text-xs text-red-500 mt-1">
                                        ⚠️ Exceeds 1 minute limit
                                    </p>
                                </div>

                                <!-- AI Girlfriend Message Bubble -->
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        AI Message
                                    </label>
                                    <div class="bg-blue-50 dark:bg-blue-900 rounded-lg p-3 border-l-4 border-blue-500">
                                        <textarea
                                            v-model="scene.display_text"
                                            rows="2"
                                            class="w-full bg-transparent border-none resize-none text-sm text-gray-800 dark:text-gray-200 focus:outline-none"
                                            placeholder="What the AI girlfriend will say..."
                                        ></textarea>
                                    </div>
                                </div>

                                <!-- User Response Options -->
                                <div>
                                    <div class="flex items-center justify-between mb-2">
                                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">
                                            User Responses
                                        </label>
                                        <button
                                            @click="addOption(index)"
                                            type="button"
                                            class="text-xs text-blue-500 hover:text-blue-600 font-semibold"
                                        >
                                            + Add
                                        </button>
                                    </div>
                                    <div v-if="scene.options && scene.options.length > 0" class="space-y-2">
                                        <div
                                            v-for="(option, optIndex) in scene.options"
                                            :key="optIndex"
                                            class="bg-green-50 dark:bg-green-900 rounded-lg p-2 border-l-4 border-green-500 flex items-center justify-between group"
                                        >
                                            <input
                                                v-model="option.option_text"
                                                type="text"
                                                class="flex-1 bg-transparent border-none text-sm text-gray-800 dark:text-gray-200 focus:outline-none"
                                                placeholder="User response..."
                                            />
                                            <button
                                                @click="removeOption(index, optIndex)"
                                                type="button"
                                                class="opacity-0 group-hover:opacity-100 text-red-500 hover:text-red-600 transition-opacity"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                    <div v-else class="text-xs text-gray-400 dark:text-gray-500 italic text-center py-2">
                                        No responses yet
                                    </div>
                                </div>

                                <!-- Next Clip Selection -->
                                <div v-if="scene.options && scene.options.length > 0">
                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Link Responses To
                                    </label>
                                    <div class="space-y-1">
                                        <div
                                            v-for="(option, optIndex) in scene.options"
                                            :key="optIndex"
                                            class="flex items-center gap-2"
                                        >
                                            <span class="text-xs text-gray-600 dark:text-gray-400 w-20 truncate">{{ option.option_text || 'Option' }}</span>
                                            <select
                                                v-model="option.next_scene_index"
                                                class="flex-1 text-xs rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                            >
                                                <option :value="null">End</option>
                                                <option
                                                    v-for="(s, idx) in scenes.filter((s, i) => i > index)"
                                                    :key="idx"
                                                    :value="scenes.findIndex(sc => sc === s)"
                                                >
                                                    Clip {{ scenes.findIndex(sc => sc === s) + 1 }}
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit Section -->
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            <span v-if="usedClipsCount === 0" class="text-red-500">Add at least one clip to continue</span>
                            <span v-else-if="hasInvalidDuration" class="text-red-500">Some clips exceed 1 minute limit</span>
                            <span v-else class="text-green-600">Ready to create post ({{ usedClipsCount }} clip{{ usedClipsCount !== 1 ? 's' : '' }})</span>
                        </p>
                    </div>
                    <div class="flex gap-3">
                        <Link
                            :href="route('home')"
                            class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 rounded-lg transition-colors"
                        >
                            Cancel
                        </Link>
                        <button
                            @click="submit"
                            :disabled="usedClipsCount === 0 || hasInvalidDuration || creating"
                            class="px-6 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg font-semibold disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                        >
                            {{ creating ? 'Creating...' : 'Create Post' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Assign Segment Modal -->
        <div
            v-if="showAssignSegmentModal"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
            @click.self="showAssignSegmentModal = false; segmentToAssign = null"
        >
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-2xl w-full">
                <div class="p-6">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">
                        Assign Clip to Box
                    </h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                        Select which clip box (1-10) to assign this segment to:
                    </p>
                    <div class="grid grid-cols-5 gap-3">
                        <button
                            v-for="(scene, index) in scenes"
                            :key="index"
                            @click="assignSegmentToClip(segmentToAssign, index)"
                            :disabled="scene.video"
                            class="p-4 border-2 rounded-lg transition-all text-center"
                            :class="scene.video 
                                ? 'border-gray-300 dark:border-gray-600 bg-gray-100 dark:bg-gray-700 opacity-50 cursor-not-allowed' 
                                : 'border-blue-500 hover:border-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900 cursor-pointer'"
                        >
                            <div class="text-lg font-bold text-gray-900 dark:text-white mb-1">
                                {{ index + 1 }}
                            </div>
                            <div v-if="scene.video" class="text-xs text-gray-500 dark:text-gray-400">
                                Occupied
                            </div>
                            <div v-else class="text-xs text-blue-600 dark:text-blue-400">
                                Available
                            </div>
                        </button>
                    </div>
                    <div class="mt-6 flex justify-end">
                        <button
                            @click="showAssignSegmentModal = false; segmentToAssign = null"
                            class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 rounded-lg transition-colors"
                        >
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed, nextTick } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import axios from 'axios';

const page = usePage();
const user = page.props.auth?.user;

const postForm = ref({
    title: '',
    description: '',
    category: '',
    thumbnail: null,
    thumbnail_preview: null,
    price: null,
    is_paid: false,
});

// Initialize 10 empty scenes
const scenes = ref(Array.from({ length: 10 }, () => ({
    title: '',
    display_text: '',
    video: null,
    video_preview: null,
    video_duration: 0,
    options: [],
})));

const videoInputs = ref([]);
const postThumbnailInput = ref(null);
const creating = ref(false);

// Video Cutter State
const sourceVideoInput = ref(null);
const videoPlayer = ref(null);
const sourceVideo = ref(null);
const sourceVideoPreview = ref(null);
const videoDuration = ref(0);
const currentTime = ref(0);
const isPlaying = ref(false);
const trimStart = ref(0);
const trimEnd = ref(0);
const videoSegments = ref([]);
const draggedSegmentIndex = ref(null);
const draggedOverIndex = ref(null);
const draggingTrim = ref(null);
const trimDragStartX = ref(0);
const trimDragStartValue = ref(0);

const usedClipsCount = computed(() => {
    return scenes.value.filter(scene => scene.video || scene.display_text || (scene.options && scene.options.length > 0)).length;
});

const hasInvalidDuration = computed(() => {
    return scenes.value.some(scene => scene.video_duration > 60);
});

const formatDuration = (seconds) => {
    if (!seconds) return '0:00';
    const mins = Math.floor(seconds / 60);
    const secs = Math.floor(seconds % 60);
    return `${mins}:${secs.toString().padStart(2, '0')}`;
};

const previewPost = async () => {
    // Save as draft first, then redirect to preview
    if (usedClipsCount.value === 0) {
        alert('Please add at least one clip to preview.');
        return;
    }

    if (hasInvalidDuration.value) {
        alert('Some clips exceed the 1 minute limit. Please adjust them before previewing.');
        return;
    }

    if (!postForm.value.title || postForm.value.title.trim() === '') {
        alert('Please enter a post title.');
        return;
    }

    creating.value = true;
    try {
        // Create the post as draft
        const postFormData = new FormData();
        postFormData.append('title', postForm.value.title.trim());
        postFormData.append('description', postForm.value.description || '');
        postFormData.append('category', postForm.value.category || '');
        postFormData.append('price', postForm.value.price || '');
        postFormData.append('is_paid', postForm.value.is_paid ? '1' : '0');
        postFormData.append('is_published', '0'); // Save as draft

        if (postForm.value.thumbnail) {
            postFormData.append('thumbnail', postForm.value.thumbnail);
        }

        const csrfToken = document.head.querySelector('meta[name="csrf-token"]')?.content;
        if (csrfToken) {
            postFormData.append('_token', csrfToken);
        }

        const postResponse = await axios.post('/api/posts', postFormData, {
            headers: { 
                'Content-Type': 'multipart/form-data',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken || '',
            },
            withCredentials: true,
        });

        if (!postResponse.data || !postResponse.data.id) {
            throw new Error('Failed to create post: Invalid response from server');
        }

        const postId = postResponse.data.id;

        // Create scenes (same as submit function)
        const validScenes = scenes.value.filter(scene => scene.video);
        const createdSceneIds = [];
        
        for (let i = 0; i < validScenes.length; i++) {
            const scene = validScenes[i];
            const sceneFormData = new FormData();
            
            if (!scene.video) continue;

            if (scene.trimInfo) {
                sceneFormData.append('video', scene.video);
                sceneFormData.append('trim_start', scene.trimInfo.start);
                sceneFormData.append('trim_end', scene.trimInfo.end);
            } else {
                sceneFormData.append('video', scene.video);
            }
            
            sceneFormData.append('title', scene.title || `Clip ${i + 1}`);
            sceneFormData.append('display_text', scene.display_text || '');
            sceneFormData.append('is_welcome', i === 0 ? '1' : '0');

            try {
                if (csrfToken) {
                    sceneFormData.append('_token', csrfToken);
                }

                const sceneResponse = await axios.post(`/api/posts/${postId}/scenes`, sceneFormData, {
                    headers: { 
                        'Content-Type': 'multipart/form-data',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken || '',
                    },
                    withCredentials: true,
                });

                const sceneId = sceneResponse.data?.id || sceneResponse.data?.scene?.id || sceneResponse.data?.data?.id;
                if (sceneId) {
                    createdSceneIds.push(sceneId);
                }
            } catch (sceneError) {
                console.error(`Error creating scene ${i + 1}:`, sceneError);
            }
        }

        // Create options
        for (let i = 0; i < validScenes.length; i++) {
            const scene = validScenes[i];
            const sceneId = createdSceneIds[i];
            if (!sceneId || !scene.options || scene.options.length === 0) continue;

            for (let optIndex = 0; optIndex < scene.options.length; optIndex++) {
                const option = scene.options[optIndex];
                if (!option.option_text || option.option_text.trim() === '') continue;

                try {
                    await axios.post(`/api/scenes/${sceneId}/options`, {
                        option_text: option.option_text,
                        next_scene_id: option.next_scene_id || null,
                        order: option.order || optIndex + 1,
                        ai_intent_key: option.ai_intent_key || null,
                        requires_tokens: option.requires_tokens || false,
                        _token: csrfToken,
                    }, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken || '',
                        },
                        withCredentials: true,
                    });
                } catch (optionError) {
                    console.error(`Error creating option for scene ${i + 1}:`, optionError);
                }
            }
        }

        // Redirect to preview (post show page)
        router.visit(`/posts/${postId}`);
    } catch (error) {
        console.error('Error creating draft for preview:', error);
        const errorMessage = error.response?.data?.message || error.message || 'Unknown error occurred';
        alert(`Failed to create draft: ${errorMessage}`);
        creating.value = false;
    }
};

const triggerVideoUpload = (index) => {
    if (videoInputs.value[index]) {
        videoInputs.value[index].click();
    }
};

const triggerPostThumbnailUpload = () => {
    if (postThumbnailInput.value) {
        postThumbnailInput.value.click();
    }
};

const handleVideoChange = (event, index) => {
    const file = event.target.files[0];
    if (file) {
        scenes.value[index].video = file;
        const url = URL.createObjectURL(file);
        scenes.value[index].video_preview = url;
        
        // Get video duration
        const video = document.createElement('video');
        video.preload = 'metadata';
        video.onloadedmetadata = () => {
            const duration = video.duration;
            scenes.value[index].video_duration = duration;
            
            // If no trim info exists, set default trim (full video)
            // But ideally, users should use the video cutter to create segments
            if (!scenes.value[index].trimInfo) {
                scenes.value[index].trimInfo = {
                    start: 0,
                    end: duration,
                };
            }
            
            window.URL.revokeObjectURL(url);
        };
        video.src = url;
    }
};

const removeVideo = (index) => {
    scenes.value[index].video = null;
    scenes.value[index].video_preview = null;
    scenes.value[index].video_duration = 0;
    scenes.value[index].trimInfo = null;
    if (videoInputs.value[index]) {
        videoInputs.value[index].value = '';
    }
};

// Video trimming functions for clip previews
const clipVideoRefs = ref(new Map());

const setClipVideoRef = (el, index) => {
    if (el) {
        clipVideoRefs.value.set(index, el);
    }
};

const handleClipVideoLoaded = (event, index) => {
    const video = event.target;
    const scene = scenes.value[index];
    
    // If scene has trim info, set the video to start at trim_start
    if (scene && scene.trimInfo) {
        video.currentTime = parseFloat(scene.trimInfo.start || 0);
    }
};

const handleClipTimeUpdate = (event, index) => {
    const video = event.target;
    const scene = scenes.value[index];
    
    // If scene has trim info, stop video at trim_end
    if (scene && scene.trimInfo) {
        const trimEnd = parseFloat(scene.trimInfo.end);
        if (video.currentTime >= trimEnd) {
            video.pause();
            video.currentTime = parseFloat(scene.trimInfo.start || 0);
        }
    }
};

const handleClipVideoEnded = (event, index) => {
    const video = event.target;
    const scene = scenes.value[index];
    
    // If scene has trim info, loop back to trim_start
    if (scene && scene.trimInfo) {
        video.currentTime = parseFloat(scene.trimInfo.start || 0);
        // Don't auto-play, let user control it
    }
};

const handlePostThumbnailChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        postForm.value.thumbnail = file;
        const reader = new FileReader();
        reader.onload = (e) => {
            postForm.value.thumbnail_preview = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

const addOption = (sceneIndex) => {
    if (!scenes.value[sceneIndex].options) {
        scenes.value[sceneIndex].options = [];
    }
    scenes.value[sceneIndex].options.push({
        option_text: '',
        next_scene_index: null,
        order: scenes.value[sceneIndex].options.length + 1,
    });
};

const removeOption = (sceneIndex, optionIndex) => {
    scenes.value[sceneIndex].options.splice(optionIndex, 1);
};

// Video Cutter Functions
const handleSourceVideoUpload = (event) => {
    const file = event.target.files[0];
    if (file) {
        sourceVideo.value = file;
        const url = URL.createObjectURL(file);
        sourceVideoPreview.value = url;
    }
};

const clearSourceVideo = () => {
    sourceVideo.value = null;
    sourceVideoPreview.value = null;
    videoDuration.value = 0;
    currentTime.value = 0;
    trimStart.value = 0;
    trimEnd.value = 0;
    videoSegments.value = [];
    if (videoPlayer.value) {
        videoPlayer.value.pause();
        isPlaying.value = false;
    }
    if (sourceVideoInput.value) {
        sourceVideoInput.value.value = '';
    }
};

const onVideoLoaded = () => {
    if (videoPlayer.value) {
        videoDuration.value = videoPlayer.value.duration;
        trimEnd.value = Math.min(60, videoDuration.value); // Default to 1 minute or video length
    }
};

const onTimeUpdate = () => {
    if (videoPlayer.value) {
        currentTime.value = videoPlayer.value.currentTime;
    }
};

const togglePlay = () => {
    if (videoPlayer.value) {
        if (isPlaying.value) {
            videoPlayer.value.pause();
        } else {
            videoPlayer.value.play();
        }
        isPlaying.value = !isPlaying.value;
    }
};

const setTrimToCurrent = () => {
    if (videoPlayer.value) {
        trimStart.value = videoPlayer.value.currentTime;
        if (trimEnd.value <= trimStart.value) {
            trimEnd.value = Math.min(trimStart.value + 60, videoDuration.value);
        }
    }
};

const updateTrim = () => {
    if (trimEnd.value <= trimStart.value) {
        trimEnd.value = trimStart.value + 0.1;
    }
    if (trimEnd.value > videoDuration.value) {
        trimEnd.value = videoDuration.value;
    }
    if (trimStart.value < 0) {
        trimStart.value = 0;
    }
    // Ensure segment doesn't exceed 1 minute
    if (trimEnd.value - trimStart.value > 60) {
        trimEnd.value = trimStart.value + 60;
    }
};

const seekOnTimeline = (event) => {
    if (!videoPlayer.value || !videoDuration.value) return;
    const rect = event.currentTarget.getBoundingClientRect();
    const percent = Math.max(0, Math.min(1, (event.clientX - rect.left) / rect.width));
    const newTime = percent * videoDuration.value;
    videoPlayer.value.currentTime = newTime;
    currentTime.value = newTime;
};

const startDraggingTrim = (type, event) => {
    event.stopPropagation();
    draggingTrim.value = type;
    trimDragStartX.value = event.clientX;
    trimDragStartValue.value = type === 'start' ? trimStart.value : trimEnd.value;
    
    const timelineElement = event.currentTarget.closest('.relative').querySelector('.cursor-pointer');
    if (!timelineElement) return;
    
    const rect = timelineElement.getBoundingClientRect();
    
    const handleMouseMove = (e) => {
        if (!videoDuration.value) return;
        const percent = Math.max(0, Math.min(1, (e.clientX - rect.left) / rect.width));
        const newValue = percent * videoDuration.value;
        
        if (draggingTrim.value === 'start') {
            trimStart.value = Math.max(0, Math.min(newValue, trimEnd.value - 0.1));
        } else {
            trimEnd.value = Math.min(videoDuration.value, Math.max(newValue, trimStart.value + 0.1));
        }
        updateTrim();
    };
    
    const handleMouseUp = () => {
        draggingTrim.value = null;
        document.removeEventListener('mousemove', handleMouseMove);
        document.removeEventListener('mouseup', handleMouseUp);
    };
    
    document.addEventListener('mousemove', handleMouseMove);
    document.addEventListener('mouseup', handleMouseUp);
};

const previewTrim = () => {
    if (videoPlayer.value) {
        videoPlayer.value.currentTime = trimStart.value;
        videoPlayer.value.play();
        isPlaying.value = true;
        
        // Stop at trim end
        const checkTime = () => {
            if (videoPlayer.value && videoPlayer.value.currentTime >= trimEnd.value) {
                videoPlayer.value.pause();
                videoPlayer.value.currentTime = trimStart.value;
                isPlaying.value = false;
            } else if (isPlaying.value) {
                requestAnimationFrame(checkTime);
            }
        };
        checkTime();
    }
};

const createSegment = async () => {
    if (trimEnd.value - trimStart.value > 60) {
        alert('Segment cannot exceed 1 minute');
        return;
    }
    
    if (trimEnd.value <= trimStart.value) {
        alert('End time must be after start time');
        return;
    }

    // Create segment object
    const segment = {
        start: trimStart.value,
        end: trimEnd.value,
        duration: trimEnd.value - trimStart.value,
        sourceFile: sourceVideo.value,
        preview: sourceVideoPreview.value,
        trimInfo: {
            start: trimStart.value,
            end: trimEnd.value,
        },
    };

    videoSegments.value.push(segment);
    
    // Reset trim to next segment
    trimStart.value = trimEnd.value;
    trimEnd.value = Math.min(trimStart.value + 60, videoDuration.value);
    
    // Scroll to segments section if it exists
    await nextTick();
    const segmentsSection = document.querySelector('.border-t.border-gray-200');
    if (segmentsSection) {
        segmentsSection.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
};

const removeSegment = (index) => {
    videoSegments.value.splice(index, 1);
};

const startDrag = (event, segmentIndex) => {
    draggedSegmentIndex.value = segmentIndex;
    event.dataTransfer.effectAllowed = 'move';
};

const handleDrop = (event, clipIndex) => {
    event.preventDefault();
    draggedOverIndex.value = null;
    
    if (draggedSegmentIndex.value !== null) {
        assignSegmentToClip(draggedSegmentIndex.value, clipIndex);
        draggedSegmentIndex.value = null;
    }
};

const showAssignSegmentModal = ref(false);
const segmentToAssign = ref(null);

const openAssignModal = (segmentIndex) => {
    segmentToAssign.value = segmentIndex;
    showAssignSegmentModal.value = true;
};

const assignSegmentToClip = (segmentIndex, clipIndex) => {
    const segment = videoSegments.value[segmentIndex];
    scenes.value[clipIndex].video_segment = segment;
    scenes.value[clipIndex].video = segment.sourceFile;
    scenes.value[clipIndex].video_preview = segment.preview;
    scenes.value[clipIndex].video_duration = segment.duration;
    scenes.value[clipIndex].trimInfo = segment.trimInfo;
    
    // Debug: Log the assignment
    console.log(`Assigned segment ${segmentIndex + 1} to clip ${clipIndex + 1}:`, {
        trim_start: segment.trimInfo?.start,
        trim_end: segment.trimInfo?.end,
        duration: segment.duration,
    });
    
    showAssignSegmentModal.value = false;
    segmentToAssign.value = null;
};

const assignSegmentToNextEmpty = (segmentIndex) => {
    const emptyIndex = scenes.value.findIndex(scene => !scene.video && !scene.display_text);
    if (emptyIndex >= 0) {
        assignSegmentToClip(segmentIndex, emptyIndex);
    } else {
        // No empty clip, show modal to choose
        openAssignModal(segmentIndex);
    }
};

const submit = async () => {
    // Filter to only scenes with videos (required by API)
    const validScenes = scenes.value.filter(scene => scene.video);

    if (validScenes.length === 0) {
        alert('Please add at least one video clip to create a post.');
        return;
    }

    if (hasInvalidDuration.value) {
        alert('Some clips exceed the 1 minute limit. Please adjust them before submitting.');
        return;
    }

    // Validate post title
    if (!postForm.value.title || postForm.value.title.trim() === '') {
        alert('Please enter a post title.');
        return;
    }

    creating.value = true;
    try {
        // Create the post via API
        const postFormData = new FormData();
        postFormData.append('title', postForm.value.title.trim());
        postFormData.append('description', postForm.value.description || '');
        postFormData.append('category', postForm.value.category || '');
        postFormData.append('price', postForm.value.price || '');
        postFormData.append('is_paid', postForm.value.is_paid ? '1' : '0');
        postFormData.append('is_published', '1'); // Publish immediately
        if (postForm.value.thumbnail) {
            postFormData.append('thumbnail', postForm.value.thumbnail);
        }

        // Get fresh CSRF token before making request
        const csrfToken = document.head.querySelector('meta[name="csrf-token"]')?.content;
        if (csrfToken) {
            postFormData.append('_token', csrfToken);
        }

        const postResponse = await axios.post('/api/posts', postFormData, {
            headers: { 
                'Content-Type': 'multipart/form-data',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken || '',
            },
            withCredentials: true,
        });

        if (!postResponse.data || !postResponse.data.id) {
            throw new Error('Failed to create post: Invalid response from server');
        }

        const postId = postResponse.data.id;
                
        // Create all valid scenes (only those with videos)
        const createdSceneIds = [];
        for (let i = 0; i < validScenes.length; i++) {
            const scene = validScenes[i];
            const sceneFormData = new FormData();
            
            // Video is required
            if (!scene.video) {
                console.warn(`Skipping scene ${i + 1}: No video file`);
                continue;
            }

            // Handle video - always send trim info if available
            sceneFormData.append('video', scene.video);
            
            // If trim info exists, send it (should always exist for trimmed segments)
            if (scene.trimInfo && scene.trimInfo.start !== undefined && scene.trimInfo.end !== undefined) {
                const trimStart = parseFloat(scene.trimInfo.start);
                const trimEnd = parseFloat(scene.trimInfo.end);
                sceneFormData.append('trim_start', trimStart.toString());
                sceneFormData.append('trim_end', trimEnd.toString());
                
                console.log(`Scene ${i + 1} - Sending trim info:`, {
                    trim_start: trimStart,
                    trim_end: trimEnd,
                    duration: trimEnd - trimStart,
                });
            } else {
                // If no trim info, set default (full video)
                // This handles direct uploads (though ideally users should use video cutter)
                const videoDuration = scene.video_duration || 0;
                sceneFormData.append('trim_start', '0');
                sceneFormData.append('trim_end', videoDuration.toString());
                
                console.warn(`Scene ${i + 1} - No trim info found, using full video duration:`, {
                    video_duration: videoDuration,
                    has_trimInfo: !!scene.trimInfo,
                });
            }
            
            sceneFormData.append('title', scene.title || `Clip ${i + 1}`);
            sceneFormData.append('display_text', scene.display_text || '');
            sceneFormData.append('is_welcome', i === 0 ? '1' : '0');
            
            // Debug: Log what we're sending
            console.log(`Creating scene ${i + 1}:`, {
                hasVideo: !!scene.video,
                title: scene.title || `Clip ${i + 1}`,
                display_text: scene.display_text || '',
                is_welcome: i === 0,
            });

            try {
                // Get fresh CSRF token
                const csrfToken = document.head.querySelector('meta[name="csrf-token"]')?.content;
                if (csrfToken) {
                    sceneFormData.append('_token', csrfToken);
                }

                const sceneResponse = await axios.post(`/api/posts/${postId}/scenes`, sceneFormData, {
                    headers: { 
                        'Content-Type': 'multipart/form-data',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken || '',
                    },
                    withCredentials: true,
                });

                console.log(`Scene ${i + 1} response:`, sceneResponse);
                console.log(`Scene ${i + 1} response data:`, sceneResponse.data);
                console.log(`Scene ${i + 1} response status:`, sceneResponse.status);

                // Check if response has id (could be direct object or nested)
                const sceneId = sceneResponse.data?.id || sceneResponse.data?.scene?.id || sceneResponse.data?.data?.id;
                
                if (!sceneId) {
                    console.error('Scene response structure:', {
                        fullResponse: sceneResponse,
                        data: sceneResponse.data,
                        status: sceneResponse.status,
                        headers: sceneResponse.headers,
                    });
                    throw new Error(`Invalid response from server - no scene ID found. Response: ${JSON.stringify(sceneResponse.data)}`);
                }

                createdSceneIds.push(sceneId);
            } catch (sceneError) {
                console.error(`Error creating scene ${i + 1}:`, sceneError);
                console.error(`Error response:`, sceneError.response);
                console.error(`Error response data:`, sceneError.response?.data);
                console.error(`Error response status:`, sceneError.response?.status);
                
                // Extract detailed error message
                let errorMessage = `Failed to create scene ${i + 1}`;
                if (sceneError.response) {
                    const data = sceneError.response.data;
                    if (data.message) {
                        errorMessage += `: ${data.message}`;
                    } else if (data.errors) {
                        // Laravel validation errors
                        const errorMessages = Object.values(data.errors).flat();
                        errorMessage += `: ${errorMessages.join(', ')}`;
                    } else if (data.error) {
                        errorMessage += `: ${data.error}`;
                    } else {
                        // If no specific message, show the full response
                        errorMessage += `: ${JSON.stringify(data)}`;
                    }
                } else if (sceneError.message) {
                    errorMessage += `: ${sceneError.message}`;
                }
                
                throw new Error(errorMessage);
            }
        }

        if (createdSceneIds.length === 0) {
            throw new Error('No scenes were created successfully');
        }

        // Create options with correct next_scene_id references
        for (let i = 0; i < validScenes.length; i++) {
            const scene = validScenes[i];
            const sceneId = createdSceneIds[i];
            
            if (!sceneId) continue; // Skip if scene wasn't created
            
            if (scene.options && scene.options.length > 0) {
                for (let optIndex = 0; optIndex < scene.options.length; optIndex++) {
                    const option = scene.options[optIndex];
                    if (!option.option_text || option.option_text.trim() === '') continue;
                    
                    // Find which valid scene the next_scene_index points to
                    let nextSceneId = null;
                    if (option.next_scene_index !== null && option.next_scene_index !== undefined) {
                        // Find the original scene index in the full scenes array
                        const originalScene = scenes.value[option.next_scene_index];
                        // Find its index in validScenes
                        const validIndex = validScenes.findIndex(s => s === originalScene);
                        if (validIndex >= 0 && validIndex < createdSceneIds.length) {
                            nextSceneId = createdSceneIds[validIndex];
                        }
                    }
                    
                    try {
                        // Get fresh CSRF token
                        const csrfToken = document.head.querySelector('meta[name="csrf-token"]')?.content;
                        
                        await axios.post(`/api/scenes/${sceneId}/options`, {
                            option_text: option.option_text.trim(),
                            next_scene_id: nextSceneId,
                            order: optIndex + 1, // Use index + 1 as order
                            requires_tokens: option.requires_tokens || false,
                            _token: csrfToken,
                        }, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken || '',
                            },
                            withCredentials: true,
                        });
                    } catch (optionError) {
                        console.error(`Error creating option for scene ${i + 1}:`, optionError);
                        // Continue with other options even if one fails
                    }
                }
            }
        }

        // Redirect to profile page after successful creation
        if (user?.username) {
            router.visit(route('profile.show', user.username));
        } else if (user?.id) {
            router.visit(route('profile.show', user.id));
        } else {
            router.visit(route('home'));
        }
    } catch (error) {
        console.error('Error creating post:', error);
        const errorMessage = error.response?.data?.message || error.message || 'Unknown error occurred';
        alert(`Failed to create post: ${errorMessage}`);
    } finally {
        creating.value = false;
    }
};
</script>
