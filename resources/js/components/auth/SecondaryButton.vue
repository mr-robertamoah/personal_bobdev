<template>
    <Button :id="id" :type="type" class="">
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