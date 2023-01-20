const actions = {
    addUserType: function ({commit}, userType){
        console.log(userType);
        commit('SET_USER_TYPE', userType)
    }
}


export default actions