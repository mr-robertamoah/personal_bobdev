<template>
    <transition name="modal">
        <slot></slot>
    </transition>
</template>

<script setup lang="ts">

</script>

<style lang="scss" scoped>

    .modal-enter-active,
    .modal-leave-active {
        transition: all 1s ease;
    }

    .modal-enter-from,
    .modal-leave-to{
        opacity: 0;
        transform: scale(0);
    }
</style>