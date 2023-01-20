<template>
    <Modal 
        :has-close-button="true" 
        :show="show" 
        @close-modal="closeLoginModal" 
        :overlay-bg-color-class="'bg-transparent'"
        :main-bg-color-class="'bg-blue-200'"
        :close-modal-by-overlay="false"
    >
        <Login 
            :has-title="false" 
            :is-modal="true" 
            :has-register-option="true"
            @close-login-modal="closeLoginModal"
        >
        </Login>
    </Modal>
</template>

<script setup lang="ts">
import Login from "./auth/Login.vue";
import Modal from "./Modal.vue"

let props = defineProps<{
    show: boolean
}>()

let emits = defineEmits(['closeLoginModal'])

function closeLoginModal()
{
    emits('closeLoginModal')
}

</script>

<style scoped>

</style>