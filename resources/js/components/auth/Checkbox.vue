<template>
    <div 
        class="align-middle border-blue-600 cursor-pointer flex items-center mt-4 pr-2 py-1 rounded
             max-w-400 mx-auto"
        @click="clickedCheckBox"
    >
        <div
            class="bg-white mr-2 text-center w-5 rounded text-sm"
        >
            <font-awesome-icon 
                class=""
                :class="[boxIcon == 'check' ? 'text-green-500' : 'text-red-500']"
                :icon="['fa', boxIcon]"
                v-if="hasTimes || boxIcon == 'check'"
            ></font-awesome-icon>
            <div v-if="!hasTimes && boxIcon != 'check'" class="bg-blue-200 h-3 m-1 shadow-inner w-3"></div>
        </div>
        <div 
            class="text-gray-500 text-sm"
        >
            {{label}}
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onBeforeMount } from 'vue'
import useGenerateId from '../../composables/useGenerateId'
import useElementProps from '../../composables/useElementProps'
import useOtherElementProps from '../../composables/useOtherElementProps'

let props = defineProps({
    ...useOtherElementProps(),
    ...useElementProps(),
    modelValue: {
        type: Boolean,
        default: false
    },
    hasTimes: {
        type: Boolean,
        default: true
    },
})
    
let emit = defineEmits(['update:modelValue'])
let {id} = useGenerateId()
let boxIcon = ref('check')

onBeforeMount(()=>{
    boxIcon.value = props.modelValue ? 'check' : 'times'
})

function switchBoxIcon() {
    boxIcon.value = boxIcon.value == 'check' ? 'times' : 'check'

    return boxIcon.value == 'check' ? true : false
}

function clickedCheckBox() {
    emit('update:modelValue', switchBoxIcon())
}
    
</script>

<style scoped>

</style>