<template>
    <button :id="id" :type="type" :disabled="disabled" class="">
        <slot></slot>
    </button>
</template>

<script>
import ButtonMixin from '../mixins/ButtonMixin'
    export default {
        mixins: [ButtonMixin],
        props: {
            disabled: {
                type: Boolean,
                default: false
            }
        }
    }
</script>