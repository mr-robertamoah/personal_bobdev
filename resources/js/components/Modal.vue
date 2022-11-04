<template>
    <ModalTransition>
        <template v-if="show">
            <div class="absolute top-0 bottom-0 left-0 right-0 flex justify-center items-center">
            
                <!-- overlay -->
                <div class="absolute top-0 bottom-0 left-0 right-0 bg-blue-200 opacity-50 z-40 rounded-lg"
                    @click="closeModal"
                ></div>
                <div v-bind="$attrs" class="z-50 shadow w-11/12 h-[85%] rounded relative
                    sm:w-9/12 sm:h-[80%] md:w-8/12 lg:w-7/12 lg:h-[75%]"
                    :class="[bg ? bg : 'bg-white']"
                >
                    <div class="absolute -right-2 cursor-pointer -top-2 bg-white border rounded-full
                        flex items-center justify-center p-1 h-6 w-6"
                        @click="closeModal"
                    >
                        <font-awesome-icon :icon="['fa', 'times']"></font-awesome-icon>
                    </div>
                    <div v-if="title" class="bg-slate-50 p-2 text-slate-600 font-bold w-full text-center rounded-t-lg">{{title}}</div>
                    <div class="p-2">
                        <slot></slot>
                    </div>
                </div>
            </div>
        </template>
    </ModalTransition>
</template>

<script setup lang="ts">
import ModalTransition from "./transitions/ModalTransition.vue";

    let emits = defineEmits(['closeModal'])
    let props = defineProps<{
        title?: string,
        show: boolean,
        hasCloseButton?: boolean,
        bg?: string,
    }>()

    function closeModal() {
        emits('closeModal')
    }
</script>

<style scoped>

</style>