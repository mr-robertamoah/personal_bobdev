<template>
    <button :id="id" :type="type" class="">
        <slot></slot>
    </button>
</template>

<script>
import ButtonMixin from '../mixins/ButtonMixin'
    export default {
        mixins: [ButtonMixin],
    }
</script>