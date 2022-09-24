const getters = {
    isLoggedIn(state) {
        return !!state.user
    },
    userTypes(state) {
        return state.user ? state.user.userTypes : []
    }
}


export default getters