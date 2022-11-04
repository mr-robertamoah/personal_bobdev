import { isRef, ref, unref, type Ref } from "vue";
import type User from "../../ts/types/User";
import {defaultUser} from "../../ts/types/User";
import ApiService from "../services/api.service";
import useAlert from "./useAlert";

interface EditableData
{
    id?: string|number,
    firstName?: string,
    surname?: string,
    otherNames?: string,
    email?: string,
    password?: string,
    currentPassword?: string,
    passwordConfirmation?: string,
}

export default function useEditUserInfo(user: User|Ref<User>|null)
{
    let {setDangerAlertMessage} = useAlert()
    let editableUser = ref<User>(defaultUser)
    let editableUserLoading = ref<boolean>(false)

    if (user) {
        editableUser.value = isRef(user) ? unref<User>(user) : user
    }

    async function editInfo(data: EditableData)
    {
        editableUserLoading.value = true

        let response = await ApiService.post(`/user/${editableUser.value.id}/edit-info`, data)

        if (response.status != 200) {

            editableUserLoading.value = false

            setDangerAlertMessage({
                message: response.data.message,
                duration: 500
            })

            return
        }

        editableUser.value = response.data.user

        editableUserLoading.value = false
    }

    async function resetPassword(data: EditableData)
    {
        editableUserLoading.value = true

        let response = await ApiService.post(`/user/${editableUser.value.id}/reset-password`, data)

        if (response.status != 200) {

            editableUserLoading.value = false

            setDangerAlertMessage({
                message: response.data.message,
                duration: 500
            })

            return
        }

        editableUser.value = response.data.user

        editableUserLoading.value = false   
    }


    return {
        editableUser,
        editableUserLoading,
        editInfo,
        resetPassword
    }
}