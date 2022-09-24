

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

    setItem(key, value, parse = false) {
        value = parse ? JSON.stringify(value) : value

        return this.instance.setItem(key, value)
    }
    
    getItem(key, parse = false) {
        let item = this.instance.getItem(key)

        if (parse) {
            return JSON.parse(item)
        }

        return item
    }
    
    removeItem(key) {
        return this.instance.removeItem(key)
    }
    
    clear() {
        return this.instance.clear()
    }
}

export default StorageService