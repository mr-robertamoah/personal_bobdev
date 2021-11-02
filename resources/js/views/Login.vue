<template>
    <Container>
        <Form>
            <Input label="email"></Input>
            <Input label="password" type="password"></Input>
            <div class="w-full flex justify-between items-center p-2 m-2.5">
                <primary-button>primary</primary-button>
                <secondary-button>secondary</secondary-button>
            </div>
        </Form>
    </Container>
</template>

<script>
import Container from '../components/auth/Container.vue'
import Form from '../components/auth/Form.vue'
import Input from '../components/auth/Input.vue'
import PrimaryButton from '../components/auth/PrimaryButton.vue'
import SecondaryButton from '../components/auth/SecondaryButton.vue'
    export default {
        components: { 
            Container, 
            PrimaryButton, 
            SecondaryButton, 
            Form,
            Input
        },
        
    }
</script>

<style lang="scss" scoped>

</style>