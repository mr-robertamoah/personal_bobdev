<template>
    <transition name="slide-left" :duration="duration" mode="in-out">
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

.slide-left-enter-from,
.slide-left-leave-to{
    opacity: 0;
    transform: translateX(100px);
}

.slide-left-enter-active,
.slide-left-leave-active{
    transition: all 0.5s cubic-bezier(0.075, 0.82, 0.165, 1);
}
</style>