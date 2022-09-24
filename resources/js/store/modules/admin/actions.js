import ApiService from "../../../services/api.service"

const actions = {
    async getUsers({commit}, data) {
        let url = 'admin/users'

        if (data.page) {
            url += `?page=${data.page}`
        }

        let response = await ApiService.get(url)
        console.log(response)

        if (response.status == 200 && response.data) {
            
            commit('SET_USERS', {...response.data})
        }

        return response
    }
}


export default actions