<template>
    <Button :id="localId" :type="type" 
        class="bg-gradient-to-tr border border-gray-400 duration-100 from-blue-600 
            hover:to-red-300 hover:from-blue-400 hover:scale-x-105 
            p-2 rounded shadow text-white to-red-400 transition-all outline-none
            focus:from-blue-600 focus:to-red-300 focus:border-teal-600
            disabled:bg-red-200 hover:disabled:bg-gray-100
            "
        :disabled="disabled"
    >
        <slot></slot>
    </Button>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { getRandomNumber } from '../../helper';
import ButtonMixin from '../../mixins/ButtonMixin'
import Button from '../Button.vue'

    let props = defineProps({
        type: {
            type: String,
            default: ''
        },
        disabled: {
            type: Boolean,
            default: false
        },
        id: {
            type: String,
            default: ''
        },
    })

    let localId = ref<String|null>(null)

    function createId() {
        
        localId.value = `input${props.id ? props.id : getRandomNumber(1000000)}`
    }

    createId()
    
</script>