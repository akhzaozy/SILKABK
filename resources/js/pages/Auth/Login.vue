<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="max-w-md w-full bg-white rounded-lg shadow-md p-8">
      <h2 class="text-2xl font-bold text-center mb-6">Login</h2>

      <form @submit.prevent="submit">
        <div class="mb-4">
          <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
          <input
            id="email"
            v-model="form.email"
            type="email"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            :class="{ 'border-red-500': errors.email }"
            required
          />
          <p v-if="errors.email" class="text-red-500 text-sm mt-1">{{ errors.email }}</p>
        </div>

        <div class="mb-4">
          <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
          <input
            id="password"
            v-model="form.password"
            type="password"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            :class="{ 'border-red-500': errors.password }"
            required
          />
          <p v-if="errors.password" class="text-red-500 text-sm mt-1">{{ errors.password }}</p>
        </div>

        <div class="mb-6 flex items-center">
          <input
            id="remember"
            v-model="form.remember"
            type="checkbox"
            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
          />
          <label for="remember" class="ml-2 block text-sm text-gray-700">Remember me</label>
        </div>

        <button
          type="submit"
          class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 mb-4"
          :disabled="form.processing"
        >
          {{ form.processing ? 'Loading...' : 'Login' }}
        </button>

        <p class="text-center text-sm text-gray-600">
          Belum punya akun?
          <Link :href="route('register')" class="text-blue-600 hover:underline font-medium">
            Buat Akun
          </Link>
        </p>
      </form>
    </div>
  </div>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3'

defineProps({
  errors: Object
})

const form = useForm({
  email: '',
  password: '',
  remember: false
})

const submit = () => form.post('/login')
</script>
