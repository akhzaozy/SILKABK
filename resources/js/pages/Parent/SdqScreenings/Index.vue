<template>
  <AppLayout title="Kuesioner SDQ">
    <div class="space-y-5">

      <!-- Header -->
      <div class="flex items-start justify-between gap-4">
        <div>
          <h2 class="text-lg font-semibold text-gray-800">Kuesioner SDQ</h2>
          <p class="text-sm text-gray-500 mt-0.5">
            Strengths and Difficulties Questionnaire — skrining perilaku dan emosi anak
          </p>
        </div>
        <Link :href="route('parent.sdq.create')"
          class="flex-shrink-0 inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-xl transition-colors">
          + Isi Kuesioner
        </Link>
      </div>

      <!-- Filter pasien -->
      <div v-if="patients.length > 1" class="bg-white rounded-2xl border border-gray-200 p-4">
        <div class="flex flex-wrap gap-3">
          <select v-model="f.patient_id" @change="applyFilter"
            class="text-sm border border-gray-300 rounded-xl px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">Semua Anak</option>
            <option v-for="p in patients" :key="p.id" :value="p.id">{{ p.name }}</option>
          </select>
          <button v-if="f.patient_id" @click="resetFilter"
            class="text-sm text-gray-500 hover:text-gray-700 px-3 py-2 rounded-xl hover:bg-gray-100">
            Reset
          </button>
        </div>
      </div>

      <!-- Empty state -->
      <div v-if="screenings.data.length === 0"
        class="bg-white rounded-2xl border border-gray-200 p-12 text-center">
        <div class="w-14 h-14 mx-auto mb-4 rounded-full bg-purple-100 flex items-center justify-center">
          <svg class="w-7 h-7 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0119 9.414V19a2 2 0 01-2 2z"/>
          </svg>
        </div>
        <h3 class="text-base font-medium text-gray-800 mb-1">Belum Ada Kuesioner SDQ</h3>
        <p class="text-sm text-gray-500 mb-4">Mulai isi kuesioner SDQ untuk memantau perkembangan emosi dan perilaku anak</p>
        <Link :href="route('parent.sdq.create')"
          class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-xl transition-colors">
          Isi Kuesioner Sekarang
        </Link>
      </div>

      <!-- List -->
      <div v-else class="space-y-3">
        <div v-for="s in screenings.data" :key="s.id"
          class="bg-white rounded-2xl border border-gray-200 hover:border-blue-200 hover:shadow-sm transition-all p-5">
          <div class="flex items-start justify-between gap-4">
            <div class="flex-1">
              <div class="flex items-center gap-2 mb-2">
                <span :class="interpretBadge(s.interpretation)"
                  class="inline-flex items-center gap-1.5 text-xs font-medium px-2.5 py-1 rounded-full border">
                  <span :class="interpretDot(s.interpretation)" class="w-1.5 h-1.5 rounded-full"/>
                  {{ interpretLabel(s.interpretation) }}
                </span>
                <span class="text-xs text-gray-400">Total: {{ s.total_score }}/40</span>
              </div>
              <p class="text-base font-semibold text-gray-800">{{ s.patient?.name }}</p>
              <p class="text-sm text-gray-500">{{ formatDate(s.created_at) }}</p>

              <div class="grid grid-cols-2 sm:grid-cols-5 gap-2 mt-3">
                <div v-for="(val, key) in subscaleScores(s)" :key="key"
                  class="bg-gray-50 rounded-xl px-3 py-2 text-center">
                  <p class="text-xs text-gray-500 leading-tight">{{ val.label }}</p>
                  <p class="text-base font-bold text-gray-800">{{ val.score }}</p>
                </div>
              </div>
            </div>

            <Link :href="route('parent.sdq.show', s.id)"
              class="flex-shrink-0 text-xs font-medium text-blue-600 hover:text-blue-800 px-3 py-1.5 rounded-lg hover:bg-blue-50 transition-colors">
              Detail →
            </Link>
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="screenings.last_page > 1" class="flex items-center justify-between">
        <p class="text-xs text-gray-500">{{ screenings.from }}–{{ screenings.to }} dari {{ screenings.total }} kuesioner</p>
        <div class="flex gap-1">
          <Link v-for="link in screenings.links" :key="link.label" :href="link.url ?? '#'"
            :class="[
              'px-3 py-1.5 text-xs rounded-lg transition-colors',
              link.active ? 'bg-blue-600 text-white font-semibold'
                : link.url ? 'text-gray-600 hover:bg-gray-100'
                : 'text-gray-300 cursor-not-allowed',
            ]"
            v-html="link.label"
          />
        </div>
      </div>

    </div>
  </AppLayout>
</template>

<script setup>
import { reactive } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

const props = defineProps({
  screenings: { type: Object, required: true },
  patients:   { type: Array,  default: () => [] },
  filters:    { type: Object, default: () => ({}) },
})

const f = reactive({ patient_id: props.filters.patient_id ?? '' })

function applyFilter() {
  const params = {}
  if (f.patient_id) params.patient_id = f.patient_id
  router.get(route('parent.sdq.index'), params, { preserveState: true, replace: true })
}

function resetFilter() {
  f.patient_id = ''
  applyFilter()
}

function formatDate(val) {
  if (!val) return '—'
  return new Date(val).toLocaleDateString('id-ID', {
    weekday: 'long', day: '2-digit', month: 'long', year: 'numeric',
  })
}

function subscaleScores(s) {
  return {
    emotional:     { label: 'Emosional',    score: s.emotional_score },
    conduct:       { label: 'Perilaku',      score: s.conduct_score },
    hyperactivity: { label: 'Hiperaktivitas', score: s.hyperactivity_score },
    peer:          { label: 'Teman Sebaya',  score: s.peer_score },
    prosocial:     { label: 'Prososial',     score: s.prosocial_score },
  }
}

const interpretMap = {
  normal:     { label: 'Normal',     badge: 'bg-green-50 text-green-700 border-green-200',  dot: 'bg-green-500'  },
  borderline: { label: 'Borderline', badge: 'bg-yellow-50 text-yellow-700 border-yellow-200', dot: 'bg-yellow-500' },
  abnormal:   { label: 'Abnormal',   badge: 'bg-red-50 text-red-700 border-red-200',        dot: 'bg-red-500'    },
}
function interpretLabel(v)  { return interpretMap[v]?.label ?? v }
function interpretBadge(v)  { return interpretMap[v]?.badge ?? 'bg-gray-50 text-gray-600 border-gray-200' }
function interpretDot(v)    { return interpretMap[v]?.dot   ?? 'bg-gray-400' }
</script>
