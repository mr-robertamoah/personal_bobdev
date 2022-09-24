<template>
    <div
        v-if="isValidProject"
    >
        in project
    </div>
    <div
        v-else
        class=""
    >
        <div>
            {{`sorry ${$route.path} is not a valid route path to a project`}}
        </div>
        <div>
            go to <router-link :to="{name: 'home'}">home</router-link>
        </div>
    </div>
</template>

<script lang="ts">
import Routes from '../../ts/Routes'
import { defineComponent } from 'vue'
import type { RouteLocationNormalized } from 'vue-router'

export default defineComponent({
    setup () {
        

        return {}
    },
    beforeRouteEnter(to: RouteLocationNormalized, from: RouteLocationNormalized, next) {
        next(vm => {
            
        });
    },
    computed: {
        isValidProject(): boolean {
            let projectName = this.$route.params.projectName

            let projectRoute = Routes
                .find(route=>route.name == 'projects')
                ?.subItems
                ?.find(subRoute=> subRoute.name == projectName || subRoute.routeName == projectName)
            
            console.log(projectRoute, projectName)
            return projectRoute ? true : false
        },
    }
})
</script>

<style scoped>

</style>