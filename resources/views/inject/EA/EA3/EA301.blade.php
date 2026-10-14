@section("list_before_list")

@endsection

@section("list_after_list")

@endsection

@section("list_before_script")

@endsection

@section("list_after_script")

@endsection

@section("form_before_head")

@endsection

@section("form_after_head")

@endsection

@section("form_before_body")

@endsection

@section("form_after_body")

@endsection

@section("form_before_script")

@endsection

@section("form_after_script")
<script>
    // 承辦人代碼/承辦人名稱：不可反白、選取、複製
    (function () {
        const LOCK_LABELS = ['承辦人代碼', '承辦人名稱'];
        const style = document.createElement('style');
        style.textContent = '.no-copy-field, .no-copy-field * { -webkit-user-select: none !important; user-select: none !important; } .no-copy-field input { pointer-events: none; }';
        document.head.appendChild(style);

        const markFields = () => {
            document.querySelectorAll('.inline.field').forEach(el => {
                const label = el.querySelector(':scope > label');
                if (label && LOCK_LABELS.some(t => label.textContent.trim().startsWith(t))) {
                    el.classList.add('no-copy-field');
                }
            });
        };
        new MutationObserver(markFields).observe(document.body, { childList: true, subtree: true });
        markFields();

        ['copy', 'cut', 'selectstart', 'contextmenu', 'dragstart'].forEach(type => {
            document.addEventListener(type, e => {
                if (e.target && e.target.closest && e.target.closest('.no-copy-field')) e.preventDefault();
            }, true);
        });
    })();

    window.injects.injectOnInit.push((that, pageData) => {})
    window.injects.injectOnAdd.push((that, pageData) => {
        that.dataset.data.undertaker = '{{session("username")}}';
        that.dataset.data.undertakername = '{{session("user_name")}}';
        that.dataset.data.undertakerday = getTodayDate();
        that.dataset.data.work_date = getTodayDate();
    })
    window.injects.injectOnView.push((that, pageData, id) => {})
    window.injects.injectOnEdit.push((that, pageData, id) => {})
    window.injects.injectOnCopy.push((that, pageData, id) => {
        // 承辦人不沿用被複製單據的資料，改帶入目前登入的帳號
        that.dataset.data.undertaker = '{{session("username")}}';
        that.dataset.data.undertakername = '{{session("user_name")}}';
    })
    window.injects.injectOnReferenceWrite.push((that, pageData, fromField, referenceData, fields, dataset) => {})
    window.injects.injectOnRowAdd.push((that, pageData, targetSubDataArray, dataset) => {})
    window.injects.injectOnRowDelete.push((that, pageData, parentDataset, formID, rowIndex) => {})

    window.injects.injectOnHeadInput.push((that, pageData, field, dataset) => {
        if (field.field_code !== 'today_num') return;

        const todayNum = Number(dataset.data.today_num) || 0;
        const body1Rows = (dataset.subData && dataset.subData['6247']) || [];

        body1Rows.forEach(rowWrapper => {
            const row = rowWrapper.data;
            if (!row || !row.product_code) return;

            // 假設：product_code 首字母 H = 原料類，需請您確認此規則是否可靠
            if (row.product_code.charAt(0) === 'H') {
                row.body_num1 = todayNum;
                row.body_num = (Number(row.body_num1) || 0) * (Number(row.body_num2) || 0);
            }
        });
    })
    window.injects.injectOnHeadChange.push((that, pageData, field, dataset) => {})
    window.injects.injectOnHeadClick.push((that, pageData, field, dataset) => {})

    window.injects.injectOnBodyInput.push((that, pageData, parentDataset, row, field, formID, subDataArray) => {
        // 表身1 本身：數量(body_num1) 或 用量(body_num2) 變動時，重新計算小計
        if (formID === 6247 && (field.field_code === 'body_num1' || field.field_code === 'body_num2')) {
            row.data.body_num = (Number(row.data.body_num1) || 0) * (Number(row.data.body_num2) || 0);
        }

        // 表身2：數量(body_num) 輸入後，帶入表身1 包裝材料類(I) 的 數量(body_num1)
        if (formID === 6248 && field.field_code === 'body_num') {
            const packNum = Number(row.data.body_num) || 0;
            const body1Rows = (parentDataset.subData && parentDataset.subData['6247']) || [];

            body1Rows.forEach(rowWrapper => {
                const r = rowWrapper.data;
                if (!r || !r.product_code) return;

                // 假設：product_code 首字母 I = 包裝材料類，需請您確認此規則是否可靠
                if (r.product_code.charAt(0) === 'I') {
                    r.body_num1 = packNum;
                    r.body_num = (Number(r.body_num1) || 0) * (Number(r.body_num2) || 0);
                }
            });
        }
    })
    window.injects.injectOnBodyChange.push((that, pageData, parentDataset, row, field, formID, subDataArray) => {})
    window.injects.injectOnBodyClick.push((that, pageData, parentDataset, row, field, formID, subDataArray) => {})
</script>
@endsection