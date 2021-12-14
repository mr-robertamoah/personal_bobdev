<template>
    <nav 
        class="flex w-10/12 min-w-400 mx-auto items-center px-5 py-3 flex-nowrap z-50 sticky top-0 transition-all"
        :class="[icon === 'times' ? 'justify-between bg-blue-200' : 'justify-end']"
    >
        <div
            class="flex-grow-0 flex-shrink-0 pr-2"
            :class="[icon === 'times' ? 'visible' : 'invisible']"
        >
            <logo-component></logo-component>
        </div>
        <div
            class="flex justify-center w-full"
            :class="[icon === 'times' ? 'visible' : 'invisible']"
        >
            <template
                v-for="(item, index) in navItems"
                :key="index"
            >
                <div
                    v-if="item.notRoute"
                    class="group transition-colors duration-300 px-3 py-1 cursor-pointer hover:bg-blue-300 relative flex-grow-0 mx-1" 
                >{{item.name}}</div>
                <router-link 
                    v-else
                    custom
                    :to="{name: item.routeName ? item.routeName : item.name}"
                    v-slot="{navigate, isActive, isExactActive}"
                >
                    <div
                        @click="navigate"
                        v-if="item.name != 'login' || (item.name == 'login' && !isExactActive)"
                        class="group transition-colors duration-300 px-3 py-1 cursor-pointer hover:bg-blue-300 relative 
                            flex-grow-0 mx-1"
                        :class="{'border-white text-white border-b-2': isExactActive}"
                    >
                        {{item.name}}
                        <div 
                            class="group-hover:visible ease-out group-hover:translate-y-0 group-hover:opacity-100 invisible opacity-0 
                                transition -translate-y-3 duration-300 flex flex-col items-center justify-center w-52 absolute top-full 
                                bg-blue-200 -left-16"
                            :class="{'': isActive, 'border-white text-white border-b-2': isExactActive}"
                            v-if="item.subItems"
                        >
                            <router-link 
                                class="text-sm hover:text-base hover:text-blue-800 p-2 hover:bg-blue-300 w-full cursor-pointer 
                                    transition-colors duration-300 text-center"
                                v-for="(subItem, index) in item.subItems"
                                :key="index"
                                :class="[index != 0 ? 'mt-1' : 'm-0']"
                                :to="{name: 'project', params: {projectName: subItem.routeName ? subItem.routeName : subItem.name}}"
                                :tag="'div'"
                            >{{subItem.name}}</router-link>
                        </div>
                    </div>
                </router-link>
            </template>
        </div>
        <div @click="clickedNav" class="flex-grow-0 flex-shrink-0 pl-5 cursor-pointer">
            <font-awesome-icon :icon="['fa', icon]"></font-awesome-icon>
        </div>
    </nav>
</template>

<script lang="ts">
import LogoComponent from './LogoComponent.vue'
import {NavItem} from '../../ts/types/NavItem'
import {PropType, defineComponent} from 'vue'
    export default defineComponent({
        components: {
            LogoComponent,
        },
        props: {
            navItems: {
                type: Array as PropType<NavItem[]>,
                default() {
                    return []
                }
            },
        },
        data() {
            return {
                icon: 'bars',
                currentItem: '',
            }
        },
        methods: {
            clickedNav() {
                if (this.icon === 'bars') {
                    this.icon = 'times'
                    return
                }

                this.icon = 'bars'
            },
            hoveringOverItem(item: {name: string}) {
                this.currentItem = item.name
            }
        },
    })
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