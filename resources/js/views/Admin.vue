<template>
    <!-- side bar -->
<div class="portrait:h-3/4 landscape:h-full rounded-br-md shadow-md absolute z-20 top-0 left-0 flex align-middle 
    transition-all duration-500"
    :class="[showFullSidebar ? 'w-1/2 sm:w-1/4 lg:w-1/5 min-w-150 max-w-sm': '']"
>
    
    <div class="bg-slate-300 h-full mb-6 flex flex-col align-middle flex-1 p-5 transition-all duration-500"
        :class="[showFullSidebar ? 'visible': 'hidden']"
    >
        <template></template>
        <template></template>
        <div class="text-sm md:text-md lg:text-lg font-black p-2 bg-gradient-to-br from-slate-600 to-green-600 
            w-content bg-clip-text text-transparent mb-6"
            :class="[showFullSidebar ? 'visible': 'hidden']"
        >welcome <br> {{adminType}}</div>

        <div class="">
            <div class="font-bold">Pages</div>

            <div @click="clickedSideBarItem('Users')" 
                class="font-medium p-1 cursor-pointer"
                :class="{'text-red-500': showItemType === 'User'}"
            >Users</div>
            <div @click="clickedSideBarItem('Projects')" 
                class="font-medium p-1 cursor-pointer">Progects</div>
        </div>
    </div>

    <div class="bg-slate-100 p-1 flex items-center justify-center rounded-br-md">
        <div class="cursor-pointer pt-2 pb-2"  @click="toggleShowFullSideBar">||</div>
    </div>
</div>
<div class="bg-transarent absolute top-0 left-0 w-full h-full bg-transparent z-10" 
    @click="toggleShowFullSideBar"
    v-if="showFullSidebar"></div>
<!-- main section -->
<div class="bg-blue-100 p-5">
    <div class="">
        <div class="max-h-[500px] overflow-y-scroll scroll-pl-9 sm:h-auto sm:flex w-full p-3 mt-2 mb-3 sm:overflow-x-scroll
            sm:overflow-y-visible">
            <InfoCard
                :title="'Users'"
            >
                <Loader v-if="loadingInfo"></Loader>
                <template v-else>
                    <div class="">{{generalInfo.users}} users</div>
                </template>
            </InfoCard>
            <InfoCard
                :title="'User Types'"
            >
                <Loader v-if="loadingInfo"></Loader>
                <div class="flex w-10/12 mx-auto p-3 sm:p-5 flex-wrap text-sm sm:text-base bg-blue-100" v-else>
                    <div class="w-content mb-1 text-sm sm:text-base rouned mx-1 p-1 bg-slate-50">{{`${generalInfo.parents} ${UserTypeClass.parent}${getPluralBasedOnNumber(generalInfo.parents)}`}}</div>
                    <div class="w-content mb-1 text-sm sm:text-base rouned mx-1 p-1 bg-slate-50">{{`${generalInfo.students} ${UserTypeClass.student}${getPluralBasedOnNumber(generalInfo.students)}`}}</div>
                    <div class="w-content mb-1 text-sm sm:text-base rouned mx-1 p-1 bg-slate-50">{{`${generalInfo.donors} ${UserTypeClass.donor}${getPluralBasedOnNumber(generalInfo.donors)}`}}</div>
                    <div class="w-content mb-1 text-sm sm:text-base rouned mx-1 p-1 bg-slate-50">{{`${generalInfo.facilitators} ${UserTypeClass.facilitator}${getPluralBasedOnNumber(generalInfo.facilitators)}`}}</div>
                    <div class="w-content mb-1 text-sm sm:text-base rouned mx-1 p-1 bg-slate-50">{{`${generalInfo.admins} ${UserTypeClass.admin}${getPluralBasedOnNumber(generalInfo.admins)}`}}</div>
                    <div v-if="isSuperAdmin" class="w-content mx-1 p-1 bg-slate-50">{{`${generalInfo.superadmins} ${UserTypeClass.admin}${getPluralBasedOnNumber(generalInfo.superadmins)}`}}</div>
                </div>
            </InfoCard>
        </div>
        <template v-if="showItemType == 'Users'">
            <div class="bg-slate-100 p-2 text-center hidden sm:visible sm:grid sm:grid-cols-3 shadow-sm rounded">
                <div class="">Username</div>
                <div class="">Name</div>
                <div class="">Email</div>
            </div>
            <div class="sm:hidden text-center mb-2 mt-3 font-black bg-gradient-to-r from-gray-500 
                to-blue-600 bg-clip-text text-transparent">{{showItemType}}</div>
            <User
                v-for="(user, index) in data"
                :key="index"
                :user="user"
            ></User>
        </template>
    </div>
        
    <button
        v-if="shownItemTypeHasMore && showItemType"
    >more</button>
</div>
</template>

<script setup lang="ts">
import { computed, reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import { useStore } from 'vuex';
import { UserTypeClass } from '../../ts/types/UserType';
import useGetUserTypes from '../composables/useGetUserTypes';
import useGeneralFunctions from '../composables/useGeneralFunctions';
import User from "../components/admin/User.vue"
import InfoCard from "../components/admin/InfoCard.vue"
import Loader from "../components/admin/Loader.vue"
import type UserType from '../../ts/types/User';
import ApiService from '../services/api.service';


let router = useRouter()
let store = useStore()
let {isSuperAdmin} = useGetUserTypes()
let {getPluralBasedOnNumber} = useGeneralFunctions()
let loadingInfo = ref<Boolean>(false)
let generalInfo = reactive<{
    users: number,
    parents: number,
    students: number,
    facilitators: number,
    donors: number,
    admins: number,
    superadmins: number,
}>({
    users: 0,
    parents: 0,
    students: 0,
    facilitators: 0,
    donors: 0,
    admins: 0,
    superadmins: 0,
})
let shownItemTypeHasMore = ref<Boolean>(false)
let showFullSidebar = ref<Boolean>(false)
let showItemType = ref<String|null>(null)
let adminType = computed(() => {
        if (store.getters['user/userTypes'].includes(UserTypeClass.superAdmin)) {
            return UserTypeClass.superAdmin
        }

        return UserTypeClass.admin
})
let data = computed<Array<UserType>>(() => {
    return showItemType.value === 'Users' ?
        store.getters['admin/getUsers'] :
        store.getters['admin/getProjects']
})

async function clickedSideBarItem(itemName: string) {
    shownItemTypeHasMore.value = false
    showItemType.value = itemName
    
    if (itemName === 'Users') {
        await getUsers()

    }
}

async function getGeneralInfo(){
    loadingInfo.value = true
    let response = await ApiService.get('admin/generalinfo')

    console.log(response)
    if (response.status == 200) {
        updateGeneralInfo(Object(response.data))
    }

    loadingInfo.value = false
}

function updateGeneralInfo(data: {[key: string]: number}){
    generalInfo.users = data['users_count']
    generalInfo.parents = data['parents_count']
    generalInfo.students = data['students_count']
    generalInfo.facilitators = data['facilitators_count']
    generalInfo.donors = data['donors_count']
    generalInfo.admins = data['admins_count']
    generalInfo.superadmins = data['superadmins_count']
}

getGeneralInfo()

async function getUsers(page: Number|String|null = null) {
    let {meta} = await store.dispatch('admin/getUsers', {page})

    if (meta.current_page !== meta.last_page) {
        shownItemTypeHasMore.value = true
    }
}

function toggleShowFullSideBar() {
    showFullSidebar.value = !showFullSidebar.value
}

</script>

<style scoped>

</style>