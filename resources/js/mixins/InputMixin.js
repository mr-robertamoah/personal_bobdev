import { getRandomNumber } from "../helper";

export default {
    props: {
        label: {
            type: String,
            default: ''
        },
        type: {
            type: String,
            default: ''
        },
        placeholder: {
            type: String,
            default: ''
        },
        icon: {
            type: Array,
            default: []
        },
        errors: {
            type: Array,
            default: null
        },
        info: {
            type: Array,
            default: null
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