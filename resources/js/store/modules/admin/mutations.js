const mutations = {
    SET_USERS: (state, {data, meta})=> {
        if (meta.current_page === 1) {
            state.users = [
                ...data
            ]

            return
        }
        
        state.users.push(...data)
    }
}


export default mutations
