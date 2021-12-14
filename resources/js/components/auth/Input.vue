<template>
    <div class="w-full flex flex-col mt-4">
        <div class="w-full flex">
            <label
                v-if="label.length" 
                :for="id" 
                class="text-gray-500 text-sm ml-2 -mb-0"
            >{{label}}</label>
            <div 
                class="text-red-500"
                v-if="required"
            >*</div>
        </div>
        <div class="flex w-full items-center justify-between border-blue-600 bg-white px-2 py-1 rounded">
            <input
                :type="type" :id="id" :placeholder="placeholder" :value="modelValue" 
                @input="emitInput($event.target.value)" 
                class="w-full focus:outline-none bg-transparent"
                ref="input"
            >
            <font-awesome-icon 
                class="mx-2 text-blue-600 text-xs cursor-pointer" 
                v-if="modelValue?.length && passwordIcon" :icon="['fa', passwordIcon]" @click="clickedIcon"
            ></font-awesome-icon>
            <font-awesome-icon 
                class="mx-2 text-blue-600 text-xs cursor-pointer" 
                v-if="info?.length && info?.length && !showInfo" :icon="['fa', 'info']" @click="showInfo = !showInfo"
            ></font-awesome-icon>
        </div>
    </div>
    <div 
        class="mb-2 text-xs text-red-400 font-bold pl-2"
        v-if="errors"
    >
        <div 
            v-for="(error, index) in errors"
            class=""
            :key="index"
        >
            {{error}}
        </div>
    </div>
    <div 
        class="mb-2 text-xs text-white font-bold cursor-pointer pl-2"
        v-if="info && showInfo"
        @click="showInfo = !showInfo"
    >
        <div 
            v-for="(infoItem, index) in info"
            class=""
            :key="index"
        >
            {{infoItem}}
        </div>
    </div>
</template>

<script lang="ts">
import InputMixin from '../../mixins/InputMixin'
import {defineComponent, ref, onBeforeMount} from 'vue'

export default defineComponent({
    mixins: [InputMixin],
    props: {
        modelValue: {
            type: String,
            default: ''
        },
        required: {
            type: Boolean,
            default: false
        },
    },
    setup(props, ctx) {

        let showInfo = ref(false)
        let passwordIcon = ref(null)
        let input = ref<HTMLInputElement>()

        const emitInput = (value: string)=>{
            ctx.emit('update:modelValue', value)
        }

        function setPasswordIcon()
        {
            if (!props.icon) {
                return
            }

            if (props.icon.length < 2) {
                passwordIcon.value = props.icon[0]
                return
            }

            if (props.icon[0] == passwordIcon.value) {
                passwordIcon.value = props.icon[1]
                return
            }

            passwordIcon.value = props.icon[0]
        }

        function switchType() {
            if (props.type !== 'password' || !input.value) {
                return
            }

            if (input.value.type === 'password') {
                input.value.type = 'text'
                return
            }

            input.value.type = 'password'
        }

        onBeforeMount(()=> setPasswordIcon())

        const clickedIcon = ()=>{
            setPasswordIcon()
            switchType()
        }

        return  {
            emitInput, 
            clickedIcon, 
            showInfo, 
            passwordIcon, 
            input
        }
    }
})
</script>