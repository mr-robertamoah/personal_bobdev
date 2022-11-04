import {ref, watchEffect,} from 'vue'
import type {Ref} from 'vue'
import type GeneralObjectInterface from '../types/GeneralObjectInterface'

export default function useValidationErrors() {

    let errors = ref<GeneralObjectInterface|null>()

    function hasErrors(newErrors: Ref<GeneralObjectInterface>|null = null) {
        let errs = newErrors ? newErrors : errors
        
        for (const key in errs.value) {
            if ((errs.value as GeneralObjectInterface)[key]) {
                return true
            }
        }

        return false
    }
    

    function setErrors(responseErrors: GeneralObjectInterface, newErrors: Ref<GeneralObjectInterface>|null)
    {
        for (const errorKey in responseErrors) {
            
            if (newErrors?.value.hasOwnProperty(errorKey)) {
                newErrors.value[errorKey] = responseErrors[errorKey]
                continue
            }

            if (!errors.value?.hasOwnProperty(errorKey)) {
                continue
            }

            errors.value[errorKey] = responseErrors[errorKey]
        }
    }

    function clearError(errorKey: string, newErrors: Ref<GeneralObjectInterface>|null = null)
    {
        if (newErrors?.value.hasOwnProperty(errorKey)) {
            newErrors.value[errorKey] = null
            return
        }

        if (!errors.value?.hasOwnProperty(errorKey)) {
            return
        }
        
        errors.value[errorKey] = null
    }

    function errorWatchEffects(data: GeneralObjectInterface, newErrors: Ref<GeneralObjectInterface>|null = null)
    {
        Object.keys(data).forEach(key => {
            watchEffect(() => {
                data[key]
                
                clearError(key, newErrors)
            })
        });
    }

    return {
        errors,
        setErrors,
        clearError,
        errorWatchEffects,
        hasErrors
    }
}