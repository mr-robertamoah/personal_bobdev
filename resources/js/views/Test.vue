<template>
    <div class="p-9">
        <button @click="show = !show">login in</button>
        
        <LoginModal :show="show" @close-login-modal="show = !show"></LoginModal>
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import LoginModal from '../components/LoginModal.vue';
import Slider from '../components/slider/Slider.vue'
import SliderDisplay from '../components/slider/SliderDisplay.vue'

let show = ref(false)

</script>

<style scoped>

</style>