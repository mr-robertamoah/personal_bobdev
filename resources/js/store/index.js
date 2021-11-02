import {createStore} from 'vuex'
import state from './states'
import mutations from './mutations'
import actions from './actions'
import getters from './getters'

const store = createStore({
    state,
    mutations,
    actions,
    getters
})

export default store