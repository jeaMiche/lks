<!-- resources/js/Pages/Dashboard.vue -->
<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps({
    userRole: String,
    stats: Object,
})

const user = computed(() => usePage().props.auth.user)

const formatRp = (val) =>
    new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(val)
</script>

<template>
    <AppLayout>
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-800">
                Selamat Datang, {{ user.name }} 👋
            </h1>
            <p class="text-gray-500 text-sm mt-1 capitalize">
                Role: <span class="font-semibold text-blue-700">{{ userRole }}</span>
            </p>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <!-- Total Pinjaman -->
            <div class="bg-white rounded-xl shadow p-6 border-l-4 border-blue-500">
                <p class="text-sm text-gray-500">Total Pengajuan</p>
                <p class="text-3xl font-bold text-gray-800 mt-1">{{ stats.total }}</p>
            </div>

            <!-- Pending -->
            <div class="bg-white rounded-xl shadow p-6 border-l-4 border-yellow-500">
                <p class="text-sm text-gray-500">Menunggu Review</p>
                <p class="text-3xl font-bold text-yellow-600 mt-1">{{ stats.pending }}</p>
            </div>

            <!-- Approved -->
            <div class="bg-white rounded-xl shadow p-6 border-l-4 border-green-500">
                <p class="text-sm text-gray-500">Disetujui</p>
                <p class="text-3xl font-bold text-green-600 mt-1">{{ stats.approved }}</p>
            </div>
        </div>

        <!-- Info Card berdasarkan Role -->
        <!-- Borrower -->
        <div v-if="userRole === 'borrower'"
             class="bg-blue-50 border border-blue-200 rounded-xl p-6">
            <h2 class="font-semibold text-blue-800 mb-2">Informasi Akun Anda</h2>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-gray-500">Nama Usaha</p>
                    <p class="font-semibold">{{ user.business_name ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Omzet Bulanan</p>
                    <p class="font-semibold text-green-700">
                        {{ formatRp(user.monthly_revenue ?? 0) }}
                    </p>
                </div>
                <div>
                    <p class="text-gray-500">Maks. Pinjaman (3x Omzet)</p>
                    <p class="font-semibold text-blue-700">
                        {{ formatRp((user.monthly_revenue ?? 0) * 3) }}
                    </p>
                </div>
            </div>
            <a :href="route('loans.create')"
               class="inline-block mt-4 bg-blue-700 text-white px-6 py-2 rounded-lg
                      hover:bg-blue-800 transition text-sm font-medium">
                + Ajukan Pinjaman Baru
            </a>
        </div>

        <!-- Analyst -->
        <div v-if="userRole === 'analyst'"
             class="bg-yellow-50 border border-yellow-200 rounded-xl p-6">
            <h2 class="font-semibold text-yellow-800 mb-2">Tugas Anda</h2>
            <p class="text-sm text-gray-600">
                Terdapat <strong>{{ stats.pending }}</strong> pengajuan menunggu review Anda.
            </p>
            <a :href="route('loans.index')"
               class="inline-block mt-4 bg-yellow-600 text-white px-6 py-2 rounded-lg
                      hover:bg-yellow-700 transition text-sm font-medium">
                Lihat Daftar Pengajuan
            </a>
        </div>

        <!-- Admin -->
        <div v-if="userRole === 'admin'"
             class="bg-green-50 border border-green-200 rounded-xl p-6">
            <h2 class="font-semibold text-green-800 mb-2">Panel Admin</h2>
            <p class="text-sm text-gray-600">
                Kelola seluruh pengajuan pinjaman dan proses persetujuan akhir.
            </p>
            <a :href="route('loans.index')"
               class="inline-block mt-4 bg-green-700 text-white px-6 py-2 rounded-lg
                      hover:bg-green-800 transition text-sm font-medium">
                Kelola Semua Pinjaman
            </a>
        </div>

    </AppLayout>
</template>