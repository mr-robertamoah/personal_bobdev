<template>
    <transition name="slide-down">
        <slot></slot>
    </transition>
</template>

<script>
    export default {
        
    }
</script>

<style scoped>

    .slide-down-leave-active,
    .slide-down-enter-active {
        transition: all 0.5s ease-in-out;
    }
    
    .slide-down-leave-to,
    .slide-down-enter-from {
        opacity: 0;
        transform: translateY(-1oopx);
    }
</style>