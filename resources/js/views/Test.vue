<template>
    <div class="p-9">
    
        <Slider>
            <SliderDisplay class="">hey</SliderDisplay>
            <SliderDisplay class="">sup</SliderDisplay>
        </Slider>
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import Slider from '../components/slider/Slider.vue'
import SliderDisplay from '../components/slider/SliderDisplay.vue'

let show = ref(false)

</script>

<style scoped>

</style>