import {ref} from 'vue'
import type AlertDataInterface from '../types/AlertDataInterface'
import type AlertDataObjectInterface from '../types/AlertDataObjectInterface'
import useEmitter from './useEmitter'

export default function useAlert()
{
    let {emitter} = useEmitter()

    let defaultData = {
        alertMessage: '',
        alertStatus: '',
        alertDuration: 1000
    }

    let alertData = ref<AlertDataInterface>({
        ...defaultData
    })

    function clearAlertMessage()
    {
        alertData.value.alertDuration = defaultData.alertDuration

        alertData.value.alertStatus = ''

        alertData.value.alertMessage = ''

        emitter.emit('updatedAlertData', alertData.value)
    }

    function setAlertMessage(data: AlertDataObjectInterface)
    {
        if (!data.message) {
            return
        }

        alertData.value.alertDuration = data.duration ?? defaultData.alertDuration

        alertData.value.alertStatus = data.success ? 'success' : (data.danger ? 'danger' : '')

        alertData.value.alertMessage = data.message

        emitter.emit('updatedAlertData', alertData.value)
    }

    function setSuccessAlertMessage(data: AlertDataObjectInterface)
    {
        setAlertMessage({
            ...data, success: true, danger: false
        })
    }

    function setDangerAlertMessage(data: AlertDataObjectInterface)
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