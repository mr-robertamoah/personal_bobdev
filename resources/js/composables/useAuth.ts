import {useStore} from 'vuex'
import {ref, watchEffect} from 'vue'

export default function useAuth() {
    let user = useStore().state.user
    let isLoggedin = ref(false)

    watchEffect(()=>{
        isLoggedin.value = !!user
    })

    return {
        isLoggedin
    }
}