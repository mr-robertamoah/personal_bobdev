<template>
    <!-- shadow rounded sm:shadow-none sm:border-t-2 bg-slate-50 flex flex-wrap md:border-gray-600 md:grid-cols-4 
        md:grid text-sm md:text-base md:flex-nowrap -->
    <div class="rounded sm:rounded-none flex justify-between flex-wrap p-2 sm:p-0 shadow items-center text-sm bg-slate-50 sm:shadow-none sm:border-t-2 sm:border-gray-600 sm:grid-cols-3 
        sm:grid sm:text-base cursor-pointer"
        @dblclick="toggleShowUserTypes"
    >
        <!-- flex-shrink-0 w-1/2 md:w-1/3 overflow-ellipsis md:border-r md:border-gray-600 p-2 text-center -->
        
        <div class="mr-2 sm:m-0 flex items-center justify-center p-1
            sm:border-gray-600 text-center rounded-b-sm"
        >
            <DP class='h-6 w-6 sm:h-8 sm:w-8 mr-2 bg-slate-300' size="small" :defaultIcon="['fas', 'user-circle']"></DP>
            <div class="">
                <span class="overflow-ellipsis overflow-hidden">{{user.username}}</span>
            </div>
        </div>
        <div class="flex items-center justify-center p-1 sm:border-x sm:border-gray-600 
            text-center">
                <span class="overflow-ellipsis overflow-hidden">{{user.name}}</span>
            </div>
        <!-- w-full sm:w-1/3 p-2 text-center -->
        <div class="w-full flex-shrink-0 sm:w-auto flex items-center justify-center p-1 text-center">
            <span class=" overflow-ellipsis overflow-hidden">{{user.email}}</span>
        </div>
    </div>
    <SlideDownTransition>
        <div class="bg-slate-50 w-9/12 mx-auto my-2 p-2" v-if="showUserTypes">
            <div 
                :class="[user.userTypes?.length ? 'text-start' : 'text-center']"
                class="ml-2 mb-2 font-bold text-sm text-slate-600">{{user.userTypes?.length ? 'user types': 'has no user types'}}</div>
            <div
                class="flex justify-center items-center flex-wrap">
                <div v-for="(userType, index) in user.userTypes" 
                    class="p-1 text-sm mx-2 rounded transition-colors cursor-pointer" :key="index"
                    :class="[['super admin', 'admin'].includes(userType.usableName ?? '') ? 
                    'hover:bg-pink-700 hover:text-white bg-pink-900 text-pink-100' : 'hover:bg-slate-700 hover:text-white bg-slate-900 text-slate-100']"
                >
                    {{userType.usableName}}
                </div>
            </div>
        </div>
    </SlideDownTransition>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import type User from '../../../ts/types/User'
import DP from '../profile/DP.vue'
import SlideDownTransition from '../transitions/SlideDownTransition.vue'
let props = defineProps<{user: User}>()
let showUserTypes = ref<Boolean>(false)

function toggleShowUserTypes(){
    showUserTypes.value = !showUserTypes.value
}

</script>

<style lang="scss" scoped>

</style>