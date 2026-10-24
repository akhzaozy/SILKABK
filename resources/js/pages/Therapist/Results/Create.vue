<template>
  <AppLayout title="Input Hasil Terapi">
    <div class="max-w-2xl space-y-5">

      <!-- Header -->
      <div class="flex items-center gap-3">
        <Link :href="route('therapist.schedules.show', schedule.id)" class="p-2 rounded-lg hover:bg-gray-100 transition-colors">
          <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
          </svg>
        </Link>
        <div>
          <h2 class="text-lg font-semibold text-gray-800">Input Hasil Terapi</h2>
          <p class="text-sm text-gray-500">{{ schedule.patient?.name }} · {{ formatDate(schedule.schedule_date) }}</p>
        </div>
      </div>

      <!-- Info Sesi -->
      <div class="bg-blue-50 border border-blue-200 rounded-2xl p-4">
        <div class="grid grid-cols-2 gap-3 text-sm">
          <div>
            <p class="text-xs font-medium text-blue-600 uppercase">Jenis Terapi</p>
            <p class="text-blue-900 font-medium">{{ schedule.therapy_type?.name }}</p>
          </div>
          <div>
            <p class="text-xs font-medium text-blue-600 uppercase">Waktu</p>
            <p class="text-blue-900">{{ schedule.start_time }} – {{ schedule.end_time }}</p>
          </div>
        </div>
      </div>

      <!-- Form -->
      <form @submit.prevent="submit" class="bg-white rounded-2xl border border-gray-200 p-6 space-y-5">

        <input type="hidden" v-model="form.schedule_id" />

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">
            Tanggal Sesi <span class="text-red-500">*</span>
          </label>
          <input type="date" v-model="form.session_date"
            class="w-full px-3 py-2 border rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
            :class="form.errors.session_date ? 'border-red-400' : 'border-gray-300'" />
          <p v-if="form.errors.session_date" class="mt-1 text-xs text-red-500">{{ form.errors.session_date }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">
            Aktivitas Terapi <span class="text-red-500">*</span>
          </label>
          <textarea v-model="form.activities" rows="4"
            class="w-full px-3 py-2 border rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
            :class="form.errors.activities ? 'border-red-400' : 'border-gray-300'"
            placeholder="Jelaskan aktivitas yang dilakukan selama sesi terapi..."/>
          <p v-if="form.errors.activities" class="mt-1 text-xs text-red-500">{{ form.errors.activities }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">
            Respons & Perkembangan Anak <span class="text-red-500">*</span>
          </label>
          <textarea v-model="form.patient_response" rows="4"
            class="w-full px-3 py-2 border rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
            :class="form.errors.patient_response ? 'border-red-400' : 'border-gray-300'"
            placeholder="Catat respons anak selama sesi dan perkembangan yang terlihat..."/>
          <p v-if="form.errors.patient_response" class="mt-1 text-xs text-red-500">{{ form.errors.patient_response }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">
            Target yang Tercapai <span class="text-gray-400 font-normal">(opsional)</span>
          </label>
          <textarea v-model="form.goals_achieved" rows="3"
            class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
            placeholder="Target atau tujuan terapi yang berhasil dicapai pada sesi ini..."/>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">
            Rencana Sesi Berikutnya <span class="text-gray-400 font-normal">(opsional)</span>
          </label>
          <textarea v-model="form.next_plan" rows="3"
            class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
            placeholder="Rencana aktivitas atau fokus untuk sesi terapi berikutnya..."/>
        </div>

        <div class="flex items-center justify-end gap-3 pt-2 border-t border-gray-100">
          <Link :href="route('therapist.schedules.show', schedule.id)"
            class="px-4 py-2 text-sm text-gray-600 border border-gray-300 rounded-xl hover:bg-gray-50 transition-colors">
            Batal
          </Link>
          <button type="submit" :disabled="form.processing"
            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-xl transition-colors disabled:opacity-50">
            {{ form.processing ? 'Menyimpan...' : 'Simpan & Selesaikan Sesi' }}
          </button>
        </div>
      </form>

    </div>
  </AppLayout>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

const props = defineProps({
  schedule: { type: Object, required: true },
})

const form = useForm({
  schedule_id:      props.schedule.id,
  session_date:     props.schedule.schedule_date,
  activities:       '',
  patient_response: '',
  goals_achieved:   '',
  next_plan:        '',
})

function submit() {
  form.post(route('therapist.results.store'))
}

function formatDate(val) {
  if (!val) return '—'
  return new Date(val).toLocaleDateString('id-ID', { weekday: 'long', day: '2-digit', month: 'long', year: 'numeric' })
}
</script>
