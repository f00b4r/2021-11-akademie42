import { createStore } from "vuex";

export const store = createStore({
    modules: {
        numbers: {
            namespaced: true,
            state() {
                return {
                    clicks: 0
                }
            },
            mutations: {
                INCREMENT(state, {size}) {
                    state.clicks += size;
                }
            },
            actions: {
                increment({commit}, {size}) {
                    commit('INCREMENT', {size});
                }
            },
        },
        user: {
            namespaced: true,
            state() {
                return {
                    jwt: null,
                }
            },
            mutations: {
                SET_JWT(state, {jwt}) {
                    state.jwt = jwt;
                },
                RESET_JWT(state) {
                    state.jwt = null;
                }
            },
            actions: {
                async init({commit}, {jwt}) {
                    commit('SET_JWT', {jwt});
                },
                async login({commit}, {username, password}) {
                    try {
                        const res = await fetch({
                            url: 'http://localhost:8080/api/v1/login',
                            method: 'POST',
                            body: {username, password},
                        });

                        const data = await res.json();
                        const value = {jwt: data.data.jwt};

                        commit('SET_JWT', value);

                        localStorage.setItem('APP_JWT', value);

                        return {value, error: null};
                    } catch (e) {
                        console.error(e);
                        this.$flashes.add("error during loging");
                    }
                },
                async logout({commit}) {
                    commit('RESET_JWT');
                }
            },
        }
    }
})