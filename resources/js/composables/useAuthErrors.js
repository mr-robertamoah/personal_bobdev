import {ref, watchEffect, unref} from 'vue'

export default function useAuthErrors() {
    
    let errors = ref({
        username: null,
        email: null,
        password:  null,
        passwordConfirmation:  null,
    })

    function hasErrors(newErrors = null) {
        let errs = newErrors ? newErrors : errors
        
        for (const key in errs.value) {
            if (errs.value[key]) {
                return true
            }
        }

        return false
    }

    function setErrors(responseErrors, newErrors = null)
    {
        for (const errorKey in responseErrors) {
            
            if (newErrors?.value.hasOwnProperty(errorKey)) {
                newErrors.value[errorKey] = responseErrors[errorKey]
                continue
            }

            if (!errors.value.hasOwnProperty(errorKey)) {
                continue
            }

            errors.value[errorKey] = responseErrors[errorKey]
        }
    }

    function clearError(errorKey, newErrors = null)
    {
        if (newErrors?.value.hasOwnProperty(errorKey)) {
            newErrors.value[errorKey] = null
            return
        }

        if (!errors.value.hasOwnProperty(errorKey)) {
            return
        }
        
        errors.value[errorKey] = null
    }

    function errorWatchEffects(data, newErrors = null)
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