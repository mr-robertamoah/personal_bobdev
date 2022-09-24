<template>
    <div class="bg-white w-full h-48 relative">
        <div class="w-full h-full" v-if="loading">
            <font-awesome-icon class="animate-spin" :icon="['fa', 'spinner']"></font-awesome-icon>
        </div>
        <div class="absolute -bottom-10 mx-auto w-20 h-20 flex justify-center items-center rounded-full bg-slate-100">
            <DP :src="src" :size="'big'" :default-icon="['fa', 'user-circle']"></DP>
        </div>
    </div>
    <div class="relative float-right mr-2 mt-2 font-bold w-content bg-gradient-to-r
        from-green-800 to-blue-700 bg-clip-text flex justify-center items-center">
        <span class="capitalize text-lg">{{name}}</span>
        <template v-if="username">
            <span class="text-lg mx-2">.</span>
            <span class="text-sm">@{{username}}</span>
        </template>
    </div>
</template>

<script setup lang="ts">
import { computed } from "vue";
import DP from "./DP.vue";

let props = defineProps<{
    loading: boolean,
    name: string,
    username?: string,
    src?: string,
}>()

let appropriateName = computed(()=>{
    let result = props.name

    if (props.username) {
        result += ` . ${props.username}`
    }
    return result
})
</script>
