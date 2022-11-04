import ApiService from "../services/api.service"
import useAlert from "./useAlert"


export default function useJobs(){
    let {setDangerAlertMessage} = useAlert()
    async function createAndAttachJob(
        {name, description, attach= false, userId = null}: 
        {name: string, description?:string, attach?: boolean, userId?: string|number|null}
    ) {
        let formData = new FormData()

        formData.set('name', name)

        if (description) {
            formData.set('description', description)
        }
        
        if (userId) {
            userId = String(userId)
            formData.set('user_id', userId)
        }

        if (attach) {
            formData.set('attach', JSON.stringify(attach))
        }

        let response = await ApiService.post(`/job/create`, formData)

        if (response.status != 200) {
            setDangerAlertMessage({
                message: response.data.message,
                duration: 500
            })
        }

        return response.data.job
    }

    return {
        createAndAttachJob
    }
}