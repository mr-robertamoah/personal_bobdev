<template>
    <div class="bg-blue-100 relative w-full h-screen">
        <nav-bar></nav-bar>
        <router-view></router-view>
    </div>
</template>

<script>
    export default {
        created () {
            console.log('main',this.$router.currentRoute);
        },
    }
</script>

<style lang="scss" scoped>

</style>