import {getRandomNumber} from '../helper'

export default {
    props: {
        type: {
            type: String,
            default: 'button'
        },
        id: {
            type: String,
            default: `button${getRandomNumber()}`
        },
    },
}