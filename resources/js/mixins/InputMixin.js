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
        id: {
            type: String,
            default: `input${getRandomNumber()}`
        },
        icon: {
            type: Array,
            default: []
        },
    },
}