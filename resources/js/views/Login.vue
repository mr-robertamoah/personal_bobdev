<template>
    <div class="flex justify-center items-center w-full h-90vh flex-col relative overflow-hidden">
        <Login :hasRegisterOption="true" :hasTitle="true" :isModal="false"></Login>
    </div>
</template>

<script setup lang="ts">
import Login from '../components/auth/Login.vue'

</script>

<style lang="scss" scoped>

</style>