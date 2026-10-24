// resources/js/composables/useFlash.js
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

export function useFlash() {
    const page = usePage()

    const success = computed(() => page.props.flash?.success ?? null)
    const error   = computed(() => page.props.flash?.error ?? null)

    return { success, error }
}