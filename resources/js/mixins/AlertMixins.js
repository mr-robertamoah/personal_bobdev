export default {
    data() {
        return {
            alertData: {
                success: false,
                danger: false,
                message: '',
                duration: 1000
            }
        }
    },
    methods: {
        hideAlert() {
            this.alertData.message = ''
            this.alertData.success = false
            this.alertData.danger = false
            this.alertData.duration = 1000
        },
        setSuccessAlert(message) {
            this.alertData.success = true
            this.alertData.message = message
        },
        setSuccessAlert(message) {
            this.alertData.danger = true
            this.alertData.message = message
        },
    },
}