<script setup>
import { ref } from 'vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Link, useForm, usePage, router } from '@inertiajs/vue3';

const props = defineProps({
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
});

const user = usePage().props.auth.user;

const form = useForm({
    bio: props.profile?.bio || '',
    location: props.profile?.location || '',
    avatar: null,
    banner: null,
});

const avatarPreview = ref(props.profile?.avatar_url || null);
const bannerPreview = ref(props.profile?.banner_url || null);

const handleAvatarChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        // Check file size (5MB = 5 * 1024 * 1024 bytes)
        const maxSize = 5 * 1024 * 1024; // 5MB
        if (file.size > maxSize) {
            alert(`Profile picture is too large (${(file.size / 1024 / 1024).toFixed(2)}MB). Maximum size is 5MB. Please compress or resize your image.`);
            event.target.value = ''; // Clear the input
            return;
        }
        
        form.avatar = file;
        const reader = new FileReader();
        reader.onload = (e) => {
            avatarPreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

const handleBannerChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        // Check file size (10MB = 10 * 1024 * 1024 bytes)
        const maxSize = 10 * 1024 * 1024; // 10MB
        if (file.size > maxSize) {
            alert(`Banner image is too large (${(file.size / 1024 / 1024).toFixed(2)}MB). Maximum size is 10MB. Please compress or resize your image.`);
            event.target.value = ''; // Clear the input
            return;
        }
        
        form.banner = file;
        const reader = new FileReader();
        reader.onload = (e) => {
            bannerPreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

const submitForm = () => {
    // Ensure bio and location are always set (even if empty string)
    // This ensures they're included in the request
    form.bio = form.bio ?? '';
    form.location = form.location ?? '';
    
    // Only use forceFormData if there are files to upload
    // Otherwise, use regular form submission which properly includes text fields
    const hasFiles = form.avatar !== null || form.banner !== null;
    
    const options = {
        preserveScroll: true,
        ...(hasFiles ? { forceFormData: true } : {}),
    };
    
    // Use username if available, otherwise fall back to ID
    // The controller now handles both cases
    const routeParam = user.username || user.id;
    
    // Submit using the form's patch method
    form.patch(route('settings.update', routeParam), options);
};
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                Profile Information
            </h2>

            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Update your profile information.
            </p>
        </header>

        <form
            @submit.prevent="submitForm"
            class="mt-6 space-y-6"
        >
            <div>
                <InputLabel for="name" value="Name" />

                <input
                    id="name"
                    type="text"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm bg-gray-100 text-gray-500 cursor-not-allowed dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400"
                    :value="user.name"
                    disabled
                    autocomplete="name"
                />
            </div>

            <div>
                <InputLabel for="username" value="Username" />

                <input
                    id="username"
                    type="text"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm bg-gray-100 text-gray-500 cursor-not-allowed dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400"
                    :value="user.username || ''"
                    disabled
                    autocomplete="username"
                />

                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    Username cannot be changed.
                </p>
            </div>

            <div>
                <InputLabel for="email" value="Email" />

                <input
                    id="email"
                    type="email"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm bg-gray-100 text-gray-500 cursor-not-allowed dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400"
                    :value="user.email"
                    disabled
                    autocomplete="username"
                />
            </div>

            <!-- Bio -->
            <div>
                <InputLabel for="bio" value="Bio" />
                <textarea
                    id="bio"
                    v-model="form.bio"
                    rows="4"
                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                    placeholder="Tell us about yourself"
                ></textarea>
                <InputError class="mt-2" :message="form.errors.bio" />
            </div>

            <!-- Location -->
            <div>
                <InputLabel for="location" value="Location" />
                <TextInput
                    id="location"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.location"
                    placeholder="Your location"
                />
                <InputError class="mt-2" :message="form.errors.location" />
            </div>

            <!-- Avatar -->
            <div>
                <InputLabel for="avatar" value="Profile Picture" />
                <input
                    id="avatar"
                    type="file"
                    accept="image/jpeg,image/jpg,image/png,image/gif,image/webp"
                    @change="handleAvatarChange"
                    class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 dark:file:bg-gray-700 dark:file:text-gray-300"
                />
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    Maximum file size: 5MB. Supported formats: JPEG, PNG, GIF, WebP
                </p>
                <div v-if="avatarPreview" class="mt-4">
                    <img :src="avatarPreview" alt="Avatar preview" class="w-24 h-24 rounded-full object-cover" />
                </div>
                <InputError class="mt-2" :message="form.errors.avatar" />
            </div>

            <!-- Banner -->
            <div>
                <InputLabel for="banner" value="Banner Image" />
                <input
                    id="banner"
                    type="file"
                    accept="image/jpeg,image/jpg,image/png,image/gif,image/webp"
                    @change="handleBannerChange"
                    class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 dark:file:bg-gray-700 dark:file:text-gray-300"
                />
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    Maximum file size: 10MB. Supported formats: JPEG, PNG, GIF, WebP
                </p>
                <div v-if="bannerPreview" class="mt-4">
                    <img :src="bannerPreview" alt="Banner preview" class="w-full h-48 object-cover rounded" />
                </div>
                <InputError class="mt-2" :message="form.errors.banner" />
            </div>

            <div v-if="mustVerifyEmail && user.email_verified_at === null">
                <p class="mt-2 text-sm text-gray-800 dark:text-gray-200">
                    Your email address is unverified.
                    <Link
                        :href="route('verification.send')"
                        method="post"
                        as="button"
                        class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:text-gray-400 dark:hover:text-gray-100 dark:focus:ring-offset-gray-800"
                    >
                        Click here to re-send the verification email.
                    </Link>
                </p>

                <div
                    v-show="status === 'verification-link-sent'"
                    class="mt-2 text-sm font-medium text-green-600 dark:text-green-400"
                >
                    A new verification link has been sent to your email address.
                </div>
            </div>

            <div class="flex items-center gap-4">
                <PrimaryButton :disabled="form.processing">Save</PrimaryButton>

                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p
                        v-if="form.recentlySuccessful"
                        class="text-sm text-gray-600 dark:text-gray-400"
                    >
                        Saved.
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>
