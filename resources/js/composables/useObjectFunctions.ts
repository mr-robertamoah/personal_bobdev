import { isRef, type Ref } from "vue"
import type GeneralObjectInterface from "../types/GeneralObjectInterface";

export function setObjectValuesToEmptyString(obj: GeneralObjectInterface|Ref<GeneralObjectInterface>) {
    
    for (const key in isRef(obj) ? obj.value : obj) {
        if (isRef(obj)) {
            obj.value[key] = ''
            continue
        }

        obj[key] = ''
    }
}