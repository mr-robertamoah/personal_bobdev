import {myStorage} from '../app'

const routerBeforeEachRoute = (to, from) => {

    let isLoggedIn = !!myStorage.getItem('loggedIn', true)
    
    if (to.meta.requiresAuthentication && !isLoggedIn) {
        
        return false
    }

    if (to.meta.shouldNotBeLoggedin && isLoggedIn) {

        return from ?? {name: 'home'}
    }
    
    return true
}

export {routerBeforeEachRoute}

