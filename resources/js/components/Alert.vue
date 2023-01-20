<template>
    <SlideLeftTransition :duration="alertDuration">
        <div 
            class="fixed top-2 z-50 w-full"
        >
            <div 
                class="sticky text-sm sm:text-base p-2 text-center 
                    rounded mx-auto w-[90%] md:w-[80%] md:mx-[10%] lg:mx-[15%] lg:w-[70%]"
                :class="[
                    alertStatus == 'success' ? 'bg-green-500 text-green-200' : 
                    (alertStatus == 'danger' ? 'bg-red-500 text-red-200' : 'bg-gray-500 text-white')]"
                v-if="alertMessage.length"
            >
                {{alertMessage}}
                <div 
                    v-if="hasAlertIcon && alertMessage.length"
                    class="rounded-full w-6 h-6 sm:w-6 sm:h-6 top-[-5px] p-2 z-50
                        right-[-8px] sm:right-[-12px]
                        border-2 border-slate-100 absolute flex justify-center items-center cursor-pointer"
                    :class="{
                        'bg-green-500 text-green-100' : alertStatus == 'success',
                        'bg-red-500 text-red-100': alertStatus == 'danger'}"
                    @click="clearAlertMessage"
                >
                    <font-awesome-icon :icon="['fa', 'times']"></font-awesome-icon>
                </div>
            </div>
        </div>
    </SlideLeftTransition>
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
        },
        mustClearOutside: {
            type: Boolean,
            default: false
        }
    })

    let emits = defineEmits(['clearAlertMessage'])
    
    watch(()=> props.alertMessage, (newValue) => {
        if (!newValue || props.hasAlertIcon || props.mustClearOutside) {
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