<template>
  <section id="installation" class="py-20 px-4 sm:px-6 lg:px-8 bg-gray-900 text-white">
    <div class="max-w-4xl mx-auto">
      <div class="text-center mb-12">
        <h2 class="text-3xl md:text-4xl font-bold mb-4">Get Started in Minutes</h2>
        <p class="text-lg text-gray-400">
          Clone the repository and start building with authentication out of the box.
        </p>
      </div>
      
      <div class="bg-gray-800 rounded-2xl overflow-hidden">
        <div class="flex border-b border-gray-700">
          <button 
            v-for="kit in ['phone-auth', 'email-auth']" 
            :key="kit"
            @click="selectedKit = kit"
            :class="[
              'flex-1 px-6 py-4 text-sm font-medium transition-colors',
              selectedKit === kit 
                ? 'bg-gray-700 text-white' 
                : 'text-gray-400 hover:text-white hover:bg-gray-700/50'
            ]"
          >
            {{ kit === 'phone-auth' ? 'Phone Auth' : 'Email Auth' }}
          </button>
        </div>
        
        <div class="p-6">
          <div class="space-y-4">
            <div v-for="(step, index) in installationSteps" :key="index" class="group">
              <div class="flex items-start gap-4">
                <span class="flex-shrink-0 w-8 h-8 bg-primary-600 text-white text-sm font-bold rounded-full flex items-center justify-center">
                  {{ index + 1 }}
                </span>
                <div class="flex-1">
                  <p class="text-gray-300 text-sm mb-2">{{ step.description }}</p>
                  <div class="bg-gray-950 rounded-lg p-4 relative group">
                    <pre class="text-sm text-gray-300 overflow-x-auto"><code>{{ step.command }}</code></pre>
                    <button 
                      @click="copyCommand(step.command)"
                      class="absolute top-2 right-2 p-2 text-gray-500 hover:text-white opacity-0 group-hover:opacity-100 transition-opacity"
                    >
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                      </svg>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <div class="mt-8 pt-6 border-t border-gray-700">
            <p class="text-gray-400 text-sm mb-4">
              Visit <code class="bg-gray-950 px-2 py-1 rounded text-primary-400">http://localhost:8000</code> and start building!
            </p>
            <div class="flex flex-col sm:flex-row gap-4">
              <a 
                :href="`https://github.com/NanaBright/laravel-auth-starter-kits/tree/main/${selectedKit}`"
                target="_blank"
                class="btn-primary"
              >
                View on GitHub
                <svg class="w-5 h-5 ml-2" fill="currentColor" viewBox="0 0 24 24">
                  <path fill-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z" clip-rule="evenodd" />
                </svg>
              </a>
              <a 
                href="https://github.com/NanaBright/laravel-auth-starter-kits/blob/main/docs/installation.md"
                target="_blank"
                class="btn-secondary border-gray-600 text-gray-300 hover:bg-gray-700"
              >
                Full Documentation
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, computed } from 'vue'

const selectedKit = ref('phone-auth')

const installationSteps = computed(() => [
  {
    description: 'Clone the repository',
    command: `git clone https://github.com/NanaBright/laravel-auth-starter-kits.git
cd laravel-auth-starter-kits/${selectedKit.value}`
  },
  {
    description: 'Install dependencies',
    command: `composer install
npm install`
  },
  {
    description: 'Configure environment',
    command: `cp .env.example .env
php artisan key:generate`
  },
  {
    description: 'Setup database and start servers',
    command: `php artisan migrate
php artisan serve &
npm run dev`
  }
])

const copyCommand = async (command) => {
  try {
    await navigator.clipboard.writeText(command)
  } catch (err) {
    console.error('Failed to copy:', err)
  }
}
</script>
