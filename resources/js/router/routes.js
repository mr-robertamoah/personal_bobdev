
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
        name: 'profile',
        path: '/profile',
        component: () => import(/* webpackChunkName: 'Home' */ "../views/Profile.vue"),
        children: [
            {
                name: 'userProfile',
                path: 'user/:username',
                component: () => import(/* webpackChunkName: 'Home' */ "../views/UserProfile.vue"),
            },
            {
                name: 'companyProfile',
                path: 'company/:uuid',
                component: () => import(/* webpackChunkName: 'Home' */ "../views/CompanyProfile.vue"),
            }
        ]
    },
    {
        name: 'projects',
        path: '/projects',
        component: () => import(/* webpackChunkName: 'Home' */ "../views/Projects.vue"),
        children: [
            {
                name: 'project',
                path: ':projectName',
                component: () => import(/* webpackChunkName: 'Home' */ "../views/Project.vue"),
            }
        ]
    },
    {
        name: 'notFound',
        path: '/404',
        alias: '/:pathMatch(.*)*',
        component: () => import(/* webpackChunkName: '404' */ "../views/404.vue")
    }
]

export default routes