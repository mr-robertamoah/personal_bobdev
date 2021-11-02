<template>
    <form @submit.prevent="formSubmission">
        <slot></slot>
    </form>
</template>

<script type="ts">
import {defineComponent} from 'vue'
    export default defineComponent({
        props: {
            submit: {
                type: Function,
                default: () => null
            },
        },
        methods: {
            formSubmission() {
                this.submit = 'good'
            }
        },
    })
</script>

<style lang="scss" scoped>

</style>