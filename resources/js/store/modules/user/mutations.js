const mutations = {
    SET_USER: (state, user)=> {
        state.user = user
    },
    SET_USER_TYPE: (state, userType)=> {
        if (!state.user) {
            return
        }

        if (state.user.userTypes.includes(userType.usableName)) {
            return
        }

        state.user.userTypes.push(userType.usableName)
    }
}


export default mutations
