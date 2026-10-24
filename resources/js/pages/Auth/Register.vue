<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-100 py-12 px-4">
    <div class="max-w-md w-full bg-white rounded-lg shadow-md p-8">
      <h2 class="text-2xl font-bold text-center mb-2">Registrasi Orang Tua</h2>
      <p class="text-center text-sm text-gray-600 mb-6">Daftar untuk mendapatkan akses layanan terapi anak</p>

      <form @submit.prevent="submit">
        <div class="mb-4">
          <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
            Nama Lengkap <span class="text-red-500">*</span>
          </label>
          <input
            id="name"
            v-model="form.name"
            type="text"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            :class="{ 'border-red-500': form.errors.name }"
            required
          />
          <p v-if="form.errors.name" class="text-red-500 text-sm mt-1">{{ form.errors.name }}</p>
        </div>

        <div class="mb-4">
          <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
            Email <span class="text-red-500">*</span>
          </label>
          <input
            id="email"
            v-model="form.email"
            type="email"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            :class="{ 'border-red-500': form.errors.email }"
            required
          />
          <p v-if="form.errors.email" class="text-red-500 text-sm mt-1">{{ form.errors.email }}</p>
        </div>

        <div class="mb-4">
          <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
            Password <span class="text-red-500">*</span>
          </label>
          <input
            id="password"
            v-model="form.password"
            type="password"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            :class="{ 'border-red-500': form.errors.password }"
            required
          />
          <p v-if="form.errors.password" class="text-red-500 text-sm mt-1">{{ form.errors.password }}</p>
        </div>

        <div class="mb-6">
          <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
            Konfirmasi Password <span class="text-red-500">*</span>
          </label>
          <input
            id="password_confirmation"
            v-model="form.password_confirmation"
            type="password"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            required
          />
        </div>

        <button
          type="submit"
          class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 mb-4"
          :disabled="form.processing"
        >
          {{ form.processing ? 'Loading...' : 'Register' }}
        </button>

        <p class="text-center text-sm text-gray-600">
          Sudah punya akun?
          <Link :href="route('login')" class="text-blue-600 hover:underline">Login di sini</Link>
        </p>
      </form>
    </div>
  </div>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3'

const form = useForm({
  name: '',
  email: '',
  role: 'orang_tua', // Fixed role untuk orang tua
  password: '',
  password_confirmation: ''
})

const submit = () => {
  form.post('/register', {
    onFinish: () => {
      form.reset('password', 'password_confirmation')
    }
  })
}
</script>
