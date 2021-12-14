<template>
    <div 
        class="align-middle border-blue-600 cursor-pointer flex items-center mt-4 pr-2 py-1 rounded w-content"
        @click="clickedCheckBox"
    >
        <div
            class="bg-white mr-2 text-center w-5 rounded text-sm"
        >
            <font-awesome-icon 
                class=""
                :class="[boxIcon == 'check' ? 'text-green-500' : 'text-red-500']"
                :icon="['fa', boxIcon]"
                v-if="hasTimes || boxIcon == 'check'"
            ></font-awesome-icon>
            <div v-if="!hasTimes && boxIcon != 'check'" class="bg-blue-200 h-3 m-1 shadow-inner w-3"></div>
        </div>
        <div 
            class="text-gray-500 text-sm"
        >
            {{label}}
        </div>
    </div>
</template>

<script lang="ts">
import InputMixin from '../../mixins/InputMixin'
import { defineComponent, ref } from 'vue'

export default defineComponent({
    mixins: [InputMixin],
    props: {
        modelValue: {
            type: Boolean,
            default: false
        },
        hasTimes: {
            type: Boolean,
            default: true
        },
    },
    setup (props, ctx) {
        let boxIcon = ref('check')

        function switchBoxIcon() {
            boxIcon.value = boxIcon.value == 'check' ? 'times' : 'check'

            return boxIcon.value == 'check' ? true : false
        }

        function clickedCheckBox() {
            ctx.emit('update:modelValue', switchBoxIcon())
        }

        return {
            boxIcon,
            clickedCheckBox
        }
    }
})
</script>

<style scoped>

</style>