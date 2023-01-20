import {createStore} from 'vuex'
import state from './state'
import mutations from './mutations'
import actions from './actions'
import getters from './getters'
import {user, admin} from './modules'

const store = createStore({
    modules: {
        user,
        admin
    },
    state,
    mutations,
    actions,
    getters
})

export default store