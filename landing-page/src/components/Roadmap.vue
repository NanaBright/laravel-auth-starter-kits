<template>
  <section class="py-20 px-4 sm:px-6 lg:px-8 bg-gray-50">
    <div class="max-w-7xl mx-auto">
      <div class="text-center mb-16">
        <h2 class="section-title mb-4">Roadmap</h2>
        <p class="section-subtitle">
          What we're working on and what's planned for the future.
        </p>
      </div>
      
      <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <div v-for="phase in roadmap" :key="phase.title" class="card">
          <div class="flex items-center gap-3 mb-6">
            <span :class="[
              'px-3 py-1 rounded-full text-xs font-medium',
              phase.status === 'completed' ? 'bg-green-100 text-green-700' :
              phase.status === 'current' ? 'bg-primary-100 text-primary-700' :
              'bg-gray-100 text-gray-600'
            ]">
              {{ phase.label }}
            </span>
          </div>
          
          <h3 class="text-xl font-bold text-gray-900 mb-2">{{ phase.title }}</h3>
          <p class="text-sm text-gray-500 mb-4">{{ phase.timeline }}</p>
          
          <ul class="space-y-3">
            <li v-for="item in phase.items" :key="item.name" class="flex items-center gap-3">
              <span :class="[
                'flex-shrink-0 w-5 h-5 rounded-full flex items-center justify-center',
                item.done ? 'bg-green-100' : 'bg-gray-100'
              ]">
                <svg v-if="item.done" class="w-3 h-3 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
                <span v-else class="w-2 h-2 bg-gray-300 rounded-full"></span>
              </span>
              <span :class="item.done ? 'text-gray-600' : 'text-gray-900'">{{ item.name }}</span>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
const roadmap = [
  {
    title: 'Phase 1',
    label: 'Completed',
    status: 'completed',
    timeline: 'Q3 2025',
    items: [
      { name: 'Phone/SMS Authentication Kit', done: true },
      { name: 'Email Authentication Kit', done: true },
      { name: 'Magic link implementation', done: true },
      { name: 'Comprehensive documentation', done: true },
      { name: 'Multiple SMS gateway support', done: true },
      { name: 'Production deployment guides', done: true }
    ]
  },
  {
    title: 'Phase 2',
    label: 'In Progress',
    status: 'current',
    timeline: 'Q4 2025 - Q1 2026',
    items: [
      { name: 'Multi-channel OTP Kit', done: false },
      { name: 'Social Authentication Kit', done: false },
      { name: 'Two-Factor Authentication Kit', done: false },
      { name: 'Admin dashboard', done: false },
      { name: 'Docker containerization', done: false },
      { name: 'Multi-language support', done: false }
    ]
  },
  {
    title: 'Phase 3',
    label: 'Planned',
    status: 'planned',
    timeline: '2026',
    items: [
      { name: 'Passwordless Authentication Kit', done: false },
      { name: 'Biometric Authentication Kit', done: false },
      { name: 'Enterprise features', done: false },
      { name: 'Advanced analytics', done: false },
      { name: 'Kubernetes deployment', done: false },
      { name: 'Plugin marketplace', done: false }
    ]
  }
]
</script>
