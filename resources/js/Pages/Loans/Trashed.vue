<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { router } from '@inertiajs/vue3'
import { ref } from 'vue'

const props = defineProps({ loans: Object, userRole: String })
const processing = ref(null)

const rp = (v) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(v ?? 0)
const tgl = (v) => v ? new Date(v).toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' }) : '-'
const restore = (id) => { processing.value = id; router.patch(route('loans.restore', id), {}, { onFinish: () => processing.value = null }) }
</script>

<template>
    <AppLayout>
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Pinjaman Terhapus</h1>
            <a :href="route('loans.index')" class="text-sm text-blue-600 hover:underline">← Kembali ke Daftar</a>
        </div>

        <div class="bg-white rounded-xl shadow overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                    <tr>
                        <th class="px-6 py-3 text-left">Pemohon</th>
                        <th class="px-6 py-3 text-left">Jumlah</th>
                        <th class="px-6 py-3 text-left">Status</th>
                        <th class="px-6 py-3 text-left">Dihapus Pada</th>
                        <th class="px-6 py-3 text-left">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="loan in loans.data" :key="loan.id" class="hover:bg-gray-50 transition bg-red-50">
                        <td class="px-6 py-4 font-medium">{{ loan.user?.name }}</td>
                        <td class="px-6 py-4">{{ rp(loan.amount) }}</td>
                        <td class="px-6 py-4 capitalize">{{ loan.status }}</td>
                        <td class="px-6 py-4 text-red-500">{{ tgl(loan.deleted_at) }}</td>
                        <td class="px-6 py-4">
                            <button @click="restore(loan.id)" :disabled="processing === loan.id" class="bg-green-500 text-white text-xs px-3 py-1 rounded hover:bg-green-600 transition disabled:opacity-50">
                                {{ processing === loan.id ? '...' : '↩ Pulihkan' }}
                            </button>
                        </td>
                    </tr>
                    <tr v-if="!loans.data.length">
                        <td colspan="5" class="px-6 py-10 text-center text-gray-400">Tidak ada data yang dihapus.</td>
                    </tr>
                </tbody>
            </table>

            <div class="px-6 py-4 border-t flex gap-2">
                <a v-for="link in loans.links" :key="link.label" :href="link.url" v-html="link.label"
                   :class="['px-3 py-1 rounded text-sm border transition', link.active ? 'bg-blue-700 text-white border-blue-700' : 'text-gray-600 hover:bg-gray-100', !link.url ? 'opacity-40 cursor-not-allowed pointer-events-none' : '']" />
            </div>
        </div>
    </AppLayout>
</template>