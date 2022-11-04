<template>
    <div class="bg-white z-40 absolute top-0 bottom-0 left-0 right-0">
        <div class="absolute top-0 z-10 m-3 text-blue-600 cursor-pointer hover:text-blue-400" @click="clickedBack">
            <font-awesome-icon :icon="['fa', 'arrow-left-long']"></font-awesome-icon>
        </div>
        <div class="mt-10 bg-blue-100 grid grid-flow-col gap-1 px-4 pb-1">
            <div 
                class="cursor-pointer p-2 mx-1 md:mx-2 hover:bg-gray-200 border-b-2 flex justify-center" 
                :class="[activeHeading == heading ? 'border-gray-400' : 'border-blue-100']"
                v-for="(heading, index) in headings"
                :key="index"
                @click="clickedHeading(heading)"
            >{{heading}}</div>
        </div>
        <div class="p-4">
            <slot></slot>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';


    let props = defineProps({
        headings: {
            type: Array<string>,
            default: []
        }
    })
    let emits = defineEmits(['back'])
    let activeHeading = ref<string>('')

    watch(activeHeading, ()=>{
        makeSectionVisible(activeHeading.value)
    })

    function makeSectionVisible(heading: string)
    {
        makeSectionsInvisible()
        let editSection = document.getElementById(`section${heading}`)

        if (editSection) {
            
            editSection.style.display = 'block'
        }
    }

    function makeSectionsInvisible()
    {
        let editSections = document.getElementsByClassName(`section`)

        for (let index = 0; index < editSections.length; index++) {
            const element = editSections[index];

            element.style.display = 'none'
        }
    }

    function clickedHeading(heading: string)
    {
        activeHeading.value = heading
    }

    function clickedBack()
    {
        emits('back')
    }

    makeSectionsInvisible()

    activeHeading.value = props.headings[0]
</script>