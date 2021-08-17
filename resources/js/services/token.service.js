
const TokenService = {
    TOKEN: 'bobdev.token',
    USER: 'bobdev.user',
    
    setToken: (value) => {
        return window.myStorage.setItem(TokenService.TOKEN, value)
    },
    
    setUser: (value) => {
        if (typeof value !== 'string') {
            value = JSON.stringify(value)
        }

        return window.myStorage.setItem(TokenService.USER, value)
    },
    
    getToken: () => {
        return window.myStorage.getItem(TokenService.TOKEN)
    },

    hasToken: () => !! TokenService.getToken,
    
    getUser: () => {
        let user = window.myStorage.getItem(TokenService.USER)

        if (typeof user === 'string') {
            user = JSON.parse(user)
        }

        return user
    },
    
    removeToken: () => {
        return window.myStorage.removeItem(TokenService.TOKEN)
    },
    
    removeUser: () => {
        return window.myStorage.removeItem(TokenService.USER)
    },
}

export default TokenService

