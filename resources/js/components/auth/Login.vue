<template>
    <Form :title="computedTitle" class="mt-14">
        <template #default>
            <Loader
                text="loading"
                :load="loading"
            ></Loader>
            <Input 
                :label="'username'" v-model="data.username" :errors="errors.username" :info="info.username"
                v-if="!loginWithEmail"
            ></Input>
            <Input 
                :label="'email'" v-model="data.email" :errors="errors.email" :info="info.email"
                v-else
            ></Input>
            <Input 
                :label="'password'" 
                type="password"
                v-model="data.password" 
                :icon="passwordIcon"
                :errors="errors.password" :info="info.password"

            ></Input>
            <Checkbox
                label="login with email"
                v-model="loginWithEmail"
                :hasTimes="false"
            ></Checkbox>
            <div class="w-full flex justify-between items-center p-2 m-2.5">
                <primary-button @click="clickedPrimary">clear</primary-button>
                <secondary-button @click="clickedSecondary">submit</secondary-button>
            </div>
        </template>
    
        <template #footer v-if="hasRegisterOption">
            <FormFooter name="register" text="if you do not have an account"></FormFooter>
        </template>
    </Form>
</template>

<script setup lang="ts">
import { computed, reactive, ref, type PropType } from 'vue';
import { useRoute, useRouter, type RouteLocationRaw } from 'vue-router';
import { useStore } from 'vuex';
import useAlert from '../../composables/useAlert';
import useAuthErrors from '../../composables/useAuthErrors';
import {setObjectValuesToEmptyString} from '../../composables/useObjectFunctions'
import Checkbox from '../auth/Checkbox.vue'
import Form from '../auth/Form.vue'
import FormFooter from '../auth/FormFooter.vue'
import Input from '../auth/Input.vue'
import PrimaryButton from '../auth/PrimaryButton.vue'
import SecondaryButton from '../auth/SecondaryButton.vue'
import Loader from '../auth/Loader.vue'

let emits = defineEmits(['closeLoginModal'])
let props = defineProps<{
    hasTitle: boolean,
    isModal: boolean,
    hasRegisterOption: boolean,
}>()
let loginWithEmail = ref(false)
let data = reactive({
    username: '',
    email: '',
    password:  '',
})
let passwordIcon = ref(['key'])
let {errors, setErrors, errorWatchEffects, hasErrors} = useAuthErrors()
let {setSuccessAlertMessage, setDangerAlertMessage} = useAlert()
let router = useRouter()
let route = useRoute()
let store = useStore()
let loading = ref(false)
let info = ref({
    username: [
        'it can be a combination of letters, numbers, dashes',
        'it is a required field',
        'it should have at least 8 characters'
    ],
    email: [
        'use a valid email because you will verify it',
    ],
    password:  [
        'use a combination of letters (lower and upper cases), numbers, symbols to make it strong',
        'it should have at least 8 characters',
    ],
    passwordConfirmation:  [
        'this must be equal to the password field'
    ],
})
let isLoggedin = computed<boolean>(() => {
    return !!store.getters['user/isLoggedIn']
})

if (isLoggedin.value && !props.isModal) {
    router.push({name: 'home', params: {message: "you are already logged in."}})
}

errorWatchEffects(data)

function clickedPrimary() {
    
    setObjectValuesToEmptyString(data)
}

async function clickedSecondary() {

    if (hasErrors(errors)) {
        setDangerAlertMessage({
            message: "Please there are still some errors pending. Fill the necessary fields.",
            duration: 3000
        })

        return
    }

    loading.value = true

    let response = await store.dispatch('registerOrLoginUser', {url: '/login', data: {
        username: data.username?.trim(),
        email: data.email?.trim(),
        password: data.password?.trim(),
    }})
    console.log(response)
    if (response.data.errors) {
        setErrors(response.data.errors, errors)
    }

    if ([422].includes(response.status)) {
        setDangerAlertMessage({
            message: response.data.message ?? `Sorry! You may have entered a wrong ${data.username ? 'username' : 'email'} or password combination.`,
            duration: 3000
        })
    }

    if (response.status == 500) {
        setDangerAlertMessage({
            message: "Sorry, something unfortunate happened. Please try again pretty soon.",
            duration: 3000
        })
    }
    
    if (response.status === 200) {
        setSuccessAlertMessage({message: 'you have successfully logged in', duration: 2000})

        setTimeout(() => {
            leaveLoginPage()
        }, 1500);
    }

    loading.value = false
}

function leaveLoginPage() {
    if (props.isModal) {
        emits("closeLoginModal")
        return;
    }
    
    let redirectRoute = route.query.redirect
    
    if (redirectRoute && redirectRoute !== 'login') {
        let toRoute: RouteLocationRaw = {
            path: redirectRoute as string, 
            query: {username: data.username}
        }

        toRoute = route.params ? 
            {...toRoute, params: route.params} :
            toRoute

        router.push(toRoute)
        return
    }

    router.push({name: 'userProfile', params: {username: data.username}})
}

let computedTitle = computed(fn=>props.hasTitle ? 'Login' : '')

</script>

<style scoped>

</style>