import TokenService from "./token.service"
import {router} from "../app"
import axios from "axios"

const ApiService = {
    init: (baseURL) => {
        axios.defaults.baseURL = baseURL

        return ApiService
    },
    
    interceptRequest: (reqFn = null, errFn = null) => {
        axios.interceptors.request.use(
            request => {
                if (reqFn) {
                    return reqFn(request)
                }

                console.log(`request`, request)
                return request
            },
            error => {
                
                if (errFn) {
                    return errFn(error)
                }

                throw error
            }
        )
    },
    
    interceptResponse: (reqFn = null, errFn = null) => {
        axios.interceptors.response.use(
            response => {
                if (reqFn) {
                    return reqFn(response)
                }
                
                return response
            },
            error => {
                
                if (errFn) {
                    return errFn(error)
                }

                if (![401, 419].includes(error.response.status)) {

                    throw error
                }
                
                if (['login', 'projects', 'project', 'home', 'test',].includes(router.currentRoute.value.name)) {
                    throw error
                }

                if (['test'].includes(router.currentRoute.value.name)) {
                    throw error
                }

                router.push({
                    name: 'login', 
                    query: {redirect: router.currentRoute.value.path}, 
                })

                return error
            }
        )
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

    get: async (url, data = null) => {
        return await axios.get(url, {data})
    },

    post: async (url, data, hasMultipart = false) => {
        
        let config = hasMultipart ? {headers: {'Content-Type': 'multipart/form-data'}} : {}

        try {

            let res = await axios.post(url, data, config)

            return res
        } catch (error) {
            return error.response
        }
        
    },

    put: (url, data) => {
        try {
            return axios.put(url, data)
        } catch (error) {
            
            return error.response
        }
    },

    delete: async (url, data = null) => {
        try {
            return await axios.delete(url, {data})
        } catch (error) {
            
            return error.response
        }
    },

    async custom(data){
        try {
            return await axios(data)
        } catch (error) {
            
            return error.response
        }
    },
}

export default ApiService