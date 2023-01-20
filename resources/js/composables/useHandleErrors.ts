import useAlert from "./useAlert";


export default function useHandleErrors () {
    let {setDangerAlertMessage} = useAlert()

    function handleErrors(status: string|number, message: string|null, prioritizeMessage?: boolean) {
        console.log(status == 500 && !prioritizeMessage);
        
        if (prioritizeMessage && message) {
            setDangerAlertMessage({
                message: message,
                duration: 500
            })

            return {status: false}
        }
        
        if (status == 500) {
            setDangerAlertMessage({
                message: "Sorry, something happened. Please try again later.",
                duration: 500
            })

            return {status: false}
        }
        
        setDangerAlertMessage({
            message: message ?? "Sorry, something happened. Please try again later.",
            duration: 500
        })

        return {status: false}
    }

    return {
        handleErrors
    }
    
}