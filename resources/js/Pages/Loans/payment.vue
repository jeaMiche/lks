<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { useForm } from '@inertiajs/vue3'
import { computed, ref } from 'vue'

const props = defineProps({ loan: Object, schedule: Array, payments: Array })
const activeTab = ref('schedule')

const rp = (v) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(v ?? 0)
const tgl = (v) => v ? new Date(v).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) : '-'

const paidPercent = computed(() => {
    if (!props.loan) return 0
    return Math.min(100, Math.round(((props.loan.amount - props.loan.outstanding_balance) / props.loan.amount) * 100))
})
const nextInstallment = computed(() => props.schedule?.find(s => s.status === 'pending') ?? null)

const form = useForm({ amount: '', method: 'transfer', note: '' })
const submitPayment = () => form.post(route('loans.pay', props.loan.id), { preserveScroll: true, onSuccess: () => form.reset() })

const methodLabel = { transfer: 'Transfer Bank', cash: 'Tunai', qris: 'QRIS' }
const statusStyle = { paid: 'bg-emerald-900/40 text-emerald-400 border border-emerald-700/40', pending: 'bg-yellow-900/40 text-yellow-400 border border-yellow-700/40', overdue: 'bg-red-900/40 text-red-400 border border-red-700/40' }
const statusLabel = { paid: 'Lunas', pending: 'Belum Bayar', overdue: 'Jatuh Tempo' }
</script>

<template>
    <AppLayout>
        <div class="max-w-4xl mx-auto space-y-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Pembayaran Cicilan</h1>
                <p class="text-sm text-gray-500 mt-1">Kelola cicilan pinjaman aktif Anda</p>
            </div>

            <div v-if="!loan" class="bg-white rounded-2xl shadow border border-gray-100 p-12 text-center">
                <p class="text-gray-500 font-medium">Tidak ada pinjaman aktif saat ini.</p>
                <a :href="route('loans.create')" class="inline-block mt-4 bg-blue-600 text-white text-sm font-semibold px-6 py-2.5 rounded-lg hover:bg-blue-700 transition">Ajukan Pinjaman</a>
            </div>

            <template v-if="loan">
                <div class="bg-gradient-to-br from-blue-700 to-blue-900 rounded-2xl p-6 text-white shadow-lg">
                    <div class="flex flex-wrap gap-6 justify-between mb-6">
                        <div>
                            <p class="text-blue-200 text-xs uppercase tracking-wider font-medium mb-1">Total Pinjaman</p>
                            <p class="text-3xl font-bold">{{ rp(loan.amount) }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-blue-200 text-xs uppercase tracking-wider font-medium mb-1">Sisa Kewajiban</p>
                            <p class="text-3xl font-bold text-yellow-300">{{ rp(loan.outstanding_balance) }}</p>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between text-xs text-blue-200 mb-1.5">
                            <span>Progres Pelunasan</span><span>{{ paidPercent }}%</span>
                        </div>
                        <div class="h-2.5 bg-blue-950/50 rounded-full overflow-hidden">
                            <div class="h-full bg-gradient-to-r from-yellow-400 to-yellow-300 rounded-full transition-all duration-700" :style="{ width: paidPercent + '%' }" />
                        </div>
                    </div>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6 pt-6 border-t border-blue-600">
                        <div><p class="text-blue-300 text-xs mb-0.5">Tenor</p><p class="font-semibold text-sm">{{ loan.tenor }} bulan</p></div>
                        <div><p class="text-blue-300 text-xs mb-0.5">Bunga</p><p class="font-semibold text-sm">{{ loan.interest_rate }}% / bln</p></div>
                        <div><p class="text-blue-300 text-xs mb-0.5">Tgl Mulai</p><p class="font-semibold text-sm">{{ tgl(loan.start_date) }}</p></div>
                        <div><p class="text-blue-300 text-xs mb-0.5">Status</p><span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 capitalize">{{ loan.status }}</span></div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-white rounded-2xl shadow border border-gray-100 p-6">
                        <h2 class="font-semibold text-gray-700 mb-4">Cicilan Berikutnya</h2>
                        <template v-if="nextInstallment">
                            <p class="text-3xl font-bold text-gray-800 mb-1">{{ rp(nextInstallment.amount) }}</p>
                            <p class="text-sm text-gray-500">Jatuh tempo: <span class="font-semibold text-red-500">{{ tgl(nextInstallment.due_date) }}</span></p>
                        </template>
                        <p v-else class="text-sm text-emerald-600 font-medium">🎉 Semua cicilan sudah lunas!</p>
                    </div>

                    <div class="bg-white rounded-2xl shadow border border-gray-100 p-6">
                        <h2 class="font-semibold text-gray-700 mb-4">Bayar Cicilan</h2>
                        <form @submit.prevent="submitPayment" class="space-y-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Nominal (Rp)</label>
                                <input v-model="form.amount" type="number" placeholder="Masukkan nominal..." class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-400 transition" required />
                                <p v-if="form.errors.amount" class="text-red-500 text-xs mt-1">{{ form.errors.amount }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Metode Pembayaran</label>
                                <div class="grid grid-cols-3 gap-2">
                                    <button v-for="(label, val) in methodLabel" :key="val" type="button" @click="form.method = val" class="py-2 rounded-xl text-xs font-semibold border transition" :class="form.method === val ? 'bg-blue-600 text-white border-blue-600' : 'bg-gray-50 text-gray-600 border-gray-200 hover:border-blue-300'">{{ label }}</button>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Catatan (opsional)</label>
                                <input v-model="form.note" type="text" placeholder="Contoh: Transfer BCA atas nama..." class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-400 transition" />
                            </div>
                            <button type="submit" :disabled="form.processing" class="w-full bg-blue-600 text-white font-semibold py-2.5 rounded-xl hover:bg-blue-700 disabled:opacity-50 transition text-sm mt-1">
                                {{ form.processing ? 'Memproses...' : 'Bayar Sekarang' }}
                            </button>
                        </form>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow border border-gray-100 overflow-hidden">
                    <div class="flex border-b border-gray-100">
                        <button v-for="tab in ['schedule','history']" :key="tab" @click="activeTab = tab" class="flex-1 py-4 text-sm font-semibold transition border-b-2" :class="activeTab === tab ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-400 hover:text-gray-600'">
                            {{ tab === 'schedule' ? 'Jadwal Cicilan' : 'Riwayat Pembayaran' }}
                        </button>
                    </div>

                    <div v-if="activeTab === 'schedule'" class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                                <tr>
                                    <th class="px-6 py-3 text-left">Ke-</th>
                                    <th class="px-6 py-3 text-left">Jatuh Tempo</th>
                                    <th class="px-6 py-3 text-right">Nominal</th>
                                    <th class="px-6 py-3 text-center">Status</th>
                                    <th class="px-6 py-3 text-left">Dibayar</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                <tr v-for="(item, idx) in schedule" :key="idx" class="hover:bg-gray-50/60 transition-colors">
                                    <td class="px-6 py-4 font-semibold text-gray-700">{{ idx + 1 }}</td>
                                    <td class="px-6 py-4 text-gray-600">{{ tgl(item.due_date) }}</td>
                                    <td class="px-6 py-4 text-right font-semibold text-gray-800">{{ rp(item.amount) }}</td>
                                    <td class="px-6 py-4 text-center"><span class="text-xs font-semibold px-2.5 py-1 rounded-full" :class="statusStyle[item.status] ?? statusStyle.pending">{{ statusLabel[item.status] ?? item.status }}</span></td>
                                    <td class="px-6 py-4 text-gray-500 text-xs">{{ tgl(item.paid_at) }}</td>
                                </tr>
                                <tr v-if="!schedule?.length"><td colspan="5" class="px-6 py-10 text-center text-gray-400 text-sm">Jadwal cicilan belum tersedia.</td></tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-if="activeTab === 'history'" class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                                <tr>
                                    <th class="px-6 py-3 text-left">Tanggal</th>
                                    <th class="px-6 py-3 text-right">Nominal</th>
                                    <th class="px-6 py-3 text-center">Metode</th>
                                    <th class="px-6 py-3 text-left">Catatan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                <tr v-for="p in payments" :key="p.id" class="hover:bg-gray-50/60 transition-colors">
                                    <td class="px-6 py-4 text-gray-600">{{ tgl(p.payment_date) }}</td>
                                    <td class="px-6 py-4 text-right font-semibold text-emerald-600">{{ rp(p.amount) }}</td>
                                    <td class="px-6 py-4 text-center"><span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-blue-50 text-blue-600 border border-blue-100">{{ methodLabel[p.method] ?? p.method }}</span></td>
                                    <td class="px-6 py-4 text-gray-400 text-xs">{{ p.note ?? '-' }}</td>
                                </tr>
                                <tr v-if="!payments?.length"><td colspan="4" class="px-6 py-10 text-center text-gray-400 text-sm">Belum ada riwayat pembayaran.</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </template>
        </div>
    </AppLayout>
</template>