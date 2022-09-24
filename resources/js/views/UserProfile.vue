<template>
    <div class="overflow-x-scroll">
        <ProfileHeader 
            :loading="loading" 
            :src="profileOwner?.url" 
            :username="profileOwner?.username"
            :name="profileOwner?.name ?? ''"></ProfileHeader>
        <ProfileBody>
            <FoldableCard :cardTitle="'Admin'">

            </FoldableCard>
            <FoldableCard :cardTitle="'Facilitator'">
                <Slider :titles="['about', 'want to be']">
                    <SliderDisplay>
                        <div class="font-semibold text-center text-lg mb-2">About</div>
                        <div class="text-center">This user type will have the ability to set skills and career in order to take up programs for training learners in those fields.</div>
                    </SliderDisplay>
                    <SliderDisplay>
                        <div class="font-semibold text-center text-lg mb-2">Want To be</div>
                        <div class="flex flex-column items-center">
                            <div class="text-justify">
                                The road to becoming a Facilitator starts with clicking the button below. It ends with an interview by and approval from an administrator
                            </div>
                            <SecondaryButton class="mt-2" @click="become('facilitator')">Apply</SecondaryButton>
                        </div>
                    </SliderDisplay>
                </Slider>
            </FoldableCard>
            <FoldableCard :cardTitle="'Parent'">

            </FoldableCard>
            <FoldableCard :cardTitle="'Learner'">

            </FoldableCard>
            <FoldableCard :cardTitle="'Donor'">

            </FoldableCard>
            <FoldableCard :cardTitle="'Organisation'">

            </FoldableCard>
        </ProfileBody>

        <Teleport to="body">
            <Modal :show="showModal"
                @closeModal="closeModal"
            >
                <template v-if="modalType == 'facilitator'">
                    <div class="">this is where everything goes.</div>
                </template>
            </Modal>
        </Teleport>
    </div>
</template>

<script setup lang="ts">
import type User from '../../ts/types/User'
import ApiService from '../services/api.service.js'
import { computed, ref, watch } from 'vue'
import { useStore } from 'vuex'
import useAlert from '../composables/useAlert'
import ProfileHeader from '../components/profile/ProfileHeader.vue'
import FoldableCard from '../components/FoldableCard.vue'
import ProfileBody from '../components/profile/ProfileBody.vue'
import Slider from '../components/slider/Slider.vue'
import SliderDisplay from '../components/slider/SliderDisplay.vue'
import SecondaryButton from '../components/auth/SecondaryButton.vue'
import Modal from '../components/Modal.vue'

let store = useStore()
let profileOwner = ref<User|null>(null)
let loading = ref<boolean>(false)
let props = defineProps<{username: string}>()
let emits = defineEmits(['sendProfileOwner'])
let user = computed(() => store.state.user.user)
let {setDangerAlertMessage} = useAlert()
let showModal = ref<boolean>(false)
let modalType = ref<string|null>(null)

watch(profileOwner, ()=>{
    emits('sendProfileOwner', profileOwner)
})

async function setUpUserProfile() {
    loading.value = true
    
    if (user.value?.username != props.username) {
        let response = await ApiService.get(`/user/${props.username}`)

        if (response.status != 200) {

            setDangerAlertMessage({message: response.data.message, duration: 3000})
        }

        profileOwner.value = response.data.user
    }

    if (user.value?.username == props.username) {
        profileOwner.value = user.value

    }
    
    loading.value = false
}

setUpUserProfile()

function become(type: string) {
    modalType.value = type 
    switch (type) {
        case 'facilitator':
            
            break;
    
        default:
            break;
    }

    showModal.value = true
}

function closeModal() {
    showModal.value = false
}
</script>

<style scoped>

</style>