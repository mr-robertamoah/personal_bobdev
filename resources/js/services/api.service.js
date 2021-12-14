import TokenService from "./token.service"

const { default: axios } = require("axios")


const ApiService = {
    init: (baseURL) => {
        axios.defaults.baseURL = baseURL
    },

    setHeadersPostToFormData: () => {
        axios.defaults.headers.post['Content-Type'] = 'multipart/form-data'
    },

    setHeadersPostToDefault: () => {
        axios.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded'
    },

    setHeadersAuthorization: () => {
        axios.defaults.headers.common['Authorization'] = TokenService.hasToken() ? `Bearer ${TokenService.getToken()}` : ''
    },

    removeHeadersAuthorization: () => {
        axios.defaults.headers.common['Authorization'] = ``
    },

    get: (url, data = null) => {
        return axios.get(url, {data})
    },

    post: async (url, data, hasMultipart = false) => {
        
        let config = hasMultipart ? {headers: {'Content-Type': 'multipart/form-data'}} : {}

        try {

            console.log('data',  data)

            let res = await axios.post(url, data, config)

            return res
        } catch (error) {
            
            return error.response
        }
        
    },

    put: (url, data) => {
        return axios.put(url, data)
    },

    delete: (url, data = null) => {
        return axios.delete(url, {data})
    },

    custom(data){
        return axios(data)
    },
}

export default ApiService