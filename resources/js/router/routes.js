
const routes = [
    {
        name: 'home',
        path: '/',
        component: () => import(/* webpackChunkName: 'Home' */ "../views/Home.vue")
    },
    {
        name: 'register',
        path: '/',
        component: () => import(/* webpackChunkName: 'Register' */ "../views/Register.vue")
    },
    {
        name: 'login',
        path: '/',
        component: () => import(/* webpackChunkName: 'Login' */ "../views/Login.vue")
    },
    {
        name: 'notFound',
        path: '/404',
        alias: '*',
        component: () => import(/* webpackChunkName: '404' */ "../views/404.vue")
    }
]

export default routes