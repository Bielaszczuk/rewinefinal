<script setup lang="ts">
import { ref, computed } from 'vue'
import { useI18n } from 'vue-i18n'
import BaseButton from '@components/common/BaseButton.vue'
import BaseInput from '@components/common/BaseInput.vue'

const { t } = useI18n()

const form = ref({
  name: '',
  email: '',
  subject: '',
  message: ''
})

const isSubmitting = ref(false)
const submitSuccess = ref(false)
const submitError = ref('')

const subjects = computed(() => [
  { value: 'general', label: t('contact.subjects.general') },
  { value: 'support', label: t('contact.subjects.support') },
  { value: 'partnership', label: t('contact.subjects.partnership') },
  { value: 'feedback', label: t('contact.subjects.feedback') },
  { value: 'other', label: t('contact.subjects.other') }
])

const contactInfo = computed(() => [
  {
    icon: 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
    title: t('contact.info.email'),
    value: 'contacto@rewine.com',
    link: 'mailto:contacto@rewine.com'
  },
  {
    icon: 'M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z',
    title: t('contact.info.phone'),
    value: '+54 261 123 4567',
    link: 'tel:+542611234567'
  },
  {
    icon: 'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z M15 11a3 3 0 11-6 0 3 3 0 016 0z',
    title: t('contact.info.address'),
    value: 'Mendoza, Argentina',
    link: null
  }
])

const socialLinks = [
  { name: 'Facebook', icon: 'M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z', url: '#' },
  { name: 'Instagram', icon: 'M12.315 2c2.43 0 2.784.013 3.808.06 1.064.049 1.791.218 2.427.465a4.902 4.902 0 011.772 1.153 4.902 4.902 0 011.153 1.772c.247.636.416 1.363.465 2.427.048 1.067.06 1.407.06 4.123v.08c0 2.643-.012 2.987-.06 4.043-.049 1.064-.218 1.791-.465 2.427a4.902 4.902 0 01-1.153 1.772 4.902 4.902 0 01-1.772 1.153c-.636.247-1.363.416-2.427.465-1.067.048-1.407.06-4.123.06h-.08c-2.643 0-2.987-.012-4.043-.06-1.064-.049-1.791-.218-2.427-.465a4.902 4.902 0 01-1.772-1.153 4.902 4.902 0 01-1.153-1.772c-.247-.636-.416-1.363-.465-2.427-.047-1.024-.06-1.379-.06-3.808v-.63c0-2.43.013-2.784.06-3.808.049-1.064.218-1.791.465-2.427a4.902 4.902 0 011.153-1.772A4.902 4.902 0 015.45 2.525c.636-.247 1.363-.416 2.427-.465C8.901 2.013 9.256 2 11.685 2h.63zm-.081 1.802h-.468c-2.456 0-2.784.011-3.807.058-.975.045-1.504.207-1.857.344-.467.182-.8.398-1.15.748-.35.35-.566.683-.748 1.15-.137.353-.3.882-.344 1.857-.047 1.023-.058 1.351-.058 3.807v.468c0 2.456.011 2.784.058 3.807.045.975.207 1.504.344 1.857.182.466.399.8.748 1.15.35.35.683.566 1.15.748.353.137.882.3 1.857.344 1.054.048 1.37.058 4.041.058h.08c2.597 0 2.917-.01 3.96-.058.976-.045 1.505-.207 1.858-.344.466-.182.8-.398 1.15-.748.35-.35.566-.683.748-1.15.137-.353.3-.882.344-1.857.048-1.055.058-1.37.058-4.041v-.08c0-2.597-.01-2.917-.058-3.96-.045-.976-.207-1.505-.344-1.858a3.097 3.097 0 00-.748-1.15 3.098 3.098 0 00-1.15-.748c-.353-.137-.882-.3-1.857-.344-1.023-.047-1.351-.058-3.807-.058zM12 6.865a5.135 5.135 0 110 10.27 5.135 5.135 0 010-10.27zm0 1.802a3.333 3.333 0 100 6.666 3.333 3.333 0 000-6.666zm5.338-3.205a1.2 1.2 0 110 2.4 1.2 1.2 0 010-2.4z', url: '#' },
  { name: 'Twitter', icon: 'M8.29 20.251c7.547 0 11.675-6.253 11.675-11.675 0-.178 0-.355-.012-.53A8.348 8.348 0 0022 5.92a8.19 8.19 0 01-2.357.646 4.118 4.118 0 001.804-2.27 8.224 8.224 0 01-2.605.996 4.107 4.107 0 00-6.993 3.743 11.65 11.65 0 01-8.457-4.287 4.106 4.106 0 001.27 5.477A4.072 4.072 0 012.8 9.713v.052a4.105 4.105 0 003.292 4.022 4.095 4.095 0 01-1.853.07 4.108 4.108 0 003.834 2.85A8.233 8.233 0 012 18.407a11.616 11.616 0 006.29 1.84', url: '#' }
]

async function handleSubmit() {
  isSubmitting.value = true
  submitError.value = ''
  submitSuccess.value = false

  try {
    await new Promise(resolve => setTimeout(resolve, 1500))
    submitSuccess.value = true
    form.value = { name: '', email: '', subject: '', message: '' }
  } catch {
    submitError.value = t('contact.form.error')
  } finally {
    isSubmitting.value = false
  }
}
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <section class="relative bg-wine-900 text-white py-20">
      <div class="absolute inset-0 opacity-20">
        <img
          src="/images/winery/wineyard.jpg"
          alt=""
          class="w-full h-full object-cover"
        />
      </div>
      <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-4xl md:text-5xl font-bold mb-6">
          {{ t('contact.title') }}
        </h1>
        <p class="text-xl text-wine-200 max-w-3xl mx-auto">
          {{ t('contact.subtitle') }}
        </p>
      </div>
    </section>

    <section class="py-16">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-3 gap-12">
          <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-md p-8">
              <h2 class="text-2xl font-bold text-gray-900 mb-6">
                {{ t('contact.form.title') }}
              </h2>

              <div
                v-if="submitSuccess"
                class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg"
              >
                <div class="flex items-center">
                  <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  </svg>
                  <p class="text-green-700">{{ t('contact.form.success') }}</p>
                </div>
              </div>

              <div
                v-if="submitError"
                class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg"
              >
                <div class="flex items-center">
                  <svg class="w-5 h-5 text-red-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                  <p class="text-red-700">{{ submitError }}</p>
                </div>
              </div>

              <form @submit.prevent="handleSubmit" class="space-y-6">
                <div class="grid md:grid-cols-2 gap-6">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                      {{ t('contact.form.name') }} *
                    </label>
                    <BaseInput
                      v-model="form.name"
                      type="text"
                      :placeholder="t('contact.form.namePlaceholder')"
                      required
                    />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                      {{ t('contact.form.email') }} *
                    </label>
                    <BaseInput
                      v-model="form.email"
                      type="email"
                      :placeholder="t('contact.form.emailPlaceholder')"
                      required
                    />
                  </div>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    {{ t('contact.form.subject') }} *
                  </label>
                  <select
                    v-model="form.subject"
                    required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-wine-500 focus:border-wine-500"
                  >
                    <option value="" disabled>{{ t('contact.form.subjectPlaceholder') }}</option>
                    <option
                      v-for="subject in subjects"
                      :key="subject.value"
                      :value="subject.value"
                    >
                      {{ subject.label }}
                    </option>
                  </select>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    {{ t('contact.form.message') }} *
                  </label>
                  <textarea
                    v-model="form.message"
                    rows="5"
                    required
                    :placeholder="t('contact.form.messagePlaceholder')"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-wine-500 focus:border-wine-500 resize-none"
                  ></textarea>
                </div>

                <BaseButton
                  type="submit"
                  :disabled="isSubmitting"
                  class="w-full md:w-auto"
                >
                  <svg
                    v-if="isSubmitting"
                    class="animate-spin -ml-1 mr-2 h-4 w-4"
                    fill="none"
                    viewBox="0 0 24 24"
                  >
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  {{ isSubmitting ? t('common.loading') : t('contact.form.submit') }}
                </BaseButton>
              </form>
            </div>
          </div>

          <div class="space-y-8">
            <div class="bg-white rounded-lg shadow-md p-8">
              <h2 class="text-2xl font-bold text-gray-900 mb-6">
                {{ t('contact.info.title') }}
              </h2>
              <div class="space-y-6">
                <div
                  v-for="info in contactInfo"
                  :key="info.title"
                  class="flex items-start"
                >
                  <div class="w-10 h-10 bg-wine-100 rounded-full flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-wine-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="info.icon" />
                    </svg>
                  </div>
                  <div class="ml-4">
                    <p class="text-sm text-gray-500">{{ info.title }}</p>
                    <a
                      v-if="info.link"
                      :href="info.link"
                      class="text-gray-900 hover:text-wine-600 transition-colors"
                    >
                      {{ info.value }}
                    </a>
                    <p v-else class="text-gray-900">{{ info.value }}</p>
                  </div>
                </div>
              </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-8">
              <h2 class="text-2xl font-bold text-gray-900 mb-6">
                {{ t('contact.social.title') }}
              </h2>
              <div class="flex space-x-4">
                <a
                  v-for="social in socialLinks"
                  :key="social.name"
                  :href="social.url"
                  :title="social.name"
                  class="w-10 h-10 bg-wine-100 rounded-full flex items-center justify-center hover:bg-wine-200 transition-colors"
                >
                  <svg class="w-5 h-5 text-wine-600" fill="currentColor" viewBox="0 0 24 24">
                    <path :d="social.icon" />
                  </svg>
                </a>
              </div>
            </div>

            <div class="bg-wine-50 rounded-lg p-8">
              <h3 class="text-lg font-semibold text-gray-900 mb-2">
                {{ t('contact.hours.title') }}
              </h3>
              <div class="text-gray-600 space-y-1">
                <p>{{ t('contact.hours.weekdays') }}: 9:00 - 18:00</p>
                <p>{{ t('contact.hours.saturday') }}: 10:00 - 14:00</p>
                <p>{{ t('contact.hours.sunday') }}: {{ t('contact.hours.closed') }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="py-16 bg-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-8 text-center">
          {{ t('contact.faq.title') }}
        </h2>
        <div class="grid md:grid-cols-2 gap-6 max-w-4xl mx-auto">
          <div class="bg-gray-50 rounded-lg p-6">
            <h3 class="font-semibold text-gray-900 mb-2">{{ t('contact.faq.q1') }}</h3>
            <p class="text-gray-600">{{ t('contact.faq.a1') }}</p>
          </div>
          <div class="bg-gray-50 rounded-lg p-6">
            <h3 class="font-semibold text-gray-900 mb-2">{{ t('contact.faq.q2') }}</h3>
            <p class="text-gray-600">{{ t('contact.faq.a2') }}</p>
          </div>
          <div class="bg-gray-50 rounded-lg p-6">
            <h3 class="font-semibold text-gray-900 mb-2">{{ t('contact.faq.q3') }}</h3>
            <p class="text-gray-600">{{ t('contact.faq.a3') }}</p>
          </div>
          <div class="bg-gray-50 rounded-lg p-6">
            <h3 class="font-semibold text-gray-900 mb-2">{{ t('contact.faq.q4') }}</h3>
            <p class="text-gray-600">{{ t('contact.faq.a4') }}</p>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

