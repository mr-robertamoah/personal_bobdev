<template>
    <div class="rounded-full cursor-pointer transition w-4 h-4 hover:w-5 hover:h-5 border-2"
        :class="[active ? 'bg-white border-blue-200' : 'bg-blue-200 border-white']"
        v-bind="$attrs"
    ></div>
</template>

<script setup lang="ts">
    let props = defineProps<{
        active: boolean
    }>()
</script>

<style scoped>

</style>