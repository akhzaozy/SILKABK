<template>
  <AppLayout :title="`Hasil SDQ — ${screening.patient?.name}`">
    <div class="max-w-3xl space-y-6">

      <!-- Header -->
      <div class="flex items-start justify-between gap-4">
        <div>
          <h2 class="text-lg font-semibold text-gray-800">Hasil Kuesioner SDQ</h2>
          <p class="text-sm text-gray-500 mt-0.5">
            {{ screening.patient?.name }} · {{ formatDate(screening.created_at) }}
          </p>
        </div>
        <Link :href="backRoute"
          class="text-sm text-gray-600 hover:text-gray-800 px-4 py-2 rounded-xl hover:bg-gray-100 transition-colors">
          ← Kembali
        </Link>
      </div>

      <!-- Interpretasi utama -->
      <div :class="totalCardClass" class="rounded-2xl border p-6">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium opacity-75 mb-1">Total Skor Kesulitan</p>
            <p class="text-4xl font-bold">{{ screening.total_score }}<span class="text-base font-normal opacity-60">/40</span></p>
          </div>
          <div class="text-right">
            <span :class="interpretBadge(screening.interpretation)"
              class="inline-flex items-center gap-1.5 text-sm font-semibold px-3 py-1.5 rounded-full border">
              <span :class="interpretDot(screening.interpretation)" class="w-2 h-2 rounded-full"/>
              {{ interpretLabel(screening.interpretation) }}
            </span>
            <p class="text-xs opacity-60 mt-2">Ambang: Normal ≤15 · Borderline 16–19 · Abnormal ≥20</p>
          </div>
        </div>
      </div>

      <!-- Skor per subskala -->
      <div class="bg-white rounded-2xl border border-gray-200 p-5">
        <h3 class="text-sm font-semibold text-gray-700 mb-4">Skor Per Subskala</h3>
        <div class="space-y-3">
          <div v-for="(sub, key) in subscaleDisplay" :key="key">
            <div class="flex items-center justify-between mb-1">
              <span class="text-sm text-gray-700">{{ sub.label }}</span>
              <div class="flex items-center gap-2">
                <span class="text-sm font-semibold text-gray-800">{{ screening[sub.field] }}/{{ sub.max }}</span>
                <span :class="sub.badgeClass" class="text-xs font-medium px-2 py-0.5 rounded-full border">
                  {{ sub.interpretation }}
                </span>
              </div>
            </div>
            <div class="w-full bg-gray-100 rounded-full h-2">
              <div :class="sub.barClass" class="h-2 rounded-full transition-all"
                :style="{ width: `${(screening[sub.field] / sub.max) * 100}%` }"/>
            </div>
            <p class="text-xs text-gray-400 mt-0.5">{{ sub.threshold }}</p>
          </div>
        </div>
      </div>

      <!-- Detail jawaban -->
      <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-100 bg-gray-50">
          <h3 class="text-sm font-semibold text-gray-700">Detail Jawaban</h3>
        </div>
        <div class="divide-y divide-gray-100">
          <div v-for="(qText, qNo) in questions" :key="qNo" class="px-5 py-3 flex items-start gap-3">
            <span class="text-xs font-medium text-gray-400 w-6 flex-shrink-0 pt-0.5">{{ qNo }}.</span>
            <p class="text-sm text-gray-700 flex-1">{{ qText }}</p>
            <span :class="answerBadge(screening.answers?.[qNo - 1])"
              class="flex-shrink-0 text-xs font-medium px-2 py-1 rounded-lg border">
              {{ answerLabel(screening.answers?.[qNo - 1]) }}
            </span>
          </div>
        </div>
      </div>

      <!-- Catatan -->
      <div v-if="screening.notes" class="bg-white rounded-2xl border border-gray-200 p-5">
        <h3 class="text-sm font-semibold text-gray-700 mb-2">Catatan</h3>
        <p class="text-sm text-gray-600 whitespace-pre-line">{{ screening.notes }}</p>
      </div>

    </div>
  </AppLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

const props = defineProps({
  screening: { type: Object, required: true },
  questions: { type: Object, required: true },
  subscales: { type: Object, default: () => ({}) },
  readOnly:  { type: Boolean, default: false },
})

const page = usePage()
const role = computed(() => page.props.auth?.user?.role)

const backRoute = computed(() => {
  if (role.value === 'dokter')   return route('doctor.medical-records')
  if (role.value === 'psikolog') return route('psychologist.medical-records')
  return route('parent.sdq.index')
})

function formatDate(val) {
  if (!val) return '—'
  return new Date(val).toLocaleDateString('id-ID', {
    weekday: 'long', day: '2-digit', month: 'long', year: 'numeric',
  })
}

// ─── Interpretasi subskala ────────────────────────────────────
function emotionalInterp(v)     { return v <= 3 ? 'Normal' : v === 4 ? 'Borderline' : 'Abnormal' }
function conductInterp(v)       { return v <= 2 ? 'Normal' : v === 3 ? 'Borderline' : 'Abnormal' }
function hyperactivityInterp(v) { return v <= 5 ? 'Normal' : v === 6 ? 'Borderline' : 'Abnormal' }
function peerInterp(v)          { return v <= 2 ? 'Normal' : v === 3 ? 'Borderline' : 'Abnormal' }
function prosocialInterp(v)     { return v >= 6 ? 'Normal' : v === 5 ? 'Borderline' : 'Abnormal' }

function interpBadgeCls(interp) {
  if (interp === 'Normal')     return 'bg-green-50 text-green-700 border-green-200'
  if (interp === 'Borderline') return 'bg-yellow-50 text-yellow-700 border-yellow-200'
  return 'bg-red-50 text-red-700 border-red-200'
}
function interpBarCls(interp) {
  if (interp === 'Normal')     return 'bg-green-400'
  if (interp === 'Borderline') return 'bg-yellow-400'
  return 'bg-red-400'
}

const subscaleDisplay = computed(() => {
  const s = props.screening
  const build = (label, field, max, interpFn, threshold) => {
    const interp = interpFn(s[field])
    return { label, field, max, interpretation: interp, threshold,
      badgeClass: interpBadgeCls(interp), barClass: interpBarCls(interp) }
  }
  return {
    emotional:     build('Gejala Emosional',      'emotional_score',     10, emotionalInterp,     'Normal ≤3 · Borderline 4 · Abnormal ≥5'),
    conduct:       build('Masalah Perilaku',       'conduct_score',       10, conductInterp,       'Normal ≤2 · Borderline 3 · Abnormal ≥4'),
    hyperactivity: build('Hiperaktivitas',          'hyperactivity_score', 10, hyperactivityInterp, 'Normal ≤5 · Borderline 6 · Abnormal ≥7'),
    peer:          build('Masalah Teman Sebaya',   'peer_score',          10, peerInterp,          'Normal ≤2 · Borderline 3 · Abnormal ≥4'),
    prosocial:     build('Perilaku Prososial',     'prosocial_score',     10, prosocialInterp,     'Normal ≥6 · Borderline 5 · Abnormal ≤4'),
  }
})

// ─── Total card color ─────────────────────────────────────────
const totalCardClass = computed(() => {
  const i = props.screening.interpretation
  if (i === 'normal')     return 'bg-green-50 border-green-200 text-green-900'
  if (i === 'borderline') return 'bg-yellow-50 border-yellow-200 text-yellow-900'
  return 'bg-red-50 border-red-200 text-red-900'
})

const interpretMap = {
  normal:     { label: 'Normal',     badge: 'bg-green-100 text-green-700 border-green-300',    dot: 'bg-green-500'  },
  borderline: { label: 'Borderline', badge: 'bg-yellow-100 text-yellow-700 border-yellow-300', dot: 'bg-yellow-500' },
  abnormal:   { label: 'Abnormal',   badge: 'bg-red-100 text-red-700 border-red-300',          dot: 'bg-red-500'    },
}
function interpretLabel(v) { return interpretMap[v]?.label ?? v }
function interpretBadge(v) { return interpretMap[v]?.badge ?? '' }
function interpretDot(v)   { return interpretMap[v]?.dot   ?? '' }

// ─── Jawaban ──────────────────────────────────────────────────
const answerLabels = { 0: 'Tidak Benar', 1: 'Agak Benar', 2: 'Benar' }
function answerLabel(v) { return answerLabels[v] ?? '—' }
function answerBadge(v) {
  if (v === 0) return 'bg-gray-50 text-gray-600 border-gray-200'
  if (v === 1) return 'bg-blue-50 text-blue-700 border-blue-200'
  return 'bg-indigo-50 text-indigo-700 border-indigo-200'
}
</script>
