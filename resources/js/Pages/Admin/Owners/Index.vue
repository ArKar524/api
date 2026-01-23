<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    owners: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const form = ref({
    q: props.filters.q || '',
    status: props.filters.status || '',
    verification_status: props.filters.verification_status || '',
    sort: props.filters.sort || '',
    per_page: props.filters.per_page || '',
});

const owners = computed(() => props.owners?.data ?? []);
const links = computed(() => props.owners?.links ?? []);
const total = computed(() => props.owners?.total ?? 0);

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

const applyFilters = () => {
    router.get(
        route('admin.owners.index'),
        {
            q: form.value.q || undefined,
            sort: form.value.sort || undefined,
            per_page: form.value.per_page || undefined,
            filter: {
                status: form.value.status || undefined,
                verification_status: form.value.verification_status || undefined,
            },
        },
        {
            preserveState: true,
            replace: true,
        },
    );
};

const resetFilters = () => {
    form.value = {
        q: '',
        status: '',
        verification_status: '',
        sort: '',
        per_page: '',
    };
    applyFilters();
};
</script>

<template>
    <Head title="Owner KYC" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-4">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Owner KYC
                </h2>
                <div class="text-sm text-gray-500">
                    Total owners: {{ total }}
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
                <form
                    class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-100"
                    @submit.prevent="applyFilters"
                >
                    <div class="grid gap-4 md:grid-cols-4">
                        <div class="md:col-span-2">
                            <label class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                Search
                            </label>
                            <input
                                v-model="form.q"
                                type="search"
                                placeholder="Name, email, or phone"
                                class="mt-2 w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-500 focus:ring-gray-500"
                            />
                        </div>
                        <div>
                            <label class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                Owner status
                            </label>
                            <select
                                v-model="form.status"
                                class="mt-2 w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-500 focus:ring-gray-500"
                            >
                                <option value="">All</option>
                                <option value="active">Active</option>
                                <option value="pending">Pending</option>
                                <option value="suspended">Suspended</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                KYC status
                            </label>
                            <select
                                v-model="form.verification_status"
                                class="mt-2 w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-500 focus:ring-gray-500"
                            >
                                <option value="">All</option>
                                <option value="pending">Pending</option>
                                <option value="approved">Approved</option>
                                <option value="rejected">Rejected</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                Sort
                            </label>
                            <select
                                v-model="form.sort"
                                class="mt-2 w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-500 focus:ring-gray-500"
                            >
                                <option value="">Newest</option>
                                <option value="created_at">Oldest</option>
                                <option value="name">Name A-Z</option>
                                <option value="-name">Name Z-A</option>
                                <option value="email">Email A-Z</option>
                                <option value="-email">Email Z-A</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                Per page
                            </label>
                            <select
                                v-model="form.per_page"
                                class="mt-2 w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-500 focus:ring-gray-500"
                            >
                                <option value="">Default</option>
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-6 flex flex-wrap gap-3">
                        <button
                            type="submit"
                            class="inline-flex items-center rounded-md bg-gray-900 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-gray-800"
                        >
                            Apply
                        </button>
                        <button
                            type="button"
                            class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 transition hover:bg-gray-50"
                            @click="resetFilters"
                        >
                            Reset
                        </button>
                    </div>
                </form>

                <div class="overflow-hidden rounded-lg bg-white shadow-sm ring-1 ring-gray-100">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50 text-xs uppercase tracking-widest text-gray-500">
                                <tr>
                                    <th class="px-4 py-3 text-left font-semibold">Owner</th>
                                    <th class="px-4 py-3 text-left font-semibold">Contact</th>
                                    <th class="px-4 py-3 text-left font-semibold">Owner status</th>
                                    <th class="px-4 py-3 text-left font-semibold">KYC status</th>
                                    <th class="px-4 py-3 text-left font-semibold">Created</th>
                                    <th class="px-4 py-3 text-left font-semibold">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="owner in owners" :key="owner.id">
                                    <td class="px-4 py-4">
                                        <div class="font-semibold text-gray-900">
                                            {{ owner.name }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            #{{ owner.id }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 text-gray-600">
                                        <div>{{ owner.email }}</div>
                                        <div class="text-xs text-gray-500">
                                            {{ owner.phone || 'No phone' }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-4">
                                        <span
                                            class="inline-flex rounded-full px-3 py-1 text-xs font-semibold capitalize"
                                            :class="statusClasses(owner.status)"
                                        >
                                            {{ owner.status }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-4">
                                        <span
                                            class="inline-flex rounded-full px-3 py-1 text-xs font-semibold capitalize"
                                            :class="verificationClasses(owner.latest_owner_verification?.status)"
                                        >
                                            {{ owner.latest_owner_verification?.status || 'none' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-4 text-gray-600">
                                        {{ owner.created_at }}
                                    </td>
                                    <td class="px-4 py-4">
                                        <Link
                                            class="text-xs font-semibold uppercase tracking-widest text-gray-900 hover:text-gray-600"
                                            :href="route('admin.owners.show', owner.id)"
                                        >
                                            View
                                        </Link>
                                    </td>
                                </tr>
                                <tr v-if="owners.length === 0">
                                    <td class="px-4 py-6 text-center text-gray-500" colspan="6">
                                        No owners match the current filters.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="border-t border-gray-100 px-4 py-4">
                        <div class="flex flex-wrap items-center gap-2">
                            <template v-for="(link, index) in links" :key="index">
                                <Link
                                    v-if="link.url"
                                    :href="link.url"
                                    class="inline-flex min-w-[2.25rem] items-center justify-center rounded-md border px-3 py-2 text-xs font-semibold transition"
                                    :class="link.active
                                        ? 'border-gray-900 bg-gray-900 text-white'
                                        : 'border-gray-200 bg-white text-gray-600 hover:border-gray-400'"
                                    v-html="link.label"
                                />
                                <span
                                    v-else
                                    class="inline-flex min-w-[2.25rem] items-center justify-center rounded-md border border-gray-200 bg-white px-3 py-2 text-xs font-semibold text-gray-400"
                                    v-html="link.label"
                                />
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
