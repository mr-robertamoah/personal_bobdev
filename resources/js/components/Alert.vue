<template>
    <div class="" v-if="show">
        {{message}}
    </div>
</template>

<script>
    export default {
        props: {
            message: {
                type: String,
                default:''
            },
            success: {
                type: Boolean,
                default: false
            },
            danger: {
                type: Boolean,
                default: false
            },
            duration: {
                type: Number,
                default: 1000
            },
        },
        data() {
            return {
                show: false
            }
        },
        watch: {
            message: {
                immediate: true,
                handler(newValue) {
                    if (!newValue) {
                        return this.hideAlert()
                    }

                    this.showAlert()
                }
            }
        },
        methods: {
            hideAlert() {
                this.show = false
                this.$emit('hideAlert')
            },
            showAlert() {
                this.show = true
                this.$emit('showAlert')
            }
        },
    }
</script>

<style lang="scss" scoped>

</style>