<template>
    <div class="bg-blue-100 relative w-full h-screen overflow-auto">
        <NavBar :close="!!alertData.alertMessage?.length" :navItems="routes"></NavBar>
        <Alert
            @clearAlertMessage="clearAlertMessage" 
            :alertMessage="alertData.alertMessage"
            :alertStatus="alertData.alertStatus"
            :alertDuration="alertData.alertDuration"
            :hasAlertIcon="true"
        ></Alert>
        <router-view 
            :class="{'mt-16 px-2': !['admin', 'profile', 'userProfile', 'companyProfile'].includes($route.name ?? '')}"
        ></router-view>
    </div>
</template>

<script setup lang="ts">
import Routes from '../../ts/Routes'
import {useStore} from 'vuex'
import {onBeforeMount, ref, watch} from 'vue'
import useEmitter from '../composables/useEmitter';
import Alert from './Alert.vue';
import NavBar from './NavBar.vue';
import type AlertDataInterface from '../types/AlertDataInterface';

    let store = useStore()
    let routes = Routes
    let alertData = ref<{
        alertMessage: string,
        alertDuration: number,
        alertStatus: string,
    }>({
        alertDuration: 1000,
        alertMessage: '',
        alertStatus: ''
    })
    let {emitter} = useEmitter()

    emitter.on('updatedAlertData', (data: AlertDataInterface)=>{
        alertData.value.alertDuration = data.alertDuration
        alertData.value.alertStatus = data.alertStatus
        alertData.value.alertMessage = data.alertMessage
    })

    onBeforeMount(()=>{
        store.dispatch('getUser')
    })

    function clearAlertMessage() {
        alertData.value.alertDuration = 1000
        alertData.value.alertStatus =''
        alertData.value.alertMessage =''
    }
            
</script>

<style lang="scss" scoped>

</style>