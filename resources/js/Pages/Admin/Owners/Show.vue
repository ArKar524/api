<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, ref } from 'vue';

const props = defineProps({
    owner: {
        type: Object,
        required: true,
    },
});

const ownerState = ref(JSON.parse(JSON.stringify(props.owner)));
const reviewNotes = ref('');
const isProcessing = ref(false);
const errorMessage = ref('');

const statusClasses = (status) => {
    switch (status) {
        case 'active':
            return 'bg-emerald-100 text-emerald-800';
        case 'pending':
            return 'bg-amber-100 text-amber-800';
        case 'suspended':
            return 'bg-rose-100 text-rose-800';
        default:
            return 'bg-gray-100 text-gray-600';
    }
};

const verificationClasses = (status) => {
    switch (status) {
        case 'approved':
            return 'bg-emerald-100 text-emerald-800';
        case 'pending':
            return 'bg-amber-100 text-amber-800';
        case 'rejected':
            return 'bg-rose-100 text-rose-800';
        default:
            return 'bg-gray-100 text-gray-600';
    }
};

const verifications = computed(() => {
    const items = ownerState.value.owner_verifications || [];
    return [...items].sort((a, b) => {
        const left = Date.parse(b.requested_at || b.created_at || '') || 0;
        const right = Date.parse(a.requested_at || a.created_at || '') || 0;
        return left - right;
    });
});

const pendingVerification = computed(
    () => verifications.value.find((item) => item.status === 'pending') || null,
);

const fileUrl = (file) => {
    if (!file?.file_path) {
        return '#';
    }

    if (file.disk === 'public') {
        return `/storage/${file.file_path}`;
    }

    return file.file_path;
};

const fileLabel = (file) => {
    const label = file?.meta?.label || file?.meta?.original || 'file';
    return String(label).replace(/_/g, ' ');
};

const reviewVerification = async (status) => {
    if (!pendingVerification.value) {
        return;
    }

    isProcessing.value = true;
    errorMessage.value = '';

    try {
        const response = await axios.post(
            route('admin.verifications.review', pendingVerification.value.id),
            {
                status,
                notes: reviewNotes.value || null,
            },
        );

        const updated = response.data?.data;
        if (updated && ownerState.value.owner_verifications) {
            ownerState.value.owner_verifications = ownerState.value.owner_verifications.map((item) =>
                item.id === updated.id ? { ...item, ...updated } : item,
            );
        }

        reviewNotes.value = '';
    } catch (error) {
        errorMessage.value =
            error.response?.data?.message || 'Unable to update verification.';
    } finally {
        isProcessing.value = false;
    }
};
</script>

<template>
    <Head title="Owner Details" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-semibold leading-tight text-gray-800">
                        Owner Details
                    </h2>
                    <p class="text-sm text-gray-500">
                        Review owner profile and KYC submissions.
                    </p>
                </div>
                <Link
                    class="text-xs font-semibold uppercase tracking-widest text-gray-900 hover:text-gray-600"
                    :href="route('admin.owners.index')"
                >
                    Back to owners
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
                <div class="grid gap-6 lg:grid-cols-3">
                    <div class="space-y-6 lg:col-span-2">
                        <div class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-100">
                            <div class="flex flex-wrap items-center justify-between gap-4">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">
                                        {{ ownerState.name }}
                                    </h3>
                                    <p class="text-sm text-gray-500">
                                        Owner ID #{{ ownerState.id }}
                                    </p>
                                </div>
                                <span
                                    class="inline-flex rounded-full px-3 py-1 text-xs font-semibold capitalize"
                                    :class="statusClasses(ownerState.status)"
                                >
                                    {{ ownerState.status }}
                                </span>
                            </div>

                            <div class="mt-6 grid gap-4 sm:grid-cols-2">
                                <div>
                                    <div class="text-xs font-semibold uppercase tracking-widest text-gray-500">
                                        Email
                                    </div>
                                    <div class="mt-2 text-sm text-gray-700">
                                        {{ ownerState.email }}
                                    </div>
                                </div>
                                <div>
                                    <div class="text-xs font-semibold uppercase tracking-widest text-gray-500">
                                        Phone
                                    </div>
                                    <div class="mt-2 text-sm text-gray-700">
                                        {{ ownerState.phone || 'No phone' }}
                                    </div>
                                </div>
                                <div>
                                    <div class="text-xs font-semibold uppercase tracking-widest text-gray-500">
                                        Created
                                    </div>
                                    <div class="mt-2 text-sm text-gray-700">
                                        {{ ownerState.created_at }}
                                    </div>
                                </div>
                                <div>
                                    <div class="text-xs font-semibold uppercase tracking-widest text-gray-500">
                                        Latest KYC
                                    </div>
                                    <div class="mt-2 text-sm text-gray-700">
                                        {{ verifications[0]?.status || 'none' }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-100">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-semibold text-gray-900">
                                    KYC history
                                </h3>
                                <div class="text-sm text-gray-500">
                                    {{ verifications.length }} submissions
                                </div>
                            </div>

                            <div class="mt-6 space-y-4">
                                <div
                                    v-for="verification in verifications"
                                    :key="verification.id"
                                    class="rounded-lg border border-gray-100 p-4"
                                >
                                    <div class="flex flex-wrap items-center justify-between gap-3">
                                        <div>
                                            <div class="text-sm font-semibold text-gray-900">
                                                Verification #{{ verification.id }}
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                Requested: {{ verification.requested_at || 'n/a' }}
                                            </div>
                                        </div>
                                        <span
                                            class="inline-flex rounded-full px-3 py-1 text-xs font-semibold capitalize"
                                            :class="verificationClasses(verification.status)"
                                        >
                                            {{ verification.status }}
                                        </span>
                                    </div>

                                    <div class="mt-3 text-sm text-gray-600">
                                        <div>Completed: {{ verification.completed_at || 'n/a' }}</div>
                                        <div v-if="verification.notes" class="mt-1">
                                            Notes: {{ verification.notes }}
                                        </div>
                                    </div>

                                    <div class="mt-4">
                                        <div class="text-xs font-semibold uppercase tracking-widest text-gray-500">
                                            Files
                                        </div>
                                        <div class="mt-2 flex flex-wrap gap-2">
                                            <a
                                                v-for="file in verification.files || []"
                                                :key="file.id"
                                                class="rounded-full border border-gray-200 px-3 py-1 text-xs font-semibold text-gray-700 hover:border-gray-400"
                                                :href="fileUrl(file)"
                                                target="_blank"
                                                rel="noreferrer"
                                            >
                                                {{ fileLabel(file) }}
                                            </a>
                                            <span
                                                v-if="!verification.files || verification.files.length === 0"
                                                class="text-xs text-gray-400"
                                            >
                                                No files
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div
                                    v-if="verifications.length === 0"
                                    class="rounded-lg border border-dashed border-gray-200 p-6 text-center text-sm text-gray-500"
                                >
                                    No KYC submissions yet.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <div class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-100">
                            <h3 class="text-lg font-semibold text-gray-900">
                                Review pending KYC
                            </h3>

                            <p class="mt-2 text-sm text-gray-500">
                                Approve or reject the latest pending owner submission.
                            </p>

                            <div v-if="pendingVerification" class="mt-6 space-y-4">
                                <div class="rounded-lg bg-gray-50 p-4 text-sm text-gray-700">
                                    <div class="font-semibold text-gray-900">
                                        Verification #{{ pendingVerification.id }}
                                    </div>
                                    <div class="mt-1 text-xs text-gray-500">
                                        Requested: {{ pendingVerification.requested_at || 'n/a' }}
                                    </div>
                                </div>

                                <div>
                                    <div class="text-xs font-semibold uppercase tracking-widest text-gray-500">
                                        Notes
                                    </div>
                                    <textarea
                                        v-model="reviewNotes"
                                        rows="4"
                                        class="mt-2 w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-500 focus:ring-gray-500"
                                        placeholder="Optional notes for this decision"
                                    />
                                </div>

                                <div class="flex flex-wrap gap-3">
                                    <button
                                        type="button"
                                        class="inline-flex items-center rounded-md bg-emerald-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-emerald-500 disabled:opacity-50"
                                        :disabled="isProcessing"
                                        @click="reviewVerification('approved')"
                                    >
                                        Approve
                                    </button>
                                    <button
                                        type="button"
                                        class="inline-flex items-center rounded-md bg-rose-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-rose-500 disabled:opacity-50"
                                        :disabled="isProcessing"
                                        @click="reviewVerification('rejected')"
                                    >
                                        Reject
                                    </button>
                                </div>

                                <p v-if="errorMessage" class="text-sm text-rose-600">
                                    {{ errorMessage }}
                                </p>
                            </div>

                            <div v-else class="mt-6 rounded-lg border border-dashed border-gray-200 p-4 text-sm text-gray-500">
                                No pending verification for this owner.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
