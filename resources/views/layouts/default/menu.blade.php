<div class="ts active dimmer inverted">
    <div class="ts loader"></div>
</div>
<ul id="menuItems">

</ul>

@section('script')
@parent
<script>
    let MENU_ITEMS_HTML = `
        <ul class="subMenu" style="max-height:0px"><hr>$$content$$</ul>
    `
    let MENU_ITEM_HTML = `
        <li class="menuItem $$classes$$" page_id="$$page_id$$">
            <div class="menuButton"></div>
            <a href="$$href$$" class="menuText">&nbsp;&nbsp; $$text$$</a>
            $$content$$
        </li>
    `

    let menu = [];

    let findMenuItem = (toFindPageID, items) => {
        let result = null;
        for(let item of items){
            result = item.pageID == toFindPageID ? item : result
            if (item.subItems.length != 0) {
                let subResult = findMenuItem(toFindPageID, item.subItems)
                result = subResult !== null ? subResult : result
            }
        }
        return result
    }

    let sortMenuItems = (items) => {
        items.sort((a,b) => a.order - b.order)
    }

    let buildMenuTree = (pages,itemsArray,items) => {
        for(let page of pages){
            let pageObject = {
                pageID: page.page_id,
                text: page.page_text,
                order: page.page_order,
                subItems: []
            }
            itemsArray.push(pageObject)
            buildMenuTree(items.filter(item => item.page_module == page.page_id),pageObject.subItems,items)
        }
        itemsArray.sort((a,b) => {
            if(a.subItems.length != 0 && b.subItems.length != 0){
                return a.order - b.order
            }else{
                return b.subItems.length - a.subItems.length
            }
        })
    }

    let buildMenu = (items,parentElement) => {
        let content = ``
        for(let item of items){
            let html =  MENU_ITEM_HTML.replace("$$text$$",item.text).replace("$$page_id$$",item.pageID)
            if(item.subItems.length == 0){
                content += html.replace("$$href$$","{{ route('system.page.list.show',['page_id'=>"~"]) }}".replace("~",item.pageID)).replace("$$classes$$","").replace("$$content$$","")
            }else{
                content += html.replace("$$href$$","javascript:void(0)").replace("$$classes$$","collapsed").replace("$$content$$",MENU_ITEMS_HTML.replace("$$content$$",buildMenu(item.subItems)))
            }
        }
        return content
    }

    let toggleCollapsed = (element) => {
        let classList = Array.from(element.classList)
        if(classList.includes("collapsed")){
            element.className = "menuItem uncollapsed"
            element.querySelector(".subMenu").style.maxHeight = element.querySelector(".subMenu").scrollHeight + "px"
        } else if (classList.includes("uncollapsed")) {
            element.className = "menuItem collapsed"
            element.querySelector(".subMenu").style.maxHeight = "0px"
        }
        const setParentHeight = (parentElement,toAddHeight) => {
            toAddHeight = toAddHeight + parentElement.scrollHeight;
            if(parentElement == null || parentElement.id == "menu") return
            let subMenu = Array.from(parentElement.childNodes).find(el => el.className == "subMenu")
            if(subMenu){
                parentElement.className = "menuItem uncollapsed"
                subMenu.style.maxHeight = (subMenu.scrollHeight + toAddHeight) + "px"
            }
            setParentHeight(parentElement.parentElement,toAddHeight);
        }
        setParentHeight(element.parentElement,element.querySelector(".subMenu") ? element.querySelector(".subMenu").scrollHeight : 0)
    }

    let onMenuItemClick = (element) => {
        let listenElements = Array.from(element.childNodes).filter(el => ["DIV","A"].includes(el.tagName) || el.nodeName == "#text")
        listenElements.forEach(el => el.addEventListener("click", e => {
            toggleCollapsed(element)
        }))
    }

    let uncollapsedMenuByPageID = (pageID) => {
        let targetMenuItem = document.querySelector("[page_id='"+pageID+"']")
        if(targetMenuItem){
            toggleCollapsed(targetMenuItem)
        }
    }


    sendAPIRequest("{{ route("system.menu") }}")
        .then(data => {
            buildMenuTree(data.filter(item => item.page_module == 0),menu,data);
            document.getElementById('menuItems').innerHTML = buildMenu(menu)
            Array.from(document.getElementsByClassName("menuItem")).forEach(item => onMenuItemClick(item))
            document.querySelector("#menu").removeChild(document.querySelector(".ts.active.dimmer.inverted"))
            if(window.pageID) setTimeout(() => uncollapsedMenuByPageID(window.pageID),200)
        }
    )

</script>
@endsection
