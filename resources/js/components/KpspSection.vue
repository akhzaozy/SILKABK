<template>
  <div class="bg-purple-50 border border-purple-200 rounded-2xl p-6 space-y-4">
    <div class="flex items-center gap-2 pb-3 border-b border-purple-200">
      <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
      </svg>
      <h3 class="font-semibold text-purple-900">
        Skrining KPSP <span class="text-purple-500 font-normal text-sm">(opsional)</span>
      </h3>
    </div>
    <slot name="description" />

    <div class="grid grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium text-purple-800 mb-1.5">Usia Skrining (bulan)</label>
        <input type="number" v-model="kpsp.usia_bulan" min="3" max="72"
          class="w-full px-3 py-2 border border-purple-300 bg-white rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-400"
          placeholder="mis. 24" />
      </div>
      <div>
        <label class="block text-sm font-medium text-purple-800 mb-1.5">Tanggal Skrining</label>
        <input type="date" v-model="kpsp.tanggal_screening"
          class="w-full px-3 py-2 border border-purple-300 bg-white rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-400" />
      </div>
    </div>

    <div>
      <label class="block text-sm font-medium text-purple-800 mb-1.5">Skor KPSP (0–10)</label>
      <input type="number" v-model="kpsp.skor" min="0" max="10"
        class="w-full px-3 py-2 border border-purple-300 bg-white rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-400"
        placeholder="Masukkan jumlah jawaban 'Ya'" />
    </div>

    <div>
      <label class="block text-sm font-medium text-purple-800 mb-1.5">Interpretasi</label>
      <div class="px-3 py-2 border border-purple-200 bg-white rounded-xl text-sm font-medium"
        :class="interpretasiClass">
        {{ interpretasi || 'Dihitung otomatis dari skor' }}
      </div>
    </div>

    <div>
      <label class="block text-sm font-medium text-purple-800 mb-1.5">
        Catatan KPSP <span class="text-purple-400 font-normal">(opsional)</span>
      </label>
      <textarea v-model="kpsp.catatan" rows="2"
        class="w-full px-3 py-2 border border-purple-300 bg-white rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-400"
        placeholder="Catatan tambahan hasil skrining..."/>
    </div>
  </div>
</template>

<script setup>
import { reactive, computed } from 'vue'

const props = defineProps({
  initial: { type: Object, default: null },
})

const kpsp = reactive({
  usia_bulan:        props.initial?.usia_bulan        ?? '',
  tanggal_screening: props.initial?.tanggal_screening ?? '',
  skor:              props.initial?.skor !== undefined ? props.initial.skor : '',
  catatan:           props.initial?.catatan           ?? '',
})

const interpretasi = computed(() => {
  if (kpsp.skor === '') return ''
  const s = Number(kpsp.skor)
  if (s >= 9) return 'Sesuai'
  if (s >= 7) return 'Meragukan'
  return 'Penyimpangan'
})

const interpretasiClass = computed(() => ({
  'text-green-700':         interpretasi.value === 'Sesuai',
  'text-yellow-700':        interpretasi.value === 'Meragukan',
  'text-red-700':           interpretasi.value === 'Penyimpangan',
  'text-purple-400 italic': !interpretasi.value,
}))

function build() {
  if (kpsp.skor === '') return null
  const obj = { skor: Number(kpsp.skor), interpretasi: interpretasi.value }
  if (kpsp.usia_bulan)        obj.usia_bulan        = Number(kpsp.usia_bulan)
  if (kpsp.tanggal_screening) obj.tanggal_screening = kpsp.tanggal_screening
  if (kpsp.catatan)           obj.catatan           = kpsp.catatan
  return obj
}

defineExpose({ build })
</script>
