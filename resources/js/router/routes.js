import ApiService from "../services/api.service"

const routes = [
    {
        name: 'register',
        path: '/register',
        component: () => import(/* webpackChunkName: 'Register' */ "../views/Register.vue"),
        meta: {
            shouldNotBeLoggedin: true
        }
    },
    {
        name: 'admin',
        path: '/admin',
        component: () => import(/* webpackChunkName: 'Admin' */ "../views/Admin.vue"),
        meta: {
            requiresAuthentication: true,
            requiresAdminAccess: true,
        },
        beforeEnter: async (to, from) => {
            let response = await ApiService.get('/admin/verify')

            if (response.data?.isAdmin) {
                
                return true
            }

            return {name: 'home', meta: {errorMessage: "You cannot access this route because you are not an administrator."}}
        }
    },
    {
        name: 'test',
        path: '/test',
        component: () => import(/* webpackChunkName: 'Test' */ "../views/Test.vue"),
        meta: {
            requiresAuthentication: true,
            requiresAdminAccess: true,
        },
    },
    {
        name: 'login',
        path: '/login',
        component: () => import(/* webpackChunkName: 'Login' */ "../views/Login.vue"),
        meta: {
            shouldNotBeLoggedin: true
        },
    },
    {
        name: 'home',
        path: '/',
        component: () => import(/* webpackChunkName: 'Home' */ "../views/Home.vue"),
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
                props: true
            },
            {
                name: 'companyProfile',
                path: 'company/:uuid',
                component: () => import(/* webpackChunkName: 'Home' */ "../views/CompanyProfile.vue"),
                props: true
            }
        ],
        meta: {
            // requiresAuthentication: true
        }
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