<template>
    <div class="w-full flex flex-col mt-4 max-w-400 mx-auto">
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
        <div 
            class="flex w-full items-center justify-between px-2 py-1 transition-colors duration-100"
            :class="[focus ? 
                'border-b-2 border-blue-600 bg-blue-100 rounded-none text-blue-800' : 
                'rounded bg-white text-zinc-600'
            ]"
        >
            <input
                :type="type" :id="id" :placeholder="placeholder" :value="modelValue" 
                @input="emitInput($event)" 
                class="w-full outline-none bg-transparent"
                ref="input"
                @focusin="switchFocus"
                @focusout="switchFocus"
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
        class="mb-2 text-xs text-red-400 font-bold pl-2 max-w-400 mx-auto"
        v-if="errors"
    >
        <template
            v-if="Array.isArray(errors)"
        >
            <div 
                v-for="(error, index) in errors"
                class=""
                :key="index"
            >
                {{error}}
            </div>
        </template>
        <div 
            v-else
            class=""
        >
            {{errors}}
        </div>
    </div>
    <div 
        class="mb-2 text-xs text-white font-bold cursor-pointer pl-2 max-w-400 mx-auto"
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

<script setup lang="ts">
import {ref, onBeforeMount} from 'vue'
import type {PropType} from 'vue'
import useGenerateId from '../../composables/useGenerateId'
import useElementProps from '../../composables/useElementProps'
import useOtherElementProps from '../../composables/useOtherElementProps'

let props = defineProps({
    ...useOtherElementProps(),
    ...useElementProps(),
    modelValue: {
        type: String as PropType<string|null>,
        default: ''
    },
    required: {
        type: Boolean,
        default: false
    },
    icon: {
        type: Array as PropType<String[]>,
        default: []
    },
    errors: {
        type: Array as  PropType<string[]|string|null>,
        default: null
    },
    info: {
        type: Array as PropType<String[]|null>,
        default: null
    },
})

let emit = defineEmits(['update:modelValue'])

let showInfo = ref(false)
let focus = ref(false)
let passwordIcon = ref<String|null>()
let input = ref<HTMLInputElement>()

let {id} = useGenerateId()

const emitInput = (event: InputEvent | Event)=>{
    emit('update:modelValue', (event.target as HTMLInputElement).value)
}

function switchFocus() {
    focus.value = !focus.value
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

</script>