<template>
    <div class="w-full flex flex-col">
        <label v-if="label.length" :for="id" class="text-gray-600 text-base mb-1">{{label}}</label>
        <div class="flex w-full items-center justify-between">
            <input :type="type" :id="id" :placeholder="placeholder" class="focus:outline-none">
            <font-awesome-icon v-if="icon.length" :icon="icon"></font-awesome-icon>
        </div>
    </div>
</template>

<script>
import InputMixin from '../../mixins/InputMixin'
import {defineComponent, defineEmits} from 'vue'

    export default defineComponent({
        mixins: [InputMixin],
        props: {
            value: {
                type: String,
                default: ''
            },
        },
        setup(props) {

            const emitInput = (event)=>{

            }

            return  {emitInput}
        }
    })
</script>