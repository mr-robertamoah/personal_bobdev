<template>
    <div>
        <div
            v-if="profileOwner"
            class=""
        >
            {{profileOwner}}
        </div>
        <div 
            v-else
            class=""
        >   
    
        </div>
        <ProfileHeader :loading="loading" :src="profileOwner?.url" :name="'robert amoah'"></ProfileHeader>

    </div>
</template>

<script setup lang="ts">
import type User from '../../ts/types/User'
import ApiService from '../services/api.service.js'
import { ref, watch } from 'vue'
import useAlert from '../composables/useAlert'
import ProfileHeader from '../components/profile/ProfileHeader.vue'

let emits = defineEmits(['sendProfileOwner', 'sendLoadingState'])
let profileOwner = ref<User|null>(null)
let loading = ref<boolean>(false)
let props = defineProps<{uuid: string}>()
let {setDangerAlertMessage} = useAlert()

watch(profileOwner, ()=>{
    emits('sendProfileOwner', profileOwner)
})

async function setUpCompanyProfile() {
    loading.value = true
    
    if (props.uuid) {
        let response = await ApiService.get(`/company/${props.uuid}`)

        if (response.status != 200) {

            setDangerAlertMessage({message: response.data.message, duration: 3000})
        }

        profileOwner.value = response.data.user
    }
    
    loading.value = false
}

setUpCompanyProfile()

</script>

<style scoped>

</style>