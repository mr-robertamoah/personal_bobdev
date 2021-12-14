interface NavSubItem {
    name: string,
    routeName?: string,
}

interface NavItem {
    name: string,
    subItems?: NavSubItem[],
    notRoute?: boolean,
}

export {NavItem, NavSubItem}

