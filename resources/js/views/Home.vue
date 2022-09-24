<template>
    <div class="w-full">
        <section class="h-screen w-full relative overflow-hidden">
            sup
            <div class="absolute top-1/2 h-1/2 left-0 w-full bg-gradient-to-b from-transparent z-40 to-gray-100">hey</div>
        </section>
        <section class="h-screen w-full">

        </section>
    </div>
</template>

<script setup lang="ts">
import { onBeforeMount, ref } from 'vue';
import { useRoute } from 'vue-router';


    let errorMessage = ref<string|null>(null)
    let route = useRoute()

    onBeforeMount(() => {
        errorMessage.value = (route.query.errorMessage) as string|null
    })
</script>

<style lang="scss" scoped>

</style>