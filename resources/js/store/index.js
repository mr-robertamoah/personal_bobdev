import Vue from 'vue'
import Vuex from 'vuex'
import state from './states'
import mutations from './mutations'
import actions from './actionss'
import getters from './getters'

Vue.use(Vuex)

const store = new Vuex.Store({
    state: state,
    mutations,
    actions,
    getters
})

export default store