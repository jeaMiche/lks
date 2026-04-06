<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { Link } from '@inertiajs/vue3'

const props = defineProps({ loan: Object, userRole: String })

const rp = (v) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(v ?? 0)
const tgl = (v) => v ? new Date(v).toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' }) : '-'

const statusColor = { pending: 'bg-yellow-100 text-yellow-700', reviewed: 'bg-blue-100 text-blue-700', approved: 'bg-green-100 text-green-700', rejected: 'bg-red-100 text-red-700', disbursed: 'bg-purple-100 text-purple-700', lunas: 'bg-gray-200 text-gray-600' }
const statusLabel = { pending: 'Pending', reviewed: 'Direview', approved: 'Disetujui', rejected: 'Ditolak', disbursed: 'Dicairkan', lunas: 'Lunas' }

const paidCount = props.loan.payments?.length ?? 0
const remaining = props.loan.tenor_months - paidCount
</script>

<template>
    <AppLayout>
        <div class="max-w-3xl mx-auto space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Detail Pinjaman</h1>
                    <p class="text-sm text-gray-400 mt-1">ID: {{ loan.id }}</p>
                </div>
                <Link :href="route('loans.index')" class="text-sm text-blue-600 hover:underline">← Kembali</Link>
            </div>

            <div class="bg-white rounded-xl shadow p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-semibold text-gray-700">Informasi Pinjaman</h2>
                    <span :class="['text-xs font-semibold px-3 py-1 rounded-full capitalize', statusColor[loan.status] ?? 'bg-gray-100 text-gray-600']">
                        {{ statusLabel[loan.status] ?? loan.status }}
                    </span>
                </div>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div><p class="text-gray-400">Pemohon</p><p class="font-semibold">{{ loan.user?.name }}</p></div>
                    <div><p class="text-gray-400">Tujuan Pinjaman</p><p class="font-semibold">{{ loan.purpose }}</p></div>
                    <div><p class="text-gray-400">Jumlah Pinjaman</p><p class="font-semibold">{{ rp(loan.amount) }}</p></div>
                    <div><p class="text-gray-400">Cicilan / Bulan</p><p class="font-semibold text-green-700">{{ rp(loan.monthly_installment) }}</p></div>
                    <div><p class="text-gray-400">Tenor</p><p class="font-semibold">{{ loan.tenor_months }} bulan</p></div>
                    <div><p class="text-gray-400">Suku Bunga</p><p class="font-semibold">{{ loan.interest_rate }}% / tahun</p></div>
                    <div><p class="text-gray-400">Sisa Hutang</p><p class="font-semibold text-red-600">{{ rp(loan.outstanding_balance) }}</p></div>
                    <div><p class="text-gray-400">Tanggal Pengajuan</p><p class="font-semibold">{{ tgl(loan.created_at) }}</p></div>
                </div>
            </div>

            <div v-if="loan.status === 'disbursed'" class="bg-white rounded-xl shadow p-6">
                <h2 class="font-semibold text-gray-700 mb-4">Progress Pelunasan</h2>
                <div class="flex justify-between text-sm mb-2">
                    <span class="text-gray-500">Cicilan Terbayar</span>
                    <span class="font-semibold">{{ paidCount }} / {{ loan.tenor_months }}</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-3 mb-3">
                    <div class="bg-blue-600 h-3 rounded-full transition-all" :style="{ width: (paidCount / loan.tenor_months * 100) + '%' }" />
                </div>
                <div class="flex gap-6 text-sm">
                    <span class="text-green-600 font-semibold">✅ Terbayar: {{ paidCount }} cicilan</span>
                    <span class="text-red-500 font-semibold">⏳ Sisa: {{ remaining }} cicilan</span>
                </div>
                <div v-if="loan.status === 'lunas'" class="mt-4 bg-green-100 border border-green-300 text-green-700 rounded-lg p-4 text-center font-semibold">
                    🎉 Selamat! Pinjaman Anda sudah lunas.
                </div>
                <Link v-if="userRole === 'borrower'" :href="route('loans.payments.create', loan.id)" class="inline-block mt-4 bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700 transition text-sm font-medium">
                    💳 Bayar Cicilan Sekarang
                </Link>
            </div>

            <div class="bg-white rounded-xl shadow overflow-hidden">
                <div class="px-6 py-4 border-b"><h2 class="font-semibold text-gray-700">Riwayat Pembayaran</h2></div>
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                        <tr>
                            <th class="px-6 py-3 text-left">Tanggal</th>
                            <th class="px-6 py-3 text-left">Jumlah</th>
                            <th class="px-6 py-3 text-left">Metode</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="p in loan.payments" :key="p.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4">{{ tgl(p.payment_date) }}</td>
                            <td class="px-6 py-4 font-semibold text-green-700">{{ rp(p.amount) }}</td>
                            <td class="px-6 py-4 capitalize">{{ p.method ?? '-' }}</td>
                        </tr>
                        <tr v-if="!loan.payments?.length">
                            <td colspan="3" class="px-6 py-8 text-center text-gray-400">Belum ada riwayat pembayaran.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>