import { getCurrentInstance } from "vue";


export default function useEmitter() {
    let emitter = getCurrentInstance()?.appContext.config.globalProperties.emitter

    return {
        emitter
    }
}