<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { router, Link } from '@inertiajs/vue3'
import { ref } from 'vue'

const props = defineProps({ loans: Object, userRole: String, canReview: Boolean, canApprove: Boolean, canApplyLoan: Boolean })
const processing = ref(null)

const rp = (v) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(v ?? 0)

const statusColor = { pending: 'bg-yellow-100 text-yellow-700', reviewed: 'bg-blue-100 text-blue-700', approved: 'bg-green-100 text-green-700', rejected: 'bg-red-100 text-red-700', disbursed: 'bg-purple-100 text-purple-700', lunas: 'bg-gray-200 text-gray-600' }
const statusLabel = { pending: 'Pending', reviewed: 'Direview', approved: 'Disetujui', rejected: 'Ditolak', disbursed: 'Dicairkan', lunas: 'Lunas' }

const action = (url, id) => { processing.value = id; router.patch(url, {}, { onFinish: () => processing.value = null }) }
const confirmDelete = (loan) => {
    if (!confirm(`Hapus pinjaman ${loan.user?.name}? Data bisa dipulihkan.`)) return
    processing.value = loan.id
    router.delete(route('loans.destroy', loan.id), { onFinish: () => processing.value = null })
}
const ip = (id) => processing.value === id
const btn = 'text-white text-xs px-3 py-1 rounded transition disabled:opacity-50'
</script>

<template>
    <AppLayout>
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Daftar Pinjaman</h1>
            <a v-if="userRole === 'borrower' && canApplyLoan" :href="route('loans.create')" class="bg-blue-700 text-white px-4 py-2 rounded-lg hover:bg-blue-800 transition text-sm font-medium">+ Ajukan Pinjaman</a>
        </div>

        <div class="bg-white rounded-xl shadow overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                    <tr>
                        <th class="px-6 py-3 text-left">Pemohon</th>
                        <th class="px-6 py-3 text-left">Jumlah</th>
                        <th class="px-6 py-3 text-left">Cicilan/Bulan</th>
                        <th class="px-6 py-3 text-left">Tenor</th>
                        <th class="px-6 py-3 text-left">Status</th>
                        <th class="px-6 py-3 text-left">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="loan in loans.data" :key="loan.id" class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 font-medium">{{ loan.user?.name }}</td>
                        <td class="px-6 py-4">{{ rp(loan.amount) }}</td>
                        <td class="px-6 py-4 font-semibold text-green-700">{{ rp(loan.monthly_installment) }}</td>
                        <td class="px-6 py-4">{{ loan.tenor_months }} bln</td>
                        <td class="px-6 py-4">
                            <span :class="['capitalize text-xs font-semibold px-2 py-1 rounded-full', statusColor[loan.status] ?? 'bg-gray-100 text-gray-600']">{{ statusLabel[loan.status] ?? loan.status }}</span>
                        </td>
                        <td class="px-6 py-4 space-x-2">
                            <Link :href="route('loans.show', loan.id)" class="bg-gray-500 hover:bg-gray-600 text-white text-xs px-3 py-1 rounded transition">Detail</Link>

                            <button v-if="(canApprove && ['pending','rejected'].includes(loan.status)) || (userRole === 'borrower' && loan.status === 'pending')" :disabled="ip(loan.id)" @click="confirmDelete(loan)" :class="btn + ' bg-red-700 hover:bg-red-800'">{{ ip(loan.id) ? '...' : 'Hapus' }}</button>
                            <button v-if="canReview && loan.status === 'pending'" :disabled="ip(loan.id)" @click="action(route('loans.review', loan.id), loan.id)" :class="btn + ' bg-blue-500 hover:bg-blue-600'">{{ ip(loan.id) ? '...' : 'Review' }}</button>
                            <button v-if="canApprove && loan.status === 'reviewed'" :disabled="ip(loan.id)" @click="action(route('loans.approve', loan.id), loan.id)" :class="btn + ' bg-green-500 hover:bg-green-600'">{{ ip(loan.id) ? '...' : 'Approve' }}</button>
                            <button v-if="canApprove && loan.status === 'approved'" :disabled="ip(loan.id)" @click="action(route('loans.disburse', loan.id), loan.id)" :class="btn + ' bg-indigo-500 hover:bg-indigo-600'">{{ ip(loan.id) ? '...' : 'Cairkan' }}</button>
                            <button v-if="canApprove && ['pending','reviewed'].includes(loan.status)" :disabled="ip(loan.id)" @click="action(route('loans.reject', loan.id), loan.id)" :class="btn + ' bg-red-500 hover:bg-red-600'">{{ ip(loan.id) ? '...' : 'Tolak' }}</button>

                            <a v-if="userRole === 'borrower' && loan.status === 'disbursed'" :href="route('loans.payments.create', loan.id)" class="bg-purple-600 text-white text-xs px-3 py-1 rounded hover:bg-purple-700 transition">Bayar Cicilan</a>
                            <span v-if="loan.status === 'lunas'" class="text-green-600 text-xs font-semibold">✅ Lunas</span>
                        </td>
                    </tr>
                    <tr v-if="!loans.data.length">
                        <td colspan="6" class="px-6 py-10 text-center text-gray-400">Belum ada data pinjaman.</td>
                    </tr>
                </tbody>
            </table>

            <div class="px-6 py-4 border-t flex gap-2">
                <a v-for="link in loans.links" :key="link.label" :href="link.url" v-html="link.label" :class="['px-3 py-1 rounded text-sm border transition', link.active ? 'bg-blue-700 text-white border-blue-700' : 'text-gray-600 hover:bg-gray-100', !link.url ? 'opacity-40 cursor-not-allowed pointer-events-none' : '']" />
            </div>
        </div>
    </AppLayout>
</template>