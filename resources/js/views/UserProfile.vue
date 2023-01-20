<template>
    <div class="overflow-hidden">
        <div v-if="isProfileOwner" 
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
                <DatePicker
                    :placeholder="'date of birth'" v-model="data.user.dob" :errors="errors?.user.dob"
                ></DatePicker>
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
            :src="profileOwner?.url" 
            :username="profileOwner?.username"
            :name="profileOwner?.name ?? ''"></ProfileHeader>
        <ProfileBody>
            <template v-if="isProfileOwner">
                <FoldableCard v-if="checkIfIsUserTypes(['student', 'facilitator'])" :cardTitle="'Admin'">

                </FoldableCard>
                <FoldableCard :cardTitle="'Facilitator'">
                    <Slider :titles="['about', 'want to be']">
                        <SliderDisplay>
                            <div class="font-semibold text-center text-lg mb-2">About</div>
                            <div class="text-center">This user type will have the ability to set skills and career in order to take up programs for training learners in those fields.</div>
                            <div 
                                class="font-semibold text-sm rounded text-green-600 bg-green-200 p-1 absolute bottom-0 mb-1
                                    left-0 ml-1"
                                v-if="isProfileOwnerFacilitator"
                            >A Facilitator</div>
                        </SliderDisplay>
                        <template v-if="isProfileOwnerFacilitator">
                            <SliderDisplay>
                                <div class="font-semibold text-center text-lg mb-2">Set Up</div>
                                <div class="text-center">
                                    Now you can go ahead to set up your facilitator account. Add your what you do (job), skills you are good at (skills), and others. Lets go 👍.
                                </div>
                                <SecondaryButton class="mt-4" @click="setUpUserTypeProfile">{{
                                    hasJobs || hasSkills ? 'continue' : 'start'
                                }}</SecondaryButton>
                                <div 
                                    class="font-semibold text-sm rounded text-green-600 bg-green-200 p-1 absolute bottom-0 mb-1
                                        left-0 ml-1"
                                >A Facilitator</div>
                            </SliderDisplay>
                        </template>
                        <template v-else>
                            <SliderDisplay>
                                <div v-if="loadings.facilitator.becoming" class="text-center text-green-600 my-2">
                                    becoming 
                                    <span>
                                        <font-awesome-icon class="animate-spin" :icon="['fa', 'spinner']"></font-awesome-icon>
                                    </span>
                                </div>
                                <div class="font-semibold text-center text-lg mb-2">Want To be</div>
                                <div class="flex flex-col items-center">
                                    <div class="text-justify">
                                        The road to becoming a Facilitator starts with clicking the button below. It ends with an interview by and approval from an administrator
                                    </div>
                                    <SecondaryButton class="mt-4" @click="becomeUserType('facilitator')">Apply</SecondaryButton>
                                </div>
                            </SliderDisplay>
                        </template>
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
            </template>
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
                            @click="create('createAndAttachJob')"
                        >create and attach</PrimaryButton>
                        <div class="font-bold text-base mb-4 mt-2 text-center capitalize">search and add a job to your jobs</div>
                        <div class="relative">``
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
import useUserTypes from '../composables/useUserTypes'
import DatePicker from '../components/auth/DatePicker.vue'
import useHelpers from '../composables/useHelpers'
import ResponseStatus from "../../ts/types/ResponseStatus"

let store = useStore()
let profileOwner = ref<User|null>(null)
let loading = ref<boolean>(false)
let loadings = ref<{
    job: {
        creating: boolean,
        searching: boolean,
        attaching: boolean,
    },
    facilitator: {
        becoming: boolean,
    }
}>({
    job: {
        creating: false,
        searching: false,
        attaching: false,
    },
    facilitator: {
        becoming: false,
    }
})
let {clearObjectProperties, pushItem, setObjectProperties} = useHelpers()
let props = defineProps<{username: string}>()
let emits = defineEmits(['sendProfileOwner', 'sendLoadingState'])
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
        dob: '',
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

// function
async function clickedEditInfo(type: string)
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

    let user = await editInfo({...data.user, id: profileOwner.value?.id})

    if (!user) {
        return;
    }

    data.user = clearObjectProperties(data.user)
}

async function attachJob(job: Job)
{
    let response = await ApiService.post(`/job/${job.id}/attach`, {})
}

function checkIfIsUserTypes(userTypes: Array<string>): boolean {

    let mappedUserTypes = profileOwner.value?.userTypes?.map((ut)=> {
        if (typeof ut == 'string') {
            return ut
        }
        
        ut.usableName
    })
    return userTypes.every(val=> mappedUserTypes?.includes(val))
}

async function becomeUserType(type: string) {

    loadings.value.facilitator.becoming = true

    let {status, userType} = await become({name: type, userId: profileOwner.value ? profileOwner.value.id : ""})

    loadings.value.facilitator.becoming = false

    if (!status) {
        return
    }

    await store.dispatch('user/addUserType', userType)

    pushItem(profileOwner.value?.userTypes ?? [], userType.usableName)

    modalType.value = type
}

function setUpUserTypeProfile() {

    switch (modalType.value) {
        case 'facilitator':
            steps.facilitator.step = 1
            break;
    
        default:
            break;
    }

    showModal.value = true
}

async function create(type: string, data: any = null) {
    switch (type) {
        case 'createAndAttachJob':
            loadings.value.job.creating = true
            await createAndAttachJob({
                name: data.job.name,
                description: data.job.description,
                attach: true,
                userId: user.value.id
            })

            loadings.value.job.creating = false
            break;
    
        default:
            break;
    }
}

function closeModal() {
    showModal.value = false
}

function changeStep(type: string)
{
    if (type == 'next') {
        steps.facilitator.step = steps.facilitator.step++
    }
    
    if (type == 'previous') {
        steps.facilitator.step = steps.facilitator.step--
    }
}

async function setUpUserProfile() {
    loading.value = true

    if (user.value?.username == props.username) {
        profileOwner.value = {...user.value}
    }
    
    if (user.value?.username != props.username) {
        let response = await ApiService.get(`/user/${props.username}`)

        if (response.status != 200) {

            setDangerAlertMessage({message: response.data.message, duration: 3000})
        }

        profileOwner.value = response.data.user
    }
    
    loading.value = false
}

async function getProfileDataForUser(user: User) {
    let response = await ApiService.get(`profile/user/${user.id ?? user.username}`)

    if (response.status != ResponseStatus.SUCCESS) {
        setDangerAlertMessage({
            message: "Sorry! Failed to get the profile info for this user",
            duration: 1000
        })

        return
    }

    profileOwner.value = setObjectProperties(profileOwner, response.data.profile)
}

await setUpUserProfile()

if (profileOwner.value) {
    await getProfileDataForUser(profileOwner.value)
}

let {become, isProfileOwnerFacilitator, hasSkills, hasJobs} = useUserTypes(
    profileOwner
)

let {editInfo, editableUser, editableUserLoading, resetPassword} = useEditUserInfo(profileOwner.value)

// watch
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

watch(loading, function(newValue) {
    emits("sendLoadingState", newValue)
})

watch(editableUser, ()=>{
    if (!profileOwner.value) {
        return
    }

    profileOwner.value.firstName = editableUser.value.firstName
    profileOwner.value.surname = editableUser.value.surname
    profileOwner.value.otherNames = editableUser.value.otherNames
    profileOwner.value.gender = editableUser.value.gender
    profileOwner.value.email = editableUser.value.email
    profileOwner.value.age = editableUser.value.age
    profileOwner.value.name = editableUser.value.name
})

watch(editableUserLoading, (newValue)=>{
    if (newValue) {
        return
    }

    alertMessage.value = ''
})

// computed
let isProfileOwner = computed<boolean>(()=>{
    if (!user.value || !profileOwner.value) {
        return false
    }
    
    return user.value.username == profileOwner.value?.username
})

let computedHasDataToEditInfo = computed<boolean>(()=>{
    return Boolean(
        data.user.email || data.user.firstName || data.user.surname || data.user.otherNames ||
        data.user.gender || data.user.dob
    )
})

let computedHasDataToResetPassword = computed<boolean>(()=>{
    return Boolean(
        data.user.password ||
        data.user.passwordConfirmation
    )
})
</script>