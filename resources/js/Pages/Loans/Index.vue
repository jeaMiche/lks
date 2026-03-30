<!-- resources/js/Pages/Loans/Index.vue -->
<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { router } from '@inertiajs/vue3'
import { ref } from 'vue'

const props = defineProps({
  loans:      Object,  // paginated
  userRole:   String,
  canReview:  Boolean,
  canApprove: Boolean,
})

const processing = ref(null) // track loan id yang sedang diproses

const formatRp = (val) =>
  new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val)

const statusColor = {
  pending:   'bg-yellow-100 text-yellow-700',
  reviewed:  'bg-blue-100 text-blue-700',
  approved:  'bg-green-100 text-green-700',
  rejected:  'bg-red-100 text-red-700',
  disbursed: 'bg-purple-100 text-purple-700',
}

const action = (url, loanId) => {
  processing.value = loanId
  router.patch(url, {}, {
    onFinish: () => { processing.value = null }
  })
}
</script>

<template>
  <AppLayout>
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold text-gray-800">Daftar Pinjaman</h1>
      <a v-if="userRole === 'borrower'"
         :href="route('loans.create')"
         class="bg-blue-700 text-white px-4 py-2 rounded-lg hover:bg-blue-800 transition text-sm font-medium">
        + Ajukan Pinjaman
      </a>
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
          <tr v-for="loan in loans.data" :key="loan.id"
              class="hover:bg-gray-50 transition">
            <td class="px-6 py-4 font-medium">{{ loan.user?.name }}</td>
            <td class="px-6 py-4">{{ formatRp(loan.amount) }}</td>
            <td class="px-6 py-4 font-semibold text-green-700">
              {{ formatRp(loan.monthly_installment) }}
            </td>
            <td class="px-6 py-4">{{ loan.tenor_months }} bln</td>
            <td class="px-6 py-4">
              <span :class="['capitalize text-xs font-semibold px-2 py-1 rounded-full',
                             statusColor[loan.status]]">
                {{ loan.status }}
              </span>
            </td>
            <td class="px-6 py-4 space-x-2">

              <!-- Tombol Review — hanya Analyst & Admin -->
              <button
                v-if="canReview && loan.status === 'pending'"
                :disabled="processing === loan.id"
                @click="action(route('loans.review', loan.id), loan.id)"
                class="bg-blue-500 text-white text-xs px-3 py-1 rounded hover:bg-blue-600
                       disabled:opacity-50 transition"
              >
                {{ processing === loan.id ? '...' : 'Review' }}
              </button>

              <!-- Tombol Approve — hanya Admin -->
              <button
                v-if="canApprove && loan.status === 'reviewed'"
                :disabled="processing === loan.id"
                @click="action(route('loans.approve', loan.id), loan.id)"
                class="bg-green-500 text-white text-xs px-3 py-1 rounded hover:bg-green-600
                       disabled:opacity-50 transition"
              >
                {{ processing === loan.id ? '...' : 'Approve' }}
              </button>

              <!-- Tombol Reject — hanya Admin -->
              <button
                v-if="canApprove && ['pending','reviewed'].includes(loan.status)"
                :disabled="processing === loan.id"
                @click="action(route('loans.reject', loan.id), loan.id)"
                class="bg-red-500 text-white text-xs px-3 py-1 rounded hover:bg-red-600
                       disabled:opacity-50 transition"
              >
                Tolak
              </button>

            </td>
          </tr>
        </tbody>
      </table>

      <!-- Pagination -->
      <div class="px-6 py-4 border-t flex gap-2">
        <a v-for="link in loans.links" :key="link.label"
           :href="link.url"
           v-html="link.label"
           :class="['px-3 py-1 rounded text-sm border transition',
                    link.active
                      ? 'bg-blue-700 text-white border-blue-700'
                      : 'text-gray-600 hover:bg-gray-100',
                    !link.url ? 'opacity-40 cursor-not-allowed pointer-events-none' : '']"
        />
      </div>
    </div>
  </AppLayout>
</template>