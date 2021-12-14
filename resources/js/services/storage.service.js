

const StorageService = class {
    type = 'localStorage'

    possibleTypes = ['localStorage', 'sessionStorage']

    instance = null

    constructor(type) {
        if (!type || typeof type !== 'string' || !this.possibleTypes.includes(type)) {
            return
        }

        this.type = type
    }
    
    setUpStorageBasedOnType() {
        if (this.type === 'localStorage') {
            this.instance = localStorage
            return
        }

        this.instance = sessionStorage
    }

    setItem(key, value) {
        return this.instance.setItem(key, value)
    }
    
    getItem(key) {
        return this.instance.getItem(key)
    }
    
    removeItem(key) {
        return this.instance.removeItem(key)
    }
    
    clear() {
        return this.instance.clear()
    }
}

export default StorageService