
const routes = [
    {
        name: 'register',
        path: '/register',
        component: () => import(/* webpackChunkName: 'Register' */ "../views/Register.vue")
    },
    {
        name: 'login',
        path: '/login',
        component: () => import(/* webpackChunkName: 'Login' */ "../views/Login.vue")
    },
    {
        name: 'home',
        path: '/',
        component: () => import(/* webpackChunkName: 'Home' */ "../views/Home.vue")
    },
    {
        name: 'notFound',
        path: '/404',
        alias: '/:pathMatch(.*)*',
        component: () => import(/* webpackChunkName: '404' */ "../views/404.vue")
    }
]

export default routes