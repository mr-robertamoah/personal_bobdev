import {reactive} from 'vue'

export default function useAlert()
{
    let defaultData = {
        alertMessage: '',
        alertStatus: '',
        alertDuration: 1000
    }

    let alertData = reactive({...defaultData})

    function clearAlertMessage()
    {
        alertData.alertDuration = 1000

        alertData.alertStatus = ''

        alertData.alertMessage = ''
    }

    function setAlertMessage(data)
    {
        if (!data.message) {
            return
        }

        alertData.alertDuration = data.duration

        alertData.alertStatus = data.success ? 'success' : (data.danger ? 'danger' : '')

        alertData.alertMessage = data.message
    }

    function setSuccessAlertMessage(data)
    {
        setAlertMessage({
            ...data, success: true, danger: false
        })
    }

    function setDangerAlertMessage(data)
    {
        setAlertMessage({
            ...data, danger: true, success: false
        })
    }

    return {
        clearAlertMessage,
        alertData,
        setAlertMessage,
        setSuccessAlertMessage,
        setDangerAlertMessage
    }
}