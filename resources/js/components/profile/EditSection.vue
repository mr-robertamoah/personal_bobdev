<template>
    <div :id="sectionId" class="section">
        <slot></slot>
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';


    let props = defineProps<{
        id: string
    }>()
    let sectionId = ref<string>()

    function createId()
    {
        sectionId.value = `section${props.id}`
    }

    createId()
</script>