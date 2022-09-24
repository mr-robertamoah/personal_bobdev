<template>
    <slide-left-transition :duration="alertDuration">
        <div 
            class="absolute text-sm sm:text-base p-2 right-1 left-1 sm:right-6 sm:left-4 text-center 
                top-2 z-50 mx-2.5 rounded md:right-[150px] md:left-[150px] lg:right-[200px] lg:left-[200px]"
            :class="[
                alertStatus == 'success' ? 'bg-green-500 text-green-200' : 
                (alertStatus == 'danger' ? 'bg-red-500 text-red-200' : '')]"
            v-if="alertMessage.length"
        >
            {{alertMessage}}
        </div>
    </slide-left-transition>
    <slide-left-transition>
        <div 
            v-if="hasAlertIcon && alertMessage.length"
            class="rounded-full w-6 h-6 top-2 p-2 z-50 right-5 lg:right-[200px]
                md:right-[150px]
                border-2 border-slate-100 absolute flex justify-center items-center cursor-pointer"
            :class="{
                'bg-green-500 text-green-100' : alertStatus == 'success',
                'bg-red-500 text-red-100': alertStatus == 'danger'}"
            @click="clearAlertMessage"
        >
            <font-awesome-icon :icon="['fa', 'times']"></font-awesome-icon>
        </div>
    </slide-left-transition>
</template>

<script setup lang="ts">
import SlideLeftTransition from './transitions/SlideLeftTransition.vue'
import {ref, watch} from 'vue'

    let props = defineProps({
        alertMessage: {
            type: String,
            default:''
        },
        alertStatus: {
            type: String,
            default: ''
        },
        alertDuration: {
            type: Number,
            default: 1000
        },
        hasAlertIcon: {
            type: Boolean,
            default: false
        }
    })

    let emits = defineEmits(['clearAlertMessage'])
    
    watch(()=> props.alertMessage, (newValue) => {
        if (!newValue || props.hasAlertIcon) {
            return
        }

        setTimeout(() => {
            emits('clearAlertMessage')
        }, props.alertDuration ?? 1000);
    })

    function clearAlertMessage(){

        emits('clearAlertMessage')
    }

</script>

<style lang="scss" scoped>

</style>