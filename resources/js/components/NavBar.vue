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
            <div class="group transition-colors duration-300 px-3 py-1 hover:bg-blue-300 relative flex-grow-0 mx-1" 
                v-for="(item, index) in navItems"
                :key="index"
            >
                {{item.name}}
                <div 
                    class="group-hover:visible ease-out group-hover:translate-y-0 group-hover:opacity-100 invisible opacity-0 
                        transition -translate-y-3 duration-300 flex flex-col items-center justify-center w-52 absolute top-full 
                        bg-blue-200 -left-16"
                >
                    <div 
                        class="text-sm hover:text-base hover:text-blue-800 p-2 hover:bg-blue-300 w-full cursor-pointer 
                            transition-colors duration-300 text-center"
                        v-for="(item, index) in item.subItems"
                        :key="index"
                        :class="[index != 0 ? 'mt-1' : 'm-0']"
                    >
                        {{item}}
                    </div>
                </div>
            </div>
        </div>
        <div @click="clickedNav" class="flex-grow-0 flex-shrink-0 pl-5 cursor-pointer">
            <font-awesome :icon="['fa', icon]"></font-awesome>
        </div>
    </nav>
</template>

<script>
import LogoComponent from './LogoComponent.vue'
    export default {
        components: {
            LogoComponent,
        },
        props: {
            navItems: {
                type: Array,
                default() {
                    return [
                        {name: 'home', subItems: []},
                        {name: 'projects', subItems: [
                            'blog', 
                            'codeGH', 
                            'nation building series', 
                            'personal finance', 
                            'web design/development'
                        ]},
                    ]
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
            hoveringOverItem(item) {
                this.currentItem = item.name
            }
        },
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