import {getRandomNumber} from '../helper'

export default {
    props: {
        type: {
            type: String,
            default: 'button'
        },
    },
    created() {
        this.id = `input${getRandomNumber(1000000)}`
    },
    data() {
        return {
            id: ''
        }
    }
}