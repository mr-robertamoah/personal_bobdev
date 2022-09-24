import type { PropType } from "vue";

export default function useOtherElementProps() {
    return {
        icon: {
            type: Array as PropType<String[]>,
            default: []
        },
        errors: {
            type: Array as  PropType<String[]|null>,
            default: null
        },
        info: {
            type: Array as PropType<String[]|null>,
            default: null
        },
    }
}