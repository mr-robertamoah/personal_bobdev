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
                <div
                    v-show="step == 1"
                >
                    <Input 
                        :label="'first name'" 
                        v-model="firstName" 
                        :errors="errors.firstName" 
                        :info="info.firstName"
                        :required="true" 
                    ></Input>
                    <Input 
                        :label="'surname'" 
                        v-model="surname" 
                        :errors="errors.surname" 
                        :info="info.surname"
                        :required="true" 
                    ></Input>
                    <Input 
                        :label="'other names'" 
                        v-model="otherNames" 
                        :errors="errors.otherNames" 
                        :info="info.otherNames"
                    ></Input>
                </div>
                <div v-show="step > 1">
                        <Input 
                        :label="'username'" 
                        v-model="username" 
                        :errors="errors.username" 
                        :info="info.username"
                    ></Input>
                    <Input 
                        :label="'email'" 
                        v-model="email" 
                        :errors="errors.email" 
                        :info="info.email"
                        :required="false" 
                    ></Input>
                    <Input 
                        :label="'password'" 
                        :required="true" 
                        type="password" 
                        v-model="password" 
                        :icon="passwordIcon"
                        :errors="errors.password" :info="info.password"
                    ></Input>
                    <Input 
                        :label="'password confirmation'" 
                        :required="true" 
                        type="password" 
                        v-model="passwordConfirmation"
                        :icon="passwordConfirmationIcon"
                        :errors="errors.passwordConfirmation" :info="info.passwordConfirmation"
                    ></Input>
                </div>
                <div 
                    class="w-full flex items-center p-2 m-2.5"
                    :class="[step == 1 ? 'justify-end' : 'justify-between']"
                >
                    <primary-button v-if="step == 1" @click.native="clickedPrimary('next')">next</primary-button>
                    <primary-button v-if="step > 1" @click.native="clickedPrimary('previous')">previous</primary-button>
                    <secondary-button v-if="step > 1" @click.native="clickedSecondary">submit</secondary-button>
                </div>
            </template>
            <template #footer>
                <FormFooter name="login" text="if already registered"></FormFooter>
            </template>
        </Form>

    </div>
</template>

<script lang="ts">
import { useRouter } from 'vue-router'
import { defineComponent, reactive, ref, toRefs } from 'vue'
import ApiService from '../services/api.service.js'
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
        Loader
    },
    setup() { 
        let registerData = {
            firstName: null,
            surname: null,
            otherNames: null,
            username: null,
            email: null,
            password:  null,
            passwordConfirmation:  null,
        }
        let data = reactive({...registerData})
        let errors = ref({...registerData})
        let componentName = ref('register')
        let {setErrors, errorWatchEffects, hasErrors} = useAuthErrors()
        let {clearAlertMessage, setSuccessAlertMessage, alertData} = useAlert()
        let router = useRouter()
        let loading = ref(false)
        let step = ref(1)
        let info = ref({
            firstName: [],
            lastName: [],
            otherNames: [],
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
        let passwordConfirmationIcon = ref(['key'])
        
        errorWatchEffects(data, errors)

        function clickedPrimary(to: string) {
            if (to == 'next') {
                step.value = 2
                return
            }

            step.value = 1
        }

        async function clickedSecondary() {
            loading.value = true
            console.log(`hasErrors`, hasErrors(errors))

            await ApiService.get('csrf-cookie')

            let response = await ApiService.post(`/${componentName.value}`, {
                firstName: data.firstName,
                surname: data.surname,
                otherNames: data.otherNames,
                username: data.username,
                email: data.email,
                password: data.password,
                'password-confirmation': data.passwordConfirmation,
            })

            console.log(response)
            if (response.status !== 200) {
                setErrors(response.data.errors, errors)
            }
            
            if (response.status === 200) {
                setSuccessAlertMessage({message: 'your user account was successfully registered'})

                setTimeout(() => {
                    router.push({name: 'userProfile', params: {username: data.username}})
                }, 1500);
            }

            loading.value = false
        }

        return { 
            ...toRefs(data), 
            clickedPrimary, 
            clickedSecondary, 
            loading,
            passwordIcon,
            passwordConfirmationIcon,
            errors,
            info,
            clearAlertMessage,
            alertData,
            step,
            componentName
        }
    },
    
})
</script>

<style lang="scss" scoped>

</style>