<template>
    <Button :id="id" :type="type" 
        class="bg-gradient-to-tr border border-gray-400 duration-100 from-blue-600 
            hover:to-red-300 hover:from-blue-400 hover:scale-x-105 
            p-2 rounded shadow text-white to-red-400 transition-all outline-none
            focus:from-blue-600 focus:to-red-300 focus:border-teal-600"
    >
        <slot></slot>
    </Button>
</template>

<script>
import ButtonMixin from '../../mixins/ButtonMixin'
import Button from '../Button.vue'
    export default {
        components: { Button },
        mixins: [ButtonMixin],
    }
</script>