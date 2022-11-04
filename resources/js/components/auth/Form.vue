<template>
    <form 
        @submit.prevent="submit" 
        class="md:w-5/12 mx-auto w-9/12 relative bg-blue-200 px-8 pb-2 pt-16 
            rounded-b-3xl"
        @keydown.enter="submit"
        v-bind="$attrs"
    >
        <div
            v-if="title.length"
            class="bg-clip-text bg-gradient-to-tr font-extrabold from-blue-800 mx-auto my-2 text-3xl text-center 
                text-transparent to-red-400 uppercase"
        >
            {{title}}
        </div>
        <slot name="default"></slot>
    </form>

    <slot name="footer"></slot>
</template>

<script setup type="ts">
import {} from 'vue'

    let props = defineProps({
        submit: {
            type: Function,
            default: () => null
        },
        title: {
            type: String,
            default: ''
        },
    })
       
</script>

<style lang="scss" scoped>

</style>