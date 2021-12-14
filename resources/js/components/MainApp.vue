<template>
    <div class="bg-blue-100 relative w-full h-screen">
        <nav-bar :navItems="routes"></nav-bar>
        <router-view></router-view>
    </div>
</template>

<script>
import Routes from '../../ts/Routes'
    export default {
        created () {
            console.log('main',this.$router?.currentRoute);
        },
        setup() {
            let routes = Routes

            return {routes}
        }
    }
</script>

<style lang="scss" scoped>

</style>