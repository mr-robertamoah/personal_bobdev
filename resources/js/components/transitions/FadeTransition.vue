<template>
    <transition name="fade" :duration="duration" mode="in-out">
        <slot></slot>
    </transition>
</template>

<script lang="ts">
import { defineComponent } from 'vue'

export default defineComponent({
    props: {
        duration: {
            type: Number,
            default: 1000
        },
    },
    setup () {
        

        return {}
    }
})
</script>

<style scoped>

.fade-enter-from,
.fade-leave-to{
    opacity: 0;
    visibility: hidden;
}

.fade-enter-active,
.fade-leave-active{
    transition: all 0.5s ease-in;
}
</style>