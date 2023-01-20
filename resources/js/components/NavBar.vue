<template>
    <div
        class="absolute top-0 left-0 w-full"
        :class="[icon === 'times' ? 'h-screen' : 'h-content']"
        v-if="route.name !== 'admin'">
        <nav 
            class="flex w-11/12 sm:w-10/12 min-w-400 mx-auto items-center px-5 py-3 flex-nowrap z-30 sticky top-0 
                transition-all rounded-b-xl "
            :class="[icon === 'times' ? 'justify-between bg-blue-200' : 'justify-end max-h-12']"
        >
            <div
                class="flex-grow-0 flex-shrink-0 pr-2"
                :class="[icon === 'times' ? 'visible' : 'invisible']"
            >
                <logo-component></logo-component>
            </div>
            <div
                class="flex justify-center w-full text-sm sm:text-base flex-wrap"
                :class="[icon === 'times' ? 'visible' : 'invisible']"
            >
                <!-- eslint-disable -->
                <router-link 
                    v-if="!! user"
                    custom
                    :to="{name: 'userProfile', params: {username: user.username}}"
                    v-slot="{isExactActive, navigate}: {isExactActive: boolean, navigate: (e: MouseEvent)=> void}"
                >
                <!-- eslint-enable -->
                    <div
                        :title="user.username"
                        class="group transition-colors duration-300 px-3 py-1 cursor-pointer hover:bg-blue-300 hover:rounded mb-1  relative 
                            flex-grow-0 mx-1 text-ellipsis max-w-[100px] sm:max-w-[150px] md:max-h-[200px] overflow-hidden" 
                        :class="{'border-white text-white border-b-2': isExactActive, 'hidden': computedRouteName == 'userProfile'}"
                        @click="navigate"
                    >{{user.username}}</div>
                </router-link>
                <!-- eslint-disable -->
                
                <router-link 
                    v-if="isAdmin || isSuperAdmin"
                    custom
                    :to="{name: 'admin'}"
                    v-slot="{isExactActive, navigate}: {isExactActive: boolean, navigate: (e: MouseEvent)=> void}"
                >
                <!-- eslint-enable -->
                    <div
                        class="group transition-colors duration-300 px-3 py-1 cursor-pointer hover:bg-blue-300 hover:rounded mb-1  relative 
                            flex-grow-0 mx-1" 
                        :class="{'border-white text-white border-b-2': isExactActive, 'hidden': computedRouteName == 'admin'}"
                        @click="navigate"
                    >admin</div>
                </router-link>
                <template
                    v-for="(item, index) in navItems"
                    :key="index"
                >   
                    <div
                        v-if="item.notRoute"
                        class="group transition-colors duration-300 px-3 py-1 cursor-pointer hover:bg-blue-300 hover:rounded mb-1  relative 
                            flex-grow-0 mx-1" 
                        :class="{'hidden': !isLoggedin && item.name == 'logout', 'pulse text-white text-sm': loggingOut}"
                        @click="clickedNavItem(item)"
                    >{{`${loggingOut ? 'logging out...' : item.name}`}}</div>
                    <!-- eslint-disable -->
                    <router-link 
                        v-if="!item.notRoute && (item.name !== 'login' || (item.name == 'login' && !isLoggedin))"
                        custom
                        :to="{name: item.routeName ? item.routeName : item.name}"
                        v-slot="{navigate, isActive, isExactActive}: {isActive: boolean, isExactActive: boolean, navigate: (e: MouseEvent)=> void}"
                    >
                        <!-- eslint-enable -->
                        <div
                            @click="navigate"
                            class="group transition-colors duration-300 px-3 py-1 cursor-pointer hover:bg-blue-300 hover:rounded mb-1  relative 
                                flex-grow-0 mx-1"
                        >
                            <div 
                                :class="{'border-white text-white border-b-2': isExactActive || isActive}"
                                class=""
                            >
                                {{item.name}}
                            </div>
                            <div 
                                class="group-hover:visible ease-out group-hover:translate-y-0 group-hover:opacity-100 invisible opacity-0 
                                    transition -translate-y-3 duration-300 flex flex-col items-center justify-center w-52 absolute top-full 
                                    bg-blue-300 -left-16 z-10 rounded"
                                v-if="item.subItems"
                            >
                                <router-link 
                                    v-slot="{isExactActive, isActive, navigate}"
                                    class="text-sm hover:text-base hover:text-blue-900 p-2 hover:bg-blue-400 hover:rounded mb-1 w-full cursor-pointer 
                                        transition-all duration-300 text-center text-black border-none"
                                    v-for="(subItem, index) in item.subItems"
                                    :key="index"
                                    :to="{name: 'project', params: {projectName: subItem.routeName ? subItem.routeName : subItem.name}}"
                                    :tag="'div'"
                                >
                                    <div class=""
                                        :class="[index != 0 ? 'mt-1' : 'm-0', {'text-base text-white': isExactActive || isActive}]"
                                        @click="navigate"
                                    >{{subItem.name}}</div>
                                </router-link>
                            </div>
                        </div>
                    </router-link>
                </template>
            </div>
            <div @click="clickedNav" class="flex-grow-0 flex-shrink-0 pl-5 cursor-pointer">
                <font-awesome-icon :icon="['fa', icon]"></font-awesome-icon>
            </div>
        </nav>
        <div class="absolute left-0 top-0 w-full h-full bg-transparent z-10"
            v-if="icon == 'times'"
            @click="clickedNav"
        ></div>
    </div>
</template>

<script setup lang="ts">
import LogoComponent from './LogoComponent.vue'
import type {NavItem} from '../../ts/types/NavItem'
import type User from '../../ts/types/User'
import {computed, ref} from 'vue'
import type {PropType} from 'vue'
import {useStore} from 'vuex'
import {useRouter, useRoute, type RouteRecordName} from 'vue-router'
import useCheckLoginStatus from '../composables/useCheckLoginStatus'
import useUserTypes from '../composables/useUserTypes'

    let {
        isAdmin, isSuperAdmin
    } = useUserTypes()

    let props = defineProps({
        navItems: {
            type: Array as PropType<NavItem[]>,
            default() {
                return []
            }
        },
        close: {
            default: false,
            type: Boolean
        }
    })

    let store = useStore()
    let router = useRouter()
    let route = useRoute()
    let {isLoggedin} = useCheckLoginStatus()
    let user = computed<User>(() => {
        return store.state.user.user
    })
    let computedRouteName = computed(()=> route.name)
    let loggingOut = ref(false)
    let icon = ref<string>('bars')
    let currentItem = ref<string>('')

    function clickedNavItem(item: NavItem) {

        if (item.name == 'logout') {
            logout()

            return
        }
    }

    async function logout() {
        if (!isLoggedin) {
            return
        }
        loggingOut.value = true

        let response = await store.dispatch('logout')
        
        loggingOut.value = false

        console.log(response)
        if (response.status != 200) {
            return
        }

        if (route.name == 'home') {
            
            router.push({name: 'login'})
            
            return
        }
        
        router.push({name: 'home'})
    }

    function clickedNav() {
        if (icon.value === 'bars') {
            icon.value = 'times'
            return
        }

        icon.value = 'bars'
    }

    function hoveringOverItem(item: NavItem) {
        currentItem.value = item.name
    }
</script>

<style lang="scss" scoped>

    .item{

        &:hover{
            
           .sub-item{
                visibility: visible;
                opacity: 1;
                transform: translateY(0);
                transition: all .5s ease-in;
            }
        }
    }

    .sub-item{
        transition: all .5s ease-in;
        visibility: hidden;
        opacity: 0;
        transform: translateY(-10px);
    }
</style>