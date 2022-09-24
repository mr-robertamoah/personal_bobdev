<template>
    <fade-transition>
        <div v-if="load" class="p-2 text-center w-full flex justify-center align-middle absolute top-2 left-0 right-0">
            <div :class="`text-sm font-bold text-${color === 'white' ? color : color + '-500'} ml-1`">{{text}}</div>
            <div :class="`my-auto mx-2 w-2 h-2 bg-${color === 'white' ? color : color + '-500'} animate-bounce`"></div>
            <div :class="`my-auto mx-2 w-2 h-2 bg-${color === 'white' ? color : color + '-500'} animate-bounce`"></div>
        </div>
    </fade-transition>
</template>

<script lang="ts">
import FadeTransition from '../transitions/FadeTransition.vue'
import { defineComponent } from 'vue'

export default defineComponent({
    components: {
        FadeTransition,
    },
    props: {
        text: {
            type: String,
            default: 'loading'
        },
        color: {
            type: String,
            default: 'white'
        },
        load: {
            type: Boolean,
            default: false
        },
    },
    setup () {
        

        return {}
    }
})
</script>

<style scoped>

</style>