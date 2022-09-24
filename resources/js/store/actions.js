import ApiService from "../services/api.service"
import {myStorage} from "../app"

const actions = {
    registerOrLoginUser: async ({commit}, {url, data})=> {
        await ApiService.get('csrf-cookie')

        let response = await ApiService.post(url, data)

        let user = response?.data?.user

        commit('user/SET_USER', user)

        if (user) {
            myStorage.setItem('loggedIn', true, true)

            return response
        }

        myStorage.setItem('loggedIn', false, true)

        return response
    },

    getUser: async ({commit})=> {
        
        let response = await ApiService.get('/user')
        
        let user = response?.data?.user

        commit('user/SET_USER', user)

        if (user) {
            myStorage.setItem('loggedIn', true, true)

            return response
        }

        myStorage.setItem('loggedIn', false, true)

        return response
    },

    logout: async ({commit}) => {
        
        let response = await ApiService.post('/logout')

        if (response.status == 200) {
            commit('user/SET_USER', null)
            myStorage.setItem('loggedIn', false, true)
        }
        
        return response
    },
}

export default actions