<!-- resources/js/Pages/Loans/Create.vue -->
<script setup>
import { useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { computed } from 'vue'

const props = defineProps({
  maxLoan: Number,
  revenue: Number,
})

const form = useForm({
  amount:        '',
  tenor_months:  12,
  interest_rate: 12,
  purpose:       '',
})

// Preview cicilan real-time
const previewInstallment = computed(() => {
  if (!form.amount || !form.tenor_months || !form.interest_rate) return 0
  const r = form.interest_rate / 100 / 12
  const n = form.tenor_months
  const p = parseFloat(form.amount)
  if (r === 0) return p / n
  const f = Math.pow(1 + r, n)
  return Math.round(p * (r * f) / (f - 1))
})

const formatRp = (val) =>
  new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val)

const submit = () => {
  form.post(route('loans.store'))
}
</script>

<template>
  <AppLayout>
    <div class="max-w-2xl mx-auto">
      <h1 class="text-2xl font-bold text-gray-800 mb-6">Ajukan Pinjaman</h1>

      <!-- Info Omzet -->
      <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
        <p class="text-sm text-blue-700">
          Omzet Bulanan Anda: <strong>{{ formatRp(revenue) }}</strong> |
          Maks. Pinjaman: <strong>{{ formatRp(maxLoan) }}</strong>
        </p>
      </div>

      <form @submit.prevent="submit" class="bg-white rounded-xl shadow p-6 space-y-5">

        <!-- Amount -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Jumlah Pinjaman (Rp)
          </label>
          <input
            v-model="form.amount"
            type="number"
            :max="maxLoan"
            class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 outline-none"
            placeholder="Contoh: 50000000"
          />
          <p v-if="form.errors.amount" class="text-red-500 text-sm mt-1">
            {{ form.errors.amount }}
          </p>
        </div>

        <!-- Tenor -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Tenor (Bulan): <strong>{{ form.tenor_months }} bulan</strong>
          </label>
          <input
            v-model="form.tenor_months"
            type="range" min="3" max="60" step="3"
            class="w-full accent-blue-600"
          />
          <div class="flex justify-between text-xs text-gray-400 mt-1">
            <span>3 bln</span><span>60 bln</span>
          </div>
        </div>

        <!-- Suku Bunga -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Suku Bunga (% / tahun)
          </label>
          <input
            v-model="form.interest_rate"
            type="number" min="0" max="30" step="0.5"
            class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 outline-none"
          />
        </div>

        <!-- Tujuan -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Tujuan Pinjaman</label>
          <textarea
            v-model="form.purpose"
            rows="3"
            class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 outline-none"
            placeholder="Jelaskan tujuan penggunaan dana..."
          />
          <p v-if="form.errors.purpose" class="text-red-500 text-sm mt-1">
            {{ form.errors.purpose }}
          </p>
        </div>

        <!-- Preview Cicilan -->
        <div v-if="previewInstallment > 0"
             class="bg-green-50 border border-green-200 rounded-lg p-4">
          <p class="text-sm text-green-700">Estimasi Cicilan per Bulan:</p>
          <p class="text-2xl font-bold text-green-800">{{ formatRp(previewInstallment) }}</p>
        </div>

        <!-- Submit Button dengan Loading State -->
        <button
          type="submit"
          :disabled="form.processing"
          class="w-full bg-blue-700 text-white py-3 rounded-lg font-semibold
                 hover:bg-blue-800 transition disabled:opacity-50 disabled:cursor-not-allowed
                 flex items-center justify-center gap-2"
        >
          <!-- Loading Spinner -->
          <svg v-if="form.processing"
               class="animate-spin h-5 w-5"
               xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor"
                  d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
          </svg>
          <span>{{ form.processing ? 'Mengirim...' : 'Ajukan Pinjaman' }}</span>
        </button>

      </form>
    </div>
  </AppLayout>
</template>