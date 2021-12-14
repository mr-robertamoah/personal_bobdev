<template>
    <div 
        class="flex w-full justify-center align-middle mt-10"
    >
        <router-link 
            custom
            :to="{name: name}" v-slot="{isActive, isExactActive, navigate}"
        >
            <div 
                @click="navigate"
                :class="{'text-blue-600': isExactActive, 'text-gray-500': isActive}"
                class="bg-white border border-gray-500 cursor-pointer hover:bg-blue-200 px-1 rounded 
                    text-sm transition-colors"
            >{{name}}</div>
        </router-link>
        <div class="ml-2">{{text}}</div>
    </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue'

export default defineComponent({
    props: {
        name: {
            type: String,
            default: ''
        },
        text: {
            type: String,
            default: ''
        },
    },
    setup () {
        

        return {}
    }
})
</script>

<style scoped>

</style>