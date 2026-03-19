<template>
  <div class="min-h-screen bg-white">
    <Navbar />
    <Hero />
    <Features />
    <AuthKits />
    <DemoSection />
    <Installation />
    <Roadmap />
    <Footer />
  </div>
</template>

<script setup>
import Navbar from './components/Navbar.vue'
import Hero from './components/Hero.vue'
import Features from './components/Features.vue'
import AuthKits from './components/AuthKits.vue'
import DemoSection from './components/DemoSection.vue'
import Installation from './components/Installation.vue'
import Roadmap from './components/Roadmap.vue'
import Footer from './components/Footer.vue'
</script>
