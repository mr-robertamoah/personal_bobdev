<template>
    <div class="z-10 absolute left-0 right-0 top-0 bottom-0 bg-red-50 flex justify-center items-center p-5 flex-col"
        v-if="!user"
    >
        <div class="text-sm text-slate-600">Sorry! Nothing to see here. Please make sure you a logged in or visiting a valid user/company profile</div>
        <PrimaryButton class="mt-10" @click="$router.push({name: 'home'})">
            Go to Home Page
        </PrimaryButton>
    </div>
    <router-view v-bind="$attrs" class="overflow-x-visible" @send-profile-owner="getProfileOwner"></router-view>
</template>

<script setup lang="ts">
import { computed, ref, watch, type Ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useStore } from 'vuex';
import type Company from '../../ts/types/Company';
import type User from '../../ts/types/User';
import PrimaryButton from '../components/auth/PrimaryButton.vue';

let store = useStore()
let route = useRoute()
let router = useRouter()
let profileOwner = ref<User|Company|null>(null)
let user = computed<User|null>(() => store.state.user.user)

watch(user, ()=>{
    if (user.value && route.name == 'profile') {
        router.push({name: 'userProfile', params: {username: user.value.username}})
    }
})

function getProfileOwner(data: Ref<User|Company|null>) {
    profileOwner.value = data.value
}

async function setUpUserProfile() {

    if (user.value) {
        profileOwner.value = user.value
    }
}

setUpUserProfile()
    
</script>

<style scoped>

</style>