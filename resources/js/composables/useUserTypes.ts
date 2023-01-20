import { computed, type Ref } from "vue";
import type User from "../../ts/types/User";
import type UserProfile from "../../ts/types/UserProfile";
import ApiService from "../services/api.service";
import useAlert from "./useAlert";
import useGetUserTypes from "./useGetUserTypes";
import useHandleErrors from "./useHandleErrors";

export default function useUserTypes(user: Ref<User & UserProfile>) {
    let {
        isAdmin,
        isDonor,
        isSuperAdmin,
        isStudent,
        isFacilitator,
        isParent
    } = useGetUserTypes()
    let {setSuccessAlertMessage} = useAlert()
    let {handleErrors} = useHandleErrors()

    async function become(
        {name, userId} : 
        {name: string, userId: string|number}
    ) {
        try {
            let response = await ApiService.post('/user-type/become', {
                userType: name, userId
            })

            if (response.status != 200) {
                handleErrors(response.status, response.data.message, true)

                return {
                    status: false,
                    userType: null
                }
            }

            setSuccessAlertMessage({
                message: response.data.message ?? `Successfully became a ${name}`,
                duration: 500
            })

            return {
                status: true,
                userType: response.data.userType
            }
        } catch(error) {
            handleErrors(error?.response?.status ?? 500, error?.response?.data?.message ?? null, true)

            return {
                status: false,
                userType: null
            }
        }
    }
    
    async function remove(
        {name, userId} : 
        {name: string, userId: string|number}
    ) {

        try{
            let response = await ApiService.post('/user-type/remove', {
                userType: name, userId
            })

            if (response.status != 200) {
                return handleErrors(response.status, response.data.message)
            }

            setSuccessAlertMessage({
                message: response.data.message ?? `Successfully removed ${name} user type.`,
                duration: 500
            })

            return response.data
        } catch (error) {
            handleErrors(error?.response?.status ?? 500, error?.response?.data?.message ?? null, true)

            return {
                status: false
            }
        }
    }

    let isProfileOwnerFacilitator = computed<boolean>(function() {
        return isUserType('facilitator')
    })

    function isUserType(type: string): boolean {
        if (!user.value.userTypes) {
            return false
        }

        return user.value.userTypes.includes(type)
    }

    let hasSkills = computed<boolean>(function(): boolean {
        if (!user.value.skills) {
            return false
        }

        return user.value.skills.length > 0
    })

    let hasJobs = computed<boolean>(function(): boolean {
        if (!user.value.jobs) {
            return false
        }
        
        return user.value.jobs.length > 0
    })

    return {
        isProfileOwnerFacilitator,
        isAdmin,
        isDonor,
        isSuperAdmin,
        isStudent,
        isFacilitator,
        isParent,
        become,
        remove,
        isUserType,
        hasSkills,
        hasJobs
    }
}