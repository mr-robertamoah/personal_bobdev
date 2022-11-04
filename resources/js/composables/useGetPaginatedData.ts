import { reactive, ref, watchEffect } from "vue";
import ApiService from "../services/api.service";
import useAlert from "./useAlert";

export default function useGetPaginatedData() {
    let {setDangerAlertMessage} = useAlert()
    let searchData = reactive({
        data: [],
        links: {
            path: '',
            per_page: 0,
            to: null,
            total: 0
        },
        meta: {
            current_page: 0,
            from: 0,
            last_page: 0,
        },
    })
    let hasMoreData = ref<boolean>(false)

    watchEffect(()=>{
        if (searchData.meta.current_page == searchData.meta.last_page) {
            hasMoreData.value = false
            return
        }

        hasMoreData.value = true
    })

    function clearData(){
        searchData = {
            data: [],
            links: {
                path: '',
                per_page: 0,
                to: null,
                total: 0
            },
            meta: {
                current_page: 0,
                from: 0,
                last_page: 0,
            },
        }
    }

    async function getData({searchText, queryItem = 'name', url}: {searchText: String, queryItem?: String, url: String}) {
        if (!searchText.length) {
            clearData()
            return;
        }
        let query = `?${queryItem}=${searchText}`
        
        let response = await ApiService.get(`${url}/${query}`)
    
        console.log(response)

        if (response.status != 200) {
            setDangerAlertMessage({
                message: response.data.message,
                duration: 500
            })
            
            return
        }
        
        searchData.links = response.data.links
        searchData.meta = response.data.meta
        searchData.data = response.data.data
    }

    let debouncedGetData = _.debounce(getData, 500)

    async function getMoreData({searchText, queryItem = 'name', url, dataAddition = 'push'}: {
        searchText: String, queryItem?: String, url: String, dataAddition?: String
    }) {
        if (!searchText.length) {
            clearData()
            return;
        }

        if (!hasMoreData.value) {
            return
        }

        let query = `?${queryItem}=${searchText}`
        
        url = `${url}?page=${searchData.meta.current_page + 1}`
        
        let response = await ApiService.get(`${url}${query}`)
    
        console.log(response)

        if (response.status != 200) {
            setDangerAlertMessage({
                message: response.data.message,
                duration: 500
            })
            
            return
        }
        
        searchData.links = response.data.links
        searchData.meta = response.data.meta
        searchData.data[dataAddition](...response.data.data)
    }

    let debouncedGetMoreData = _.debounce(getMoreData, 500)


    return {
        searchData,
        hasMoreData,
        getData,
        debouncedGetData,
        debouncedGetMoreData,
        getMoreData,
        clearData,
    }
}