import { watch, ref } from "vue"
import { useStore } from "vuex"


export default function useCheckLoginStatus() {
    
    let isLoggedin = ref<boolean>(false)
    let store = useStore()
    
    watch(() => store?.getters['user/isLoggedIn'], () => {
        isLoggedin.value = store.getters['user/isLoggedIn']
    })

    return {isLoggedin}
}