<template>
    <div class="bg-transparent relative my-2" id="">
        <div class="w-full px-2 pt-2 text-center cursor-pointer rounded transition-colors" 
                :class="[show ? 'text-blue-800 bg-blue-200 font-bold pb-4' : 'bg-gray-100 shadow pb-2']"
            @click="show = !show">{{cardTitle}}</div>
        <FoldTransition>
            <div class="bg-white w-[80%] mx-auto relative -top-3 p-2 rounded shadow" v-if="show">
                <slot></slot>
            </div>
        </FoldTransition>
    </div>
</template>

<script setup lang="ts">
import { ref } from "vue";
import FoldTransition from "./transitions/FoldTransition.vue";

let props = defineProps<{
    cardTitle: string,
}>()

let show = ref<boolean>(false)
</script>

<style scoped>

</style>