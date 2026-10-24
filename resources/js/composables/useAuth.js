// resources/js/composables/useAuth.js
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

export function useAuth() {
    const page = usePage()

    const user = computed(() => page.props.auth?.user ?? null)
    const role = computed(() => user.value?.role ?? null)

    const isRole = (...roles) => roles.includes(role.value)

    const isSuperAdmin = computed(() => role.value === 'super_admin')
    const isAdmin      = computed(() => role.value === 'admin')
    const isOrangTua   = computed(() => role.value === 'orang_tua')
    const isTerapis    = computed(() => role.value === 'terapis')
    const isDokter     = computed(() => role.value === 'dokter')
    const isPsikolog   = computed(() => role.value === 'psikolog')

    return {
        user,
        role,
        isRole,
        isSuperAdmin,
        isAdmin,
        isOrangTua,
        isTerapis,
        isDokter,
        isPsikolog,
    }
}