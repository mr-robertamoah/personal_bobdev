

const StorageService = class {
    type = 'localStorage'
    instance = null

    constructor(type) {
        if (!type || typeof type !== 'string' || type.length == 0) {
            type = 'localStorage'
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