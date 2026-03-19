<template>
  <footer class="bg-gray-900 text-white py-16 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-12 mb-12">
        <div class="col-span-1 md:col-span-2">
          <div class="flex items-center space-x-2 mb-4">
            <div class="w-8 h-8 bg-primary-600 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
              </svg>
            </div>
            <span class="text-xl font-bold">Laravel Auth Starter Kits</span>
          </div>
          <p class="text-gray-400 mb-6 max-w-md">
            Production-ready authentication solutions for modern Laravel applications. 
            Open source. MIT Licensed.
          </p>
          <div class="flex space-x-4">
            <a href="https://github.com/NanaBright/laravel-auth-starter-kits" 
               target="_blank"
               class="text-gray-400 hover:text-white transition-colors">
              <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                <path fill-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z" clip-rule="evenodd" />
              </svg>
            </a>
          </div>
        </div>
        
        <div>
          <h4 class="text-sm font-semibold uppercase tracking-wider text-gray-400 mb-4">Documentation</h4>
          <ul class="space-y-3">
            <li><a href="#installation" class="text-gray-300 hover:text-white transition-colors">Getting Started</a></li>
            <li><a href="https://github.com/NanaBright/laravel-auth-starter-kits/blob/main/docs/installation.md" target="_blank" class="text-gray-300 hover:text-white transition-colors">Installation Guide</a></li>
            <li><a href="https://github.com/NanaBright/laravel-auth-starter-kits/blob/main/docs/configuration.md" target="_blank" class="text-gray-300 hover:text-white transition-colors">Configuration</a></li>
            <li><a href="https://github.com/NanaBright/laravel-auth-starter-kits/blob/main/docs/api.md" target="_blank" class="text-gray-300 hover:text-white transition-colors">API Reference</a></li>
          </ul>
        </div>
        
        <div>
          <h4 class="text-sm font-semibold uppercase tracking-wider text-gray-400 mb-4">Community</h4>
          <ul class="space-y-3">
            <li><a href="https://github.com/NanaBright/laravel-auth-starter-kits/issues" target="_blank" class="text-gray-300 hover:text-white transition-colors">Report Bug</a></li>
            <li><a href="https://github.com/NanaBright/laravel-auth-starter-kits/issues" target="_blank" class="text-gray-300 hover:text-white transition-colors">Request Feature</a></li>
            <li><a href="https://github.com/NanaBright/laravel-auth-starter-kits/discussions" target="_blank" class="text-gray-300 hover:text-white transition-colors">Discussions</a></li>
            <li><a href="https://github.com/NanaBright/laravel-auth-starter-kits/blob/main/CONTRIBUTING.md" target="_blank" class="text-gray-300 hover:text-white transition-colors">Contributing</a></li>
          </ul>
        </div>
      </div>
      
      <div class="pt-8 border-t border-gray-800 flex flex-col md:flex-row justify-between items-center">
        <p class="text-gray-400 text-sm">
          &copy; {{ new Date().getFullYear() }} Laravel Auth Starter Kits. Made by the community.
        </p>
        <p class="text-gray-500 text-sm mt-4 md:mt-0">
          Released under the MIT License
        </p>
      </div>
    </div>
  </footer>
</template>
