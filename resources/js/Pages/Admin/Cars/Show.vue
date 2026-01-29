<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, ref } from 'vue'; 

const props = defineProps({
    car: {
        type: Object,
        required: true,
    },
});
console.log(props.car)
const carState = ref(JSON.parse(JSON.stringify(props.car)));
const reviewNotes = ref('');
const isProcessing = ref(false);
const errorMessage = ref('');

const statusClasses = (status) => {
    switch (status) {
        case 'active':
            return 'bg-emerald-100 text-emerald-800';
        case 'pending_review':
            return 'bg-amber-100 text-amber-800';
        case 'pending':
            return 'bg-amber-100 text-amber-800';
        case 'inactive':
            return 'bg-gray-100 text-gray-600';
        case 'suspended':
            return 'bg-rose-100 text-rose-800';
        default:
            return 'bg-gray-100 text-gray-600';
    }
};

const approvalClasses = (status) => {
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

const carTitle = computed(() => {
    if (carState.value.title) {
        return carState.value.title;
    }

    return [carState.value.make, carState.value.model, carState.value.year]
        .filter(Boolean)
        .join(' ');
});

const photoUrl = (photo) => {
    if (!photo?.path) {
        return '';
    }

    if (photo.disk === 'public') {
        console.log(photo.path)
        return `/storage/${photo.path}`;
    }

    
    return photo.path;
};

const docUrl = (doc) => {
    if (!doc?.file_path) {
        return '';
    }

    if (doc.disk === 'public') {
        return `/storage/${doc.file_path}`;
    }

    return doc.file_path;
};

const reviewCar = async (status) => {
    if (isProcessing.value) {
        return;
    }

    isProcessing.value = true;
    errorMessage.value = '';

    try {
        const response = await axios.post(route('admin.cars.review', carState.value.id), {
            status,
            notes: reviewNotes.value || null,
        });

        const updated = response.data?.data;
        if (updated) {
            carState.value = updated;
        }

        reviewNotes.value = '';
    } catch (error) {
        errorMessage.value =
            error.response?.data?.message || 'Unable to update car approval.';
    } finally {
        isProcessing.value = false;
    }
};
</script>

<template>
    <Head title="Car Review" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-semibold leading-tight text-gray-800">
                        Car Review
                    </h2>
                    <p class="text-sm text-gray-500">
                        Review owner uploads and approve or reject the listing.
                    </p>
                </div>
                <Link
                    class="text-xs font-semibold uppercase tracking-widest text-gray-900 hover:text-gray-600"
                    :href="route('admin.cars.index')"
                >
                    Back to cars
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
                                        {{ carTitle }}
                                    </h3>
                                    <p class="text-sm text-gray-500">
                                        Car ID #{{ carState.id }}
                                    </p>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    <span
                                        class="inline-flex rounded-full px-3 py-1 text-xs font-semibold capitalize"
                                        :class="statusClasses(carState.status)"
                                    >
                                        {{ carState.status?.replace('_', ' ') }}
                                    </span>
                                    <span
                                        class="inline-flex rounded-full px-3 py-1 text-xs font-semibold capitalize"
                                        :class="approvalClasses(carState.approval_status)"
                                    >
                                        {{ carState.approval_status }}
                                    </span>
                                </div>
                            </div>

                            <div class="mt-6 grid gap-4 sm:grid-cols-2">
                                <div>
                                    <div class="text-xs font-semibold uppercase tracking-widest text-gray-500">
                                        Owner
                                    </div>
                                    <div class="mt-2 text-sm text-gray-700">
                                        {{ carState.owner?.name || 'Unknown' }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        {{ carState.owner?.email || carState.owner?.phone || 'No contact' }}
                                    </div>
                                </div>
                                <div>
                                    <div class="text-xs font-semibold uppercase tracking-widest text-gray-500">
                                        License plate
                                    </div>
                                    <div class="mt-2 text-sm text-gray-700">
                                        {{ carState.license_plate }}
                                    </div>
                                </div>
                                <div>
                                    <div class="text-xs font-semibold uppercase tracking-widest text-gray-500">
                                        Daily rate
                                    </div>
                                    <div class="mt-2 text-sm text-gray-700">
                                        {{ carState.daily_rate }} {{ carState.currency || '' }}
                                    </div>
                                </div>
                                <div>
                                    <div class="text-xs font-semibold uppercase tracking-widest text-gray-500">
                                        Deposit
                                    </div>
                                    <div class="mt-2 text-sm text-gray-700">
                                        {{ carState.deposit_amount }}
                                    </div>
                                </div>
                                <div>
                                    <div class="text-xs font-semibold uppercase tracking-widest text-gray-500">
                                        Pickup
                                    </div>
                                    <div class="mt-2 text-sm text-gray-700">
                                        {{ carState.pickup_latitude || 'n/a' }}, {{ carState.pickup_longitude || 'n/a' }}
                                    </div>
                                </div>
                                <div>
                                    <div class="text-xs font-semibold uppercase tracking-widest text-gray-500">
                                        Created
                                    </div>
                                    <div class="mt-2 text-sm text-gray-700">
                                        {{ carState.created_at }}
                                    </div>
                                </div>
                            </div>

                            <div v-if="carState.description" class="mt-6 border-t border-gray-100 pt-4 text-sm text-gray-600">
                                {{ carState.description }}
                            </div>
                        </div>

                        <div class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-100">
                            <h3 class="text-lg font-semibold text-gray-900">Photos</h3>
                            <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                                <div
                                    v-for="photo in carState.photos || []"
                                    :key="photo.id"
                                    class="overflow-hidden rounded-lg border border-gray-200"
                                >
                                    <img
                                        v-if="photoUrl(photo)"
                                        :src="photoUrl(photo)"
                                        class="h-40 w-full object-cover"
                                        alt="Car photo"
                                    />
                                    <div v-else class="flex h-40 items-center justify-center text-xs text-gray-400">
                                        No image
                                    </div>
                                </div>
                                <div
                                    v-if="!carState.photos || carState.photos.length === 0"
                                    class="col-span-full rounded-lg border border-dashed border-gray-200 p-6 text-center text-sm text-gray-500"
                                >
                                    No photos uploaded.
                                </div>
                            </div>
                        </div>

                        <div class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-100">
                            <h3 class="text-lg font-semibold text-gray-900">Documents</h3>
                            <div class="mt-4 flex flex-wrap gap-2">
                                <a
                                    v-for="doc in carState.documents || []"
                                    :key="doc.id"
                                    class="rounded-full border border-gray-200 px-3 py-1 text-xs font-semibold text-gray-700 hover:border-gray-400"
                                    :href="docUrl(doc)"
                                    target="_blank"
                                    rel="noreferrer"
                                >
                                    {{ doc.doc_type || 'document' }}
                                </a>
                                <span
                                    v-if="!carState.documents || carState.documents.length === 0"
                                    class="text-xs text-gray-400"
                                >
                                    No documents.
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <div class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-100">
                            <h3 class="text-lg font-semibold text-gray-900">
                                Review approval
                            </h3>

                            <p class="mt-2 text-sm text-gray-500">
                                Approve or reject this car upload.
                            </p>

                            <div v-if="carState.approval_status === 'pending'" class="mt-6 space-y-4">
                                <div>
                                    <div class="text-xs font-semibold uppercase tracking-widest text-gray-500">
                                        Notes
                                    </div>
                                    <textarea
                                        v-model="reviewNotes"
                                        rows="4"
                                        class="mt-2 w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-500 focus:ring-gray-500"
                                        placeholder="Optional review notes"
                                    />
                                </div>

                                <div class="flex flex-wrap gap-3">
                                    <button
                                        type="button"
                                        class="inline-flex items-center rounded-md bg-emerald-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-emerald-500 disabled:opacity-50"
                                        :disabled="isProcessing"
                                        @click="reviewCar('approved')"
                                    >
                                        Approve
                                    </button>
                                    <button
                                        type="button"
                                        class="inline-flex items-center rounded-md bg-rose-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-rose-500 disabled:opacity-50"
                                        :disabled="isProcessing"
                                        @click="reviewCar('rejected')"
                                    >
                                        Reject
                                    </button>
                                </div>

                                <p v-if="errorMessage" class="text-sm text-rose-600">
                                    {{ errorMessage }}
                                </p>
                            </div>

                            <div v-else class="mt-6 rounded-lg border border-dashed border-gray-200 p-4 text-sm text-gray-500">
                                This car has already been reviewed.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
