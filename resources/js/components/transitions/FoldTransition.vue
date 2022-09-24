<template>
    <transition name="fold">
        <slot></slot>
    </transition>
</template>

<script setup lang="ts">

</script>

<style scoped>

    .fold-enter-from,
    .fold-leave-to {
        /* height: 0%; */
        opacity: 0;
        transform: scaleY(-30px);
        visibility: hidden;
        display: none;
    }

    .fold-enter-active,
    .fold-leave-active {
        /* height: fit-content; */
        transition: all 2s ease-in-out;
    }
</style>