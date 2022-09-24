<template>
    <div class="bg-blue-50 rounded p-2" ref="root">
        <div class="flex w-full justify-center p-5 items-center">
            <slot></slot>
        </div>
        <div class="w-full flex justify-center my-4 items-center" v-if="sliderButtonsNumber > 1">
            <SliderButton v-for="key in sliderButtonsNumber" :key="key"
                @click="toggleActive(key - 1)"
                :active="activeIndex == key - 1"
                class="mx-2"
                :title='getTitle(key)'
            ></SliderButton>
        </div>


    </div>
</template>

<script setup lang="ts">
import { computed, ref, getCurrentInstance, onMounted, watch } from 'vue';
import SliderButton from './SliderButton.vue';

    let activeIndex = ref(0)
    let instance = getCurrentInstance()
    let root = ref<HTMLElement | null>(null)
    let children = ref<HTMLCollection>()
    let sliderButtonsNumber = ref<number>(0)
    let props = defineProps<{
        titles?: Array<string>
    }>()

    onMounted(()=>{
        children.value = root.value?.getElementsByClassName('slider-display')
        
        sliderButtonsNumber.value = 0

        if (children.value) {
            sliderButtonsNumber.value = children.value.length
            updateDisplayClasses()
        }
    })

    watch(activeIndex, ()=>{
        updateDisplayClasses()
    })

    function toggleActive(key: number) {
        activeIndex.value = key
    }

    function updateDisplayClasses() {
        if (!children.value) {
            return
        }
        
        for (let childIndex = 0; childIndex < children.value.length; childIndex++) {
            const element = children.value[childIndex];
            
            if (childIndex != activeIndex.value) {

                hideElement(element)
            }
            
            if (childIndex == activeIndex.value) {

                displayElement(element)
            }
        }
    }

    function displayElement(element: Element) {
        element.removeAttribute('close')
        element.classList.remove('slider-display-hide')
        element.classList.add('slider-display-show')
        element.setAttribute('open', '')
    }

    function hideElement(element: Element) {
        element.removeAttribute('open')
        element.classList.remove('slider-display-show')

        element.setAttribute('close', '')
        element.addEventListener('animationend', ()=>{
            
            element.classList.add('slider-display-hide')
            element.removeAttribute('close')
        }, {once: true})
    }

    function getTitle(defaultTitle: number|string) {
        if (props.titles?.length) {
            return props.titles[Number(defaultTitle) - 1]
        }

        return String(defaultTitle)
    }
    
</script>

<style>

    .slider-display{
        transition: all 1s cubic-bezier(0.86, 0, 0.07, 1);
    }

    .slider-display[open] {
        animation-delay: 1s;
        animation: slide-in 500ms forwards;
    }

    .slider-display[close] {
        position: absolute;
        animation: slide-out 500ms forwards;
    }

    .slider-display-hide{
        display: none;
    }
    
    .slider-display-show{
        display: block;
    }

    @keyframes slide-in {
        0% {
            visibility: hidden;
            opacity: 0.5;
            transform: translateX(-40%);
        }
        
        100% {
            visibility: visible;
            opacity: 1;
            transform: translateX(0%);
        }
    }

    @keyframes slide-out {
        0% {
            visibility: visible;
            opacity: 1;
            transform: translateX(0%);
        }
        
        100% {
            visibility: hidden;
            opacity: 0;
            transform: translateX(100%);
        }
    }
</style>