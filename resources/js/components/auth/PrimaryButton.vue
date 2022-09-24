<template>
    <Button :id="id" :type="type" 
        class="border p-2 rounded border-gray-400 bg-blue-100 shadow hover:bg-blue-200 
            transition-colors duration-100 outline-none focus:bg-blue-200 focus:border-blue-600"
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