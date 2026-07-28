# 前端 Inject 簡介

## Sections (可插入部分)

- list_before_list : 清單頁面 , Vue 內 , 標題下插入
- list_after_list : 清單頁面 , Vue 內 , 表格下插入
- list_before_script : 清單頁面 , 清單 Script 前
- list_after_script : 清單頁面 , 清單 Script 後
- form_before_head : 表單頁面 , Vue 內 , 標題後 , 表頭前
- form_after_head : 表單頁面 , Vue 內 , 表頭後 ， 表身前
- form_before_body : 表單頁面 , Vue 內 , 表頭後 ， 表身前
- form_after_body : 表單頁面 , Vue 內 , 表身後
- form_before_script : 表單頁面 , Vue 內 , 表單 Script 前
- form_after_script : 表單頁面 , Vue 內 , 表單 Script 後 ( Inject 至表單 Vue 容器必須放於此)

## 資料集 ( Dataset ) 可設定選項

- dataset.override.fields
  - 可設定所有的 Field 選項
  - 範例 : that.dataset.override.fields.aaa = { field_readonly: false}

## 插入 Javascript 傳入變數定義

- that 
  - 呼叫 Inject 的 Vue 容器
- id
  - 資料 ID
- fromField
  - 從哪個欄位傳來的資料
  - 會傳入 Field 格式的資料
- pageData
  - 就 Page Data w/ Translation
- referenceData
  - 資料引用回的資料
  - 會傳入 Object
- fields
  - 就 很多個 Fields
  - 範例 : { FieldCode : Field }
- targetSubDataArray / subDataArray
  - 目前行的資料們
  - 範例 : [dataset,dataset,dataset,datase...]
- parentDataset
  - 上一層的資料集
- row
  - 資料集 (單表身的資料集)
- dataset
  - 資料集 (最高層的資料、或是小視窗最高層的資料)
  - 範例 : 
```js
    {
        data : { a : 'a' }, // 資料
        form_id : 12, // 表單 ID
        override : { fields : { FieldCode : { field_readonly : false } } }, // 複寫設定
        patent : dataset, // 上一層的資料集
        schema : form, // Form 資料
        status : 'add', // 狀態 (add,update) 空白為不動作
        subData : { SubFromID : [dataset]}, // 子資料集
        tmpID : 0, // 不須理會 owo
        vue : function return vue instance // 取得 vue 容器用 (用於小視窗)
    }
```

## 插入 Javascript 必須注意事項

- 若對資料進行編輯，必須對 dataset.status 變數設定成 'update' 或是 'add'


## 表單 Javascript 插入點

Javacscript 插入位置為陣列，可以插入很多個 function，使用 push 新增 function

- 方式們
  - window.injects.injectOnInit
    - Add,View,Edit,Copy 都會呼叫到這個
    - 傳入變數
      - that
      - pageData
  - window.injects.injectOnAdd
    - 按下新增按鈕時呼叫
    - 傳入變數
      - that
      - pageData
  - window.injects.injectOnView
    - 按下查看按鈕時呼叫
    - 傳入變數
      - that
      - pageData
      - id
  - window.injects.injectOnEdit
    - 按下編輯按鈕時呼叫
    - 傳入變數
      - that
      - pageData
      - id
  - window.injects.injectOnCopy
    - 按下查看複製時呼叫
    - 傳入變數
      - that
      - pageData
      - id
- 資料引用
  - window.injects.injectOnReferenceWrite
    - 引用資料點選後會呼叫 
    - 傳入變數
      - that
      - pageData
      - fromField
      - referenceData
      - fields
      - dataset
- 表身操作
  - window.injects.injectOnRowAdd
    - 當表身新增時就會呼叫
    - 傳入變數
      - that
      - pageData
      - targetSubDataArray
      - dataset
  - window.injects.injectOnRowDelete
    - 當表身刪除時就會呼叫
    - 傳入變數
      - that
      - pageData
      - parentDataset
      - formID
      - rowIndex
  - window.injects.injectOnBodyInput
    - 當表身有欄位被輸入時呼叫
    - 傳入變數
      - that
      - pageData
      - parentDataset
      - row
      - field
      - formID
      - subDataArray
  - window.injects.injectOnBodyChange.push((that, pageData, parentDataset, row, field, formID, subDataArray) => {})
    - 當表身被更動時呼叫
    - 傳入變數
      - that
      - pageData
      - parentDataset
      - row
      - field
      - formID
      - subDataArray
  - window.injects.injectOnBodyClick.push((that, pageData, parentDataset, row, field, formID, subDataArray) => {})
    - 當表身被點擊時呼叫
    - 傳入變數
      - that
      - pageData
      - parentDataset
      - row
      - field
      - formID
      - subDataArray
- 表頭操作
  - window.injects.injectOnHeadInput
    - 當表頭輸入時呼叫
    - 傳入變數
      - that
      - pageData
      - field
      - dataset
  - window.injects.injectOnHeadChange
    - 當表頭更動時呼叫
    - 傳入變數
      - that
      - pageData
      - field
      - dataset
  - window.injects.injectOnHeadClick
    - 當表頭被點擊時呼叫
    - 傳入變數
      - that
      - pageData
      - field
      - dataset

