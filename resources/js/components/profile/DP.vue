<template>
    <div class="rounded-full p-2 flex items-center justify-center">
        <img :class="{'h-8 w-8': size == 'small', 'h-[70px] w-[70px]': size == 'big'}" :src="src" :alt="alt" v-if="src">
        <font-awesome-icon :class="{'h-8 w-8': size == 'small', 'h-[70px] w-[70px]': size == 'big'}" v-else :icon="defaultIcon"></font-awesome-icon>
    </div>
</template>

<script setup lang="ts">

let props = defineProps<{
    src?: string,
    alt?: string,
    size?: string,
    defaultIcon: Array<string>
}>()


</script>

<style scoped>

</style>