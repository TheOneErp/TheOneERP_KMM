# 後端 Inject 簡介

## 注意事項

- 當對 dataset 做更動的時候必須設定 dataset status 為 (add/update)
  - $data['status'] = $data['status'] == "" ? "update" : $data['status'];

## 變數定義

- pageData
  - 就 Page Data w/ Translation
- id
  - 資料 ID
- data
  - dataset
- schema
  - Form 資料
- rules
  - 規則們
- validationResult
  - Validator 會返回的東西
- insertData
  - 新增後的資料 包含 update_at update_by created_at created_by
- updateData
  - 更新後的資料 包含 update_at update_by
- filterResult
  - 塞選後的資料
  - 範例 : 
```js
{
    current_page: 1,
    data: [{data}],
    first_page_url: "http://localhost:8000/api/system/page/31/filter?page=1",
    from: 1,
    last_page: 1,
    last_page_url: "http://localhost:8000/api/system/page/31/filter?page=1",
    next_page_url: null,
    path: "http://localhost:8000/api/system/page/31/filter",
    per_page: 10,
    prev_page_url: null,
    to: 2,
    total: 2,
}
```
- dataset
  - 資料集 (和前端有差)
  - 範例 : 
```js
{
    'form_id': 12, // 表單 ID
    'data': { a : 'a' }, // 資料
    'tmpID': 1, // 不用管，標註資料用
    'status' : 'add', // 狀態 (add,update) 空白為不動作
    'subData' : { SubFromID : [dataset]}, // 子資料集
}
```

## 變數註記定義

- & 在前 = Pass by reference ， 更改相應變數將會變動到傳入的變數
- (Merged) = 將原本資料和寫入或是更新的資料 Merge，且加上資料 ID
- (ReferencePages) = 且將小視窗資料放進 dataset.referencePages[FieldCode] ， 且將原本 data 內的欄位替換成小視窗 ID

## 可插入點

- 查看
  - beforeView
    - 取得資料前時呼叫
    - 傳入變數
      - &$id
      - &$pageData
  - afterView
    - 取得資料後時呼叫
    - 傳入變數
      - &$id
      - &$data
      - &$pageData
- 儲存
  - beforeSave
    - 傳入變數
      - &$data
      - &$pageData
  - beforeDatasetValidation
    - 傳入變數
      - &$dataset (ReferencePages)
      - &$schema
      - &$rules
      - &$pageData
  - afterDatasetValidationSuccess
    - 傳入變數
      - &$dataset (ReferencePages)
      - &$schema
      - &$rules
      - &$validationResult
      - &$pageData
  - afterDatasetValidationFail
    - 傳入變數
      - &$dataset (ReferencePages)
      - &$schema
      - &$rules
      - &$validationResult
      - &$pageData
  - beforeDatasetInsert
    - 傳入變數
      - &$dataset (ReferencePages) (Merged)
      - &$schema
      - &$insertData
      - &$pageData
  - afterDatasetInsert
    - 傳入變數
      - &$dataset (ReferencePages) (Merged)
      - &$schema
      - &$insertData
      - &$pageData
  - beforeDatasetUpdate
    - 傳入變數
      - &$dataset  (ReferencePages) (Merged)
      - &$schema
      - &$updateData
      - &$pageData
  - afterDatasetUpdate
    - 傳入變數
      - &$dataset  (ReferencePages) (Merged)
      - &$schema
      - &$updateData
      - &$pageData
  - afterSuccessSave
    - 傳入變數
      - &$data  (ReferencePages) (Merged)
      - &$pageData
  - afterFailSave
    - 傳入變數
      - &$data  (ReferencePages) (Merged)
      - &$pageData

- 刪除
  - beforeDelete
    - 傳入變數
      - &$data
      - &$pageData
  - afterDeleteSuccess
    - 傳入變數
      - &$data
      - &$pageData
  - afterDeleteFail
    - 傳入變數
      - &$data
      - &$pageData

- 取得清單資料
  - beforeFilter
    - 傳入變數
      - &$requestData
      - &$pageData
  - afterFilter
    - 傳入變數
      - &$requestData
      - &$filterResult
      - &$pageData

- 開啟清單
  - beforeList
    - 傳入變數
      - &$pageData
