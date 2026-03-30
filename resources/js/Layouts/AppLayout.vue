<script setup>
import { usePage, Link, router } from '@inertiajs/vue3' // ← tambah router
import { computed } from 'vue'

const user = computed(() => usePage().props.auth.user)

const menus = computed(() => {
    const base = [
        { label: 'Dashboard', href: route('dashboard'), icon: '🏠' },
        { label: 'Pinjaman',  href: route('loans.index'), icon: '💰' },
    ]
    if (user.value.role === 'borrower') {
        base.push({ label: 'Ajukan Pinjaman', href: route('loans.create'), icon: '➕' })
    }
    return base
})

// ← Ganti pakai fungsi ini
const logout = () => {
    router.post(route('logout'))
}
</script>

<template>
    <div class="flex min-h-screen bg-gray-100">
        <aside class="w-64 bg-blue-900 text-white flex flex-col">
            <div class="p-6 text-xl font-bold border-b border-blue-700">
                💼 Digital UMKM
            </div>

            <nav class="flex-1 p-4 space-y-1">
                <Link
                    v-for="menu in menus"
                    :key="menu.href"
                    :href="menu.href"
                    class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-blue-700 transition"
                >
                    <span>{{ menu.icon }}</span>
                    <span>{{ menu.label }}</span>
                </Link>
            </nav>

            <div class="p-4 border-t border-blue-700">
                <p class="font-semibold text-sm">{{ user.name }}</p>
                <p class="text-xs text-blue-300 mb-3">{{ user.email }}</p>
                <span class="capitalize bg-blue-600 text-xs px-2 py-0.5 rounded-full">
                    {{ user.role }}
                </span>

                <!-- ← Ganti jadi button biasa dengan @click -->
                <button
                    @click="logout"
                    class="w-full mt-4 flex items-center gap-2 px-4 py-2 rounded-lg
                           bg-red-600 hover:bg-red-700 transition text-sm font-medium"
                >
                    🚪 Logout
                </button>
            </div>
        </aside>

        <main class="flex-1 p-8">
            <div v-if="$page.props.flash?.success"
                 class="mb-6 bg-green-100 border border-green-300 text-green-700
                        px-4 py-3 rounded-lg text-sm">
                ✅ {{ $page.props.flash.success }}
            </div>
            <slot />
        </main>
    </div>
</template>