import type {NavItem} from './types/NavItem'

const routes: NavItem[] = [
    {name: 'home'},
    {name: 'projects', subItems: [
        {name: 'blog'}, 
        {name: "let's code GH", routeName:'lets-code-gh'}, 
        {name: 'nation building series', routeName:'nation-building-series'}, 
        {name: 'personal finance', routeName:'personal-finance'}, 
        {name: 'web design/development', routeName:'web-design-development'},
    ]},
    {name: 'login'},
    {name: 'logout', notRoute: true},
]

export default routes