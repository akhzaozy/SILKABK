<template>
  <AppLayout title="Dashboard">
    <!-- Welcome Section -->
    <div class="mb-6 pb-5 border-b border-gray-200">
      <h2 class="text-xl font-semibold text-gray-800">Selamat Datang, <span class="text-blue-600">{{ auth.user.name }}</span></h2>
      <p class="text-sm text-gray-500 mt-0.5">{{ getRoleWelcomeMessage() }}</p>
    </div>

    <!-- Admin/Super Admin Dashboard -->
    <template v-if="isAdmin || isSuperAdmin">
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <StatCard
          title="Total Pasien"
          :value="stats.totalPatients || 0"
          subtitle="Pasien terdaftar"
        >
          <template #icon>
            <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center">
              <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
              </svg>
            </div>
          </template>
        </StatCard>

        <StatCard
          title="Jadwal Hari Ini"
          :value="stats.todaySchedules || 0"
          subtitle="Sesi terapi"
        >
          <template #icon>
            <div class="w-12 h-12 rounded-xl bg-green-50 flex items-center justify-center">
              <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
              </svg>
            </div>
          </template>
        </StatCard>

        <StatCard
          title="Pendaftaran Pending"
          :value="stats.pendingRegistrations || 0"
          subtitle="Menunggu persetujuan"
        >
          <template #icon>
            <div class="w-12 h-12 rounded-xl bg-yellow-50 flex items-center justify-center">
              <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
            </div>
          </template>
        </StatCard>

        <StatCard
          title="Pembayaran Bulan Ini"
          :value="formatRupiah(stats.monthlyRevenue || 0)"
          subtitle="Total pendapatan"
        >
          <template #icon>
            <div class="w-12 h-12 rounded-xl bg-purple-50 flex items-center justify-center">
              <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
            </div>
          </template>
        </StatCard>
      </div>

      <!-- Quick Actions -->
      <div class="mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Aksi Cepat</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <QuickLink
            :href="route('patients.create')"
            title="Tambah Pasien Baru"
            description="Daftarkan pasien baru ke sistem"
          >
            <template #icon>
              <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
              </svg>
            </template>
          </QuickLink>

          <QuickLink
            :href="route('schedules.create')"
            title="Buat Jadwal Terapi"
            description="Atur jadwal sesi terapi"
          >
            <template #icon>
              <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
              </svg>
            </template>
          </QuickLink>

          <QuickLink
            :href="route('payments.create')"
            title="Catat Pembayaran"
            description="Input pembayaran baru"
          >
            <template #icon>
              <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
              </svg>
            </template>
          </QuickLink>
        </div>
      </div>
    </template>

    <!-- Orang Tua Dashboard -->
    <template v-if="isOrangTua">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <StatCard
          title="Anak Terdaftar"
          :value="stats.totalChildren || 0"
          subtitle="Pasien"
        />
        <StatCard
          title="Jadwal Mendatang"
          :value="stats.upcomingSchedules || 0"
          subtitle="Sesi terapi"
        />
        <StatCard
          title="Pembayaran Pending"
          :value="stats.unpaidPayments || 0"
          subtitle="Belum lunas"
        />
      </div>

      <div class="mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Menu Utama</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <QuickLink
            :href="route('parent.register')"
            title="Daftar Layanan Baru"
            description="Daftarkan anak untuk terapi"
          />
          <QuickLink
            :href="route('parent.schedules')"
            title="Lihat Jadwal Terapi"
            description="Jadwal sesi terapi anak"
          />
          <QuickLink
            :href="route('parent.payments')"
            title="Riwayat Pembayaran"
            description="Lihat pembayaran dan tagihan"
          />
        </div>
      </div>
    </template>

    <!-- Terapis Dashboard -->
    <template v-if="isTerapis">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <StatCard
          title="Jadwal Hari Ini"
          :value="stats.todaySchedules || 0"
          subtitle="Sesi terapi"
        />
        <StatCard
          title="Jadwal Minggu Ini"
          :value="stats.weekSchedules || 0"
          subtitle="Total sesi"
        />
      </div>

      <div class="mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Aksi Cepat</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <QuickLink
            :href="route('therapist.schedules')"
            title="Jadwal Terapi Saya"
            description="Lihat jadwal sesi terapi"
          />
        </div>
      </div>
    </template>

    <!-- Dokter Dashboard -->
    <template v-if="isDokter">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <StatCard
          title="Total Rekam Medis"
          :value="stats.totalRecords || 0"
          subtitle="Rekam medis"
        />
        <StatCard
          title="KPSP Bulan Ini"
          :value="stats.monthlyKpsp || 0"
          subtitle="Screening dilakukan"
        />
      </div>

      <div class="mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Aksi Cepat</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <QuickLink
            :href="route('doctor.medical-records.create')"
            title="Input Rekam Medis Baru"
            description="Buat rekam medis pasien"
          />
          <QuickLink
            :href="route('doctor.medical-records')"
            title="Daftar Rekam Medis"
            description="Lihat semua rekam medis"
          />
        </div>
      </div>
    </template>

    <!-- Psikolog Dashboard -->
    <template v-if="isPsikolog">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <StatCard
          title="Total Rekam Medis"
          :value="stats.totalRecords || 0"
          subtitle="Rekam medis"
        />
        <StatCard
          title="KPSP Bulan Ini"
          :value="stats.monthlyKpsp || 0"
          subtitle="Screening dilakukan"
        />
      </div>

      <div class="mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Aksi Cepat</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <QuickLink
            :href="route('psychologist.medical-records.create')"
            title="Input Asesmen Psikologi"
            description="Buat rekam medis & asesmen pasien"
          >
            <template #icon>
              <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
              </svg>
            </template>
          </QuickLink>
          <QuickLink
            :href="route('psychologist.medical-records')"
            title="Daftar Rekam Medis"
            description="Lihat semua rekam medis"
          >
            <template #icon>
              <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
              </svg>
            </template>
          </QuickLink>
        </div>
      </div>
    </template>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/layouts/AppLayout.vue'
import StatCard from '@/components/StatCard.vue'
import QuickLink from '@/components/QuickLink.vue'
import { useAuth } from '@/composables/useAuth'

const props = defineProps({
  auth: Object,
  stats: {
    type: Object,
    default: () => ({})
  }
})

const { isAdmin, isSuperAdmin, isOrangTua, isTerapis, isDokter, isPsikolog } = useAuth()

function getRoleWelcomeMessage() {
  const role = props.auth.user.role
  const messages = {
    'super_admin': 'Anda memiliki akses penuh ke seluruh sistem',
    'admin': 'Kelola data pasien dan operasional klinik',
    'orang_tua': 'Pantau perkembangan terapi anak Anda',
    'terapis': 'Lihat jadwal dan input hasil terapi',
    'dokter': 'Kelola rekam medis dan screening KPSP',
    'psikolog': 'Kelola asesmen psikologi dan screening KPSP'
  }
  return messages[role] || 'Selamat bekerja!'
}

function formatRupiah(value) {
  return 'Rp ' + Number(value || 0).toLocaleString('id-ID')
}
</script>
