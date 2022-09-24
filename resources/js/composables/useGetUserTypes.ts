import { watch } from 'vue';
import { useStore } from 'vuex';
import { ref } from 'vue';
import { UserTypeClass } from '../../ts/types/UserType';

export default function useGetUserTypes() {
    let isAdmin = ref<boolean>(false)
    let isDonor = ref<boolean>(false)
    let isSuperAdmin = ref<boolean>(false)
    let isStudent = ref<boolean>(false)
    let isFacilitator = ref<boolean>(false)
    let isParent = ref<boolean>(false)
    let store  = useStore()

    watch(
        () => store?.getters['user/userTypes'],
        () => {
        
            if (store.getters['user/userTypes']?.includes(UserTypeClass.admin)) {
                isAdmin.value =  true
            }
        
            if (store.getters['user/userTypes']?.includes(UserTypeClass.superAdmin)) {
                isSuperAdmin.value =  true
            }
        
            if (store.getters['user/userTypes']?.includes(UserTypeClass.parent)) {
                isParent.value =  true
            }
        
            if (store.getters['user/userTypes']?.includes(UserTypeClass.donor)) {
                isDonor.value =  true
            }
        
            if (store.getters['user/userTypes']?.includes(UserTypeClass.student)) {
                isStudent.value =  true
            }
        
            if (store.getters['user/userTypes']?.includes(UserTypeClass.facilitator)) {
                isFacilitator.value =  true
            }
            
        }
    )

    return {
        isAdmin,
        isDonor,
        isSuperAdmin,
        isStudent,
        isFacilitator,
        isParent,
    }
}