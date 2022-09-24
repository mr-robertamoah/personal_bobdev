<template>
    <div class="mb-2 sm:mb-0 rounded-md shadow-md mr-2 ml-2 p-2 bg-slate-50 min-w-[175px] sm:min-w-[200px]">
        <div class="text-sm font-bold bg-gradient-to-r from-green-500 to-blue-500 
            bg-clip-text p-1">{{title}}</div>

        <div class="mt-2 mb-1 p-1">
            <slot></slot>
        </div>
    </div>
</template>

<script setup lang="ts">

let props = defineProps<{
    title: string
}>()

</script>

<style scoped>

</style>