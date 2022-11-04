<template>
    <div class="overflow-hidden">
        <div v-if="user.username == profileOwner?.username" 
            class="absolute top-0 z-10 m-3 text-blue-600 cursor-pointer hover:text-blue-400" 
            @click="data.showEdit = true">
            <font-awesome-icon :icon="['fa', 'pencil']"></font-awesome-icon>
        </div>
        <ProfileEdit 
            v-if="data.showEdit" 
            @back="data.showEdit = false"
            :headings="[
                'user', 'facilitator', 'parent', 'student', 'donor'
            ]"
        >
            <Alert
                :hasAlertIcon="false"
                :alertMessage="alertMessage"
                :mustClearOutside="true"
            ></Alert>
            <EditSection id="user">
                <div class="font-bold text-base mb-4 text-center capitalize">profile</div>
                <div class="text-sm mb-2">user info</div>
                <Input
                    :placeholder="'first name'" v-model="data.user.firstName" :errors="errors?.user.firstName"
                ></Input>
                <Input
                    :placeholder="'surname'" v-model="data.user.surname" :errors="errors?.user.surname"
                ></Input>
                <Input
                    :placeholder="'other names'" v-model="data.user.otherNames" :errors="errors?.user.otherNames"
                ></Input>
                <Input
                    :placeholder="'email'" v-model="data.user.email" :errors="errors?.user.email"
                ></Input>
                <div class="flex justify-center">
                <PrimaryButton class="mt-2 p-1 text-sm" 
                    v-if="computedHasDataToEditInfo"
                    @click="clickedEditInfo('edit info')"
                >edit profile</PrimaryButton>
                </div>
                <div class="text-sm mb-2 mt-4">reset password</div>
                <Input
                    :placeholder="'current password'" v-model="data.user.currentPassword" :errors="errors?.user.currentPassword"
                ></Input>
                <Input
                    :placeholder="'password'" v-model="data.user.password" :errors="errors?.user.password"
                    :type="'password'"
                ></Input>
                <Input
                    v-if="data.user.passwordConfirmation"
                    :type="'password'"
                    :placeholder="'password confirmation'" v-model="data.user.passwordConfirmation" :errors="errors?.user.passwordConfirmation"
                ></Input>
                <div class="flex justify-center">
                    <PrimaryButton class="mt-2 p-1 text-sm grid " 
                        v-if="computedHasDataToResetPassword"
                        @click="clickedEditInfo('reset password')"
                    >reset password</PrimaryButton>
                </div>
            </EditSection>
            <EditSection id="facilitator">
                <div class="font-bold text-base mb-4 text-center capitalize">job</div>
                <div class="text-sm mb-2">create a job</div>
                <Input
                    :placeholder="'name'" v-model="data.job.name" :errors="errors?.job.name" :info="info.job.name"
                ></Input>
                <Input
                    :placeholder="'description'" v-model="data.job.description" :errors="errors?.job.description" :info="info.job.description"
                ></Input>
                <SecondaryButton class="mt-2 p-1 text-sm w-content mx-auto" 
                    v-if="data.job.name?.length"
                    @click="createAndAttachJob"
                >create and attach</SecondaryButton>
                <div class="relative">
                    <Input
                        :type="'search'" :placeholder="`search for job`" v-model="data.searchText" :errors="errors?.job.description" :info="info.job.description"
                    ></Input>
                    <div class="absolute z-10 w-[90%] max-h-20 overflow-y-auto p-2 rounded bg-blue-100
                        flex justify-center flex-col" 
                        v-if="searchData.data.length"
                    >
                        <div class="bg-white my-2 text-center rounded text-sm cursor-pointer" 
                            v-for="job in (searchData.data as Array<Job>)"
                            :key="job.id"
                            @click="attachJob(job)"
                        >{{job.name}}</div>
                        <PrimaryButton class="mt-2 p-1 text-sm" 
                            v-if="hasMoreData"
                            @click="debouncedGetMoreData({
                                searchText: data.searchText,
                                url: '/jobs',
                            })"
                        >get more</PrimaryButton>
                    </div>
                </div>
                <div class="flex justify-center mt-4" v-if="data.job.actionButtonText">
                    <SecondaryButton class="text-sm" 
                        :disabled="true" 
                        @click="changeStep('previous')">{{data.job.actionButtonText}}</SecondaryButton>
                </div>
            </EditSection>
            <EditSection id="parent">
                this is for parents
            </EditSection>
            <EditSection id="student">
                this is for students
            </EditSection>
            <EditSection id="donor">
                this is for donors
            </EditSection>
        </ProfileEdit>
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
                        <div class="flex flex-col items-center">
                            <div class="text-justify">
                                The road to becoming a Facilitator starts with clicking the button below. It ends with an interview by and approval from an administrator
                            </div>
                            <SecondaryButton class="mt-4" @click="become('facilitator')">Apply</SecondaryButton>
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
                :bg="'bg-blue-200'"
            >
                <template v-if="modalType == 'facilitator'">
                    
                    <Form 
                        v-if="steps.facilitator.step" 
                        :title="'Become A Facilitator'"
                        class="flex flex-col justify-center"
                    >
                        <div class="font-bold text-base mb-4 text-center capitalize">add a job</div>
                        <div class="text-sm mb-2">create a job</div>
                        <Input
                            :label="'name'" v-model="data.job.name" :errors="errors?.job.name" :info="info.job.name"
                        ></Input>
                        <Input
                            :label="'description'" v-model="data.job.description" :errors="errors?.job.description" :info="info.job.description"
                        ></Input>
                        <PrimaryButton class="mt-2 p-1 text-sm w-content mx-auto" 
                            v-if="data.job.name?.length"
                            @click="createAndAttachJob"
                        >create and attach</PrimaryButton>
                        <div class="font-bold text-base mb-4 mt-2 text-center capitalize">search and add a job to your jobs</div>
                        <div class="relative">
                            <Input
                                :type="'search'" :placeholder="`search for job`" v-model="data.searchText" :errors="errors?.job.description" :info="info.job.description"
                            ></Input>
                            <div class="absolute z-10 w-[90%] max-h-20 overflow-y-auto p-2 rounded bg-blue-100
                                flex justify-center flex-col" 
                                v-if="searchData.data.length"
                            >
                                <div class="bg-white my-2 text-center rounded text-sm cursor-pointer" 
                                    v-for="job in (searchData.data as Array<Job>)"
                                    :key="job.id"
                                    @click="attachJob(job)"
                                >{{job.name}}</div>
                                <PrimaryButton class="mt-2 p-1 text-sm" 
                                    v-if="hasMoreData"
                                    @click="debouncedGetMoreData({
                                        searchText: data.searchText,
                                        url: '/jobs',
                                    })"
                                >get more</PrimaryButton>
                            </div>
                        </div>
                        <div class="flex justify-center mt-4" v-if="data.job.actionButtonText">
                            <SecondaryButton class="text-sm" 
                                :disabled="true" 
                                @click="changeStep('previous')">{{data.job.actionButtonText}}</SecondaryButton>
                        </div>
                        <div class="flex justify-between mt-4">
                            <SecondaryButton class="text-sm" :disabled="true" @click="changeStep('previous')">Previous</SecondaryButton>
                            <SecondaryButton class="text-sm" @click="changeStep('next')">Next</SecondaryButton>
                        </div>
                    </Form>
                </template>
            </Modal>
        </Teleport>
    </div>
</template>

<script setup lang="ts">
import type User from '../../ts/types/User'
import type Job from '../../ts/types/Job'
import ApiService from '../services/api.service.js'
import { computed, ref, watch, reactive } from 'vue'
import { useStore } from 'vuex'
import useAlert from '../composables/useAlert'
import ProfileHeader from '../components/profile/ProfileHeader.vue'
import FoldableCard from '../components/FoldableCard.vue'
import ProfileBody from '../components/profile/ProfileBody.vue'
import Slider from '../components/slider/Slider.vue'
import SliderDisplay from '../components/slider/SliderDisplay.vue'
import SecondaryButton from '../components/auth/SecondaryButton.vue'
import Modal from '../components/Modal.vue'
import Form from '../components/Form.vue'
import Input from '../components/auth/Input.vue'
import useValidationErrors from '../composables/useValidationErrors'
import useGetPaginatedData from '../composables/useGetPaginatedData'
import PrimaryButton from '../components/auth/PrimaryButton.vue'
import useJobs from '../composables/useJobs'
import EditSection from '../components/profile/EditSection.vue'
import ProfileEdit from '../components/profile/ProfileEdit.vue'
import useEditUserInfo from '../composables/useEditUserInfo'
import Alert from '../components/Alert.vue'

let store = useStore()
let profileOwner = ref<User|null>(null)
let loading = ref<boolean>(false)
let props = defineProps<{username: string}>()
let emits = defineEmits(['sendProfileOwner'])
let user = computed(() => store.state.user.user)
let showModal = ref<boolean>(false)
let modalType = ref<string|null>(null)
let {createAndAttachJob} = useJobs()
let {errors, setErrors, errorWatchEffects, hasErrors} = useValidationErrors()
let {setDangerAlertMessage} = useAlert()
let {searchData, debouncedGetData, debouncedGetMoreData, hasMoreData} = useGetPaginatedData()
let steps = reactive({
    facilitator: {
        step: 0
    }
})
let data = reactive({
    showEdit: false,
    job: {
        name: '',
        description: '',
        actionButtonText: '',
    },
    user: {
        firstName: '',
        surname: '',
        otherNames: '',
        email: '',
        gender: '',
        password: '',
        currentPassword: '',
        passwordConfirmation: '',
    },
    searchText: '',
    searchType: '',
})
let info = reactive({
    job: {
        name: ['the name of the job you', 'this is required'],
        description: ['how you would want to describe the job'],
    }
})
let alertMessage = ref<string>('')

watch(profileOwner, ()=>{
    emits('sendProfileOwner', profileOwner)
})

watch(()=> data.searchText, () =>{
    
    debouncedGetData({
        searchText: data.searchText,
        url: '/jobs',
    })
})

watch(()=> data.searchType, () =>{
    data.searchText = ''
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
let {editInfo, editableUser, editableUserLoading, resetPassword} = useEditUserInfo(profileOwner.value)

watch(editableUser, ()=>{
    if (!profileOwner.value) {
        return
    }

    profileOwner.value.firstName = editableUser.value.firstName
    profileOwner.value.surname = editableUser.value.surname
    profileOwner.value.otherNames = editableUser.value.otherNames
    profileOwner.value.gender = editableUser.value.gender
    profileOwner.value.email = editableUser.value.email
})

function changeStep(type: string)
{
    if (type == 'next') {
        steps.facilitator.step = steps.facilitator.step++
    }
    
    if (type == 'previous') {
        steps.facilitator.step = steps.facilitator.step--
    }
}

let computedHasDataToEditInfo = computed<boolean>(()=>{
    return Boolean(
        data.user.email || data.user.firstName || data.user.surname || data.user.otherNames ||
        data.user.gender
    )
})

let computedHasDataToResetPassword = computed<boolean>(()=>{
    return Boolean(
        data.user.password ||
        data.user.passwordConfirmation
    )
})

watch(editableUserLoading, (newValue)=>{
    if (newValue) {
        return
    }

    alertMessage.value = ''
})

function clickedEditInfo(type: string)
{
    if (!profileOwner.value?.username) {
        return
    }

    if (type == 'reset password') {
        alertMessage.value = 'resetting password...'

        resetPassword({...data.user, id: profileOwner.value?.id})

        return
    }
    
    alertMessage.value = 'editing profile...'

    editInfo({...data.user, id: profileOwner.value?.id})
}

async function attachJob(job: Job)
{
    let response = await ApiService.post(`/job/${job.id}/attach`, {})
}

function become(type: string) {
    modalType.value = type 
    switch (type) {
        case 'facilitator':
            steps.facilitator.step = 1
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