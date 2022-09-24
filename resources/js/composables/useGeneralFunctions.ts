

export default function useGeneralFunctions(){

    function getPluralBasedOnNumber(num: Number) {
        if (num == 1) {
            return ''
        }

        return 's'
    }

    return {getPluralBasedOnNumber}
}