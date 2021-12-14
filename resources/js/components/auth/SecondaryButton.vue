<template>
    <Button :id="id" :type="type" 
        class="bg-gradient-to-tr border border-gray-400 duration-100 from-blue-600 
            hover:bg-red-300 hover:from-blue-400 hover:from-red-100 hover:scale-x-105 
            p-2 rounded shadow text-white to-red-400 transform transition-all"
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