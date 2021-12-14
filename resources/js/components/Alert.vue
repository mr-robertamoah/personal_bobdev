<template>
    <slide-left-transition :duration="alertDuration">
        <div 
            class="absolute bottom-full left-0 right-0 p-10"
            :class="[
                alertStatus == 'sucess' ? 'bg-green-500 text-green-100' : 
                (alertStatus == 'danger' ? 'bg-red-500 text-red-100' : '')]"
            v-if="alertMessage.length"
        >
            {{alertMessage}}
        </div>
    </slide-left-transition>
</template>

<script>
import SlideLeftTransition from './transitions/SlideLeftTransition.vue'

    export default {
        components: {
            SlideLeftTransition,
        },
        props: {
            alertMessage: {
                type: String,
                default:''
            },
            alertStatus: {
                type: String,
                default: ''
            },
            alertDuration: {
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
            alertMessage: {
                immediate: true,
                handler(newValue) {
                    if (!newValue) {
                        return
                    }

                    this.$emit('clearAlertMessage')
                }
            }
        },
    }
</script>

<style lang="scss" scoped>

</style>