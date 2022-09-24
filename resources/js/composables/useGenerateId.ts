import {ref, onBeforeMount} from 'vue';
import {getRandomNumber} from '../helper'

export default function useGenerateId() {
    let id = ref<string>('')

    onBeforeMount(()=> {
        id.value = `input${getRandomNumber(1000000)}`
    })

    return {id}
}