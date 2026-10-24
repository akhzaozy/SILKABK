<template>
  <AppLayout title="Isi Kuesioner SDQ">
    <div class="max-w-3xl space-y-6">

      <!-- Header -->
      <div>
        <h2 class="text-lg font-semibold text-gray-800">Kuesioner SDQ</h2>
        <p class="text-sm text-gray-500 mt-0.5">
          Strengths and Difficulties Questionnaire (SDQ) — Isilah berdasarkan perilaku anak dalam 6 bulan terakhir
        </p>
      </div>

      <!-- Info card -->
      <div class="bg-blue-50 border border-blue-200 rounded-2xl p-4 text-sm text-blue-800">
        <p class="font-medium mb-1">Petunjuk Pengisian</p>
        <p>Untuk setiap pernyataan berikut, pilih jawaban yang paling sesuai dengan kondisi anak Anda.</p>
        <ul class="mt-2 space-y-1 text-xs text-blue-700">
          <li>• <strong>Tidak Benar</strong> — pernyataan tidak sesuai dengan anak Anda</li>
          <li>• <strong>Agak Benar</strong> — pernyataan agak sesuai</li>
          <li>• <strong>Benar</strong> — pernyataan jelas sesuai dengan anak Anda</li>
        </ul>
      </div>

      <form @submit.prevent="submit" class="space-y-6">

        <!-- Pilih anak -->
        <div class="bg-white rounded-2xl border border-gray-200 p-5">
          <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Anak <span class="text-red-500">*</span></label>
          <select v-model="form.patient_id"
            class="w-full text-sm border border-gray-300 rounded-xl px-3 py-2.5 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500"
            :class="{ 'border-red-400': form.errors.patient_id }">
            <option value="">— Pilih anak —</option>
            <option v-for="p in patients" :key="p.id" :value="p.id">
              {{ p.name }} ({{ formatAge(p.birth_date) }})
            </option>
          </select>
          <p v-if="form.errors.patient_id" class="mt-1 text-xs text-red-600">{{ form.errors.patient_id }}</p>
        </div>

        <!-- Pertanyaan per subskala -->
        <div v-for="(subscale, key) in subscaleGroups" :key="key"
          class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
          <div :class="subscaleHeaderClass(key)" class="px-5 py-3 border-b">
            <h3 class="text-sm font-semibold">{{ subscale.label }}</h3>
          </div>
          <div class="divide-y divide-gray-100">
            <div v-for="qNo in subscale.items" :key="qNo" class="px-5 py-4">
              <p class="text-sm text-gray-800 mb-3">
                <span class="text-xs font-medium text-gray-400 mr-2">{{ qNo }}.</span>
                {{ questions[qNo] }}
              </p>
              <div class="flex gap-3">
                <label v-for="opt in answerOptions" :key="opt.value"
                  class="flex items-center gap-2 cursor-pointer">
                  <input type="radio"
                    :name="`q${qNo}`"
                    :value="opt.value"
                    v-model="form.answers[qNo - 1]"
                    class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500"
                  />
                  <span class="text-sm text-gray-700">{{ opt.label }}</span>
                </label>
              </div>
              <p v-if="form.errors[`answers.${qNo - 1}`]" class="mt-1 text-xs text-red-600">
                Pertanyaan ini harus dijawab.
              </p>
            </div>
          </div>
        </div>

        <!-- Unanswered warning -->
        <div v-if="unansweredCount > 0" class="bg-yellow-50 border border-yellow-200 rounded-2xl p-4 text-sm text-yellow-800">
          Masih ada <strong>{{ unansweredCount }} pertanyaan</strong> yang belum dijawab.
        </div>

        <!-- Catatan -->
        <div class="bg-white rounded-2xl border border-gray-200 p-5">
          <label class="block text-sm font-medium text-gray-700 mb-2">Catatan Tambahan <span class="text-gray-400 font-normal">(opsional)</span></label>
          <textarea v-model="form.notes" rows="3"
            placeholder="Tuliskan catatan atau konteks tambahan jika ada..."
            class="w-full text-sm border border-gray-300 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"/>
        </div>

        <!-- Aksi -->
        <div class="flex items-center gap-3 justify-end">
          <Link :href="route('parent.sdq.index')"
            class="text-sm text-gray-600 hover:text-gray-800 px-4 py-2 rounded-xl hover:bg-gray-100 transition-colors">
            Batal
          </Link>
          <button type="submit" :disabled="form.processing || unansweredCount > 0"
            class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed text-white text-sm font-medium px-6 py-2.5 rounded-xl transition-colors">
            <svg v-if="form.processing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
            </svg>
            Simpan Kuesioner
          </button>
        </div>

      </form>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

const props = defineProps({
  patients:  { type: Array,  required: true },
  questions: { type: Object, required: true },
})

const form = useForm({
  patient_id: '',
  answers:    Array(25).fill(null),
  notes:      '',
})

const answerOptions = [
  { value: 0, label: 'Tidak Benar' },
  { value: 1, label: 'Agak Benar' },
  { value: 2, label: 'Benar' },
]

const subscaleGroups = {
  prosocial:     { label: 'Perilaku Prososial',    items: [1, 4, 9, 17, 20] },
  hyperactivity: { label: 'Hiperaktivitas',         items: [2, 10, 15, 21, 25] },
  emotional:     { label: 'Gejala Emosional',       items: [3, 8, 13, 16, 24] },
  conduct:       { label: 'Masalah Perilaku',       items: [5, 7, 12, 18, 22] },
  peer:          { label: 'Masalah Teman Sebaya',   items: [6, 11, 14, 19, 23] },
}

const headerClasses = {
  prosocial:     'bg-green-50 text-green-800 border-green-100',
  hyperactivity: 'bg-orange-50 text-orange-800 border-orange-100',
  emotional:     'bg-purple-50 text-purple-800 border-purple-100',
  conduct:       'bg-red-50 text-red-800 border-red-100',
  peer:          'bg-blue-50 text-blue-800 border-blue-100',
}
function subscaleHeaderClass(key) { return headerClasses[key] ?? 'bg-gray-50 text-gray-800 border-gray-100' }

const unansweredCount = computed(() => form.answers.filter(a => a === null).length)

function submit() {
  form.post(route('parent.sdq.store'))
}

function formatAge(birthDate) {
  if (!birthDate) return '—'
  const birth = new Date(birthDate)
  const now   = new Date()
  const years = now.getFullYear() - birth.getFullYear()
  const months = now.getMonth() - birth.getMonth()
  const totalMonths = years * 12 + months
  if (totalMonths < 12) return `${totalMonths} bulan`
  const y = Math.floor(totalMonths / 12)
  const m = totalMonths % 12
  return m > 0 ? `${y} tahun ${m} bulan` : `${y} tahun`
}
</script>
