import {createRouter, createWebHistory} from 'vue-router'
import routes from './routes'
import {routerBeforeEachRoute} from './router'

let router = createRouter({
    history: createWebHistory(),
    routes
})

router.beforeEach((to, from) => routerBeforeEachRoute(to, from))

export default router