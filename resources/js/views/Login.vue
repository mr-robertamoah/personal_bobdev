<template>
    <div class="flex justify-center items-center w-full h-90vh flex-col">
        <Form :title="componentName">
            <template #default>
                <Alert 
                    @clearAlertMessage="clearAlertMessage" 
                    :alertMessage="alertData.alertMessage"
                    :alertStatus="alertData.alertStatus"
                    :alertDuration="alertData.alertDuration"
                ></Alert>
                <Loader
                    text="loading"
                    :load="loading"
                ></Loader>
                <Input 
                    :label="'username'" v-model="username" :errors="errors.username" :info="info.username"
                    v-if="loginWithEmail"
                ></Input>
                <Input 
                    :label="'email'" v-model="email" :errors="errors.email" :info="info.email"
                    v-else
                ></Input>
                <Input 
                    :label="'password'" 
                    type="password"
                    v-model="password" 
                    :icon="passwordIcon"
                    :errors="errors.password" :info="info.password"
                ></Input>
                <Checkbox
                    label="login with email"
                    v-model="loginWithEmail"
                    :hasTimes="false"
                ></Checkbox>
                <div class="w-full flex justify-between items-center p-2 m-2.5">
                    <primary-button @click.native="clickedPrimary">clear</primary-button>
                    <secondary-button @click.native="clickedSecondary">submit</secondary-button>
                </div>
            </template>
        
            <template #footer>
                <FormFooter name="register" text="if you do not have an account"></FormFooter>
            </template>
        </Form>
    </div>
</template>

<script lang="ts">
import { defineComponent, reactive, ref, toRefs, watchEffect } from 'vue'
import ApiService from '../services/api.service.js'
import Checkbox from '../components/auth/Checkbox.vue'
import Form from '../components/auth/Form.vue'
import FormFooter from '../components/auth/FormFooter.vue'
import Input from '../components/auth/Input.vue'
import Alert from '../components/Alert.vue'
import PrimaryButton from '../components/auth/PrimaryButton.vue'
import SecondaryButton from '../components/auth/SecondaryButton.vue'
import Loader from '../components/auth/Loader.vue'
import useAuthErrors from '../composables/useAuthErrors.js'
import useAlert from '../composables/useAlert.js'
    
export default defineComponent({
    components: {
        PrimaryButton, 
        SecondaryButton, 
        Form,
        FormFooter,
        Input,
        Alert,
        Loader,
        Checkbox
    },
    setup() {
        let data = reactive({
            username: null,
            email: null,
            password:  null,
        })
        let componentName = ref('login')
        let {errors, setErrors, errorWatchEffects} = useAuthErrors()
        let {clearAlertMessage, setSuccessAlertMessage, alertData} = useAlert()
        let loginWithEmail = ref(false)

        errorWatchEffects(data)

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
        let passwordIcon = ref(['key'])

        function clickedPrimary() {
            console.log(`data.email`, data.email)
        }

        async function clickedSecondary() {
            loading.value = true
            let response = await ApiService.post('/login', {
                username: data.username,
                email: data.email,
                password: data.password,
            })

            console.log(response)
            if (response.status !== 200) {
                setErrors(response.data.errors)
            }
            
            if (response.status === 200) {
                setSuccessAlertMessage({message: ''})
            }

            loading.value = false
        }

        return { 
            ...toRefs(data), 
            clickedPrimary, 
            clickedSecondary, 
            loading,
            passwordIcon,
            errors,
            info,
            clearAlertMessage,
            alertData,
            componentName,
            loginWithEmail
        }
    }
})
</script>

<style lang="scss" scoped>

</style>