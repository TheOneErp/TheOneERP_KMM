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
    // 請款單 BA213 / 取回出貨單小視窗 BA214
    const invMainPageId = 6288;
    const invBodyId = 6274;        // 請款單表身
    const invPopPageId = 6289;     // 取回出貨單小視窗
    const invPopBodyId = 6276;     // 小視窗表身

    const invNum = v => { const n = Number(v); return Number.isFinite(n) ? n : 0; };
    const invRows = (ds) => ((ds.subData && ds.subData[invBodyId]) || []).filter(r => r.data && r.data.ship_code);

    // 公式：小計=表身小計加總；稅金=表頭小計×稅率；合計=小計+稅金；未收=合計-已收
    function invRecalc() {
        const d = formVue.dataset && formVue.dataset.data;
        if (!d) return;
        let sub = 0;
        invRows(formVue.dataset).forEach(r => { sub += invNum(r.data.body_subtotal); });
        sub = Math.round(sub * 100) / 100;
        const tax = Math.round(sub * invNum(d.taxrate) * 100) / 100;
        const total = Math.round((sub + tax) * 100) / 100;
        if (invNum(d.osubtotal) !== sub || d.osubtotal === null || d.osubtotal === undefined) d.osubtotal = sub;
        if (invNum(d.otax) !== tax || d.otax === null || d.otax === undefined) d.otax = tax;
        if (invNum(d.ototal) !== total || d.ototal === null || d.ototal === undefined) d.ototal = total;
        const out = Math.round((total - invNum(d.amt_recd)) * 100) / 100;
        if (invNum(d.amt_outstanding) !== out || d.amt_outstanding === null || d.amt_outstanding === undefined) d.amt_outstanding = out;
    }

    // 表身小計 / 稅率變動時自動重算
    (function () {
        let timer = null;
        formVue.$watch(() => {
            const d = formVue.dataset && formVue.dataset.data;
            if (!d) return '';
            return d.taxrate + '|' + d.amt_recd + '|' + invRows(formVue.dataset).map(r => r.data.body_subtotal).join(',');
        }, () => {
            clearTimeout(timer);
            timer = setTimeout(() => {
                if (formVue.config.mode.includes('view')) return;
                invRecalc();
            }, 0);
        });
    })();

    // 承辦人不可被複製
    (function () {
        const LOCK = ['承辦人代碼', '承辦人名稱'];
        const style = document.createElement('style');
        style.textContent = '.no-copy-field, .no-copy-field * { -webkit-user-select: none !important; user-select: none !important; }';
        document.head.appendChild(style);
        const mark = () => document.querySelectorAll('.inline.field').forEach(el => {
            const l = el.querySelector(':scope > label');
            if (l && LOCK.some(t => l.textContent.trim().startsWith(t))) el.classList.add('no-copy-field');
        });
        new MutationObserver(mark).observe(document.body, { childList: true, subtree: true });
        mark();
    })();

    function invSetPrintReadonly(that, ro) {
        that.dataset.override.fields['print_btn'] = { field_readonly: ro };
    }

    window.injects.injectOnInit.push((that, pageData) => {})
    window.injects.injectOnAdd.push((that, pageData) => {
        invSetPrintReadonly(that, true);
        that.dataset.data.undertaker = '{{session("username")}}';
        that.dataset.data.undertakername = '{{session("user_name")}}';
        that.dataset.data.undertakerday = getTodayDate();
        that.dataset.data.invoice_date = getTodayDate();
        that.dataset.data.amt_recd = 0;
        that.dataset.data.osubtotal = 0;
        that.dataset.data.otax = 0;
        that.dataset.data.ototal = 0;
        that.dataset.data.amt_outstanding = 0;
    })
    window.injects.injectOnView.push((that, pageData, id) => {
        invSetPrintReadonly(that, false);
    })
    window.injects.injectOnEdit.push((that, pageData, id) => {
        invSetPrintReadonly(that, false);
    })
    window.injects.injectOnCopy.push((that, pageData, id) => {
        invSetPrintReadonly(that, true);
        // 承辦人不沿用被複製單據，改帶入登入帳號；已收/收款日期清空
        that.dataset.data.undertaker = '{{session("username")}}';
        that.dataset.data.undertakername = '{{session("user_name")}}';
        that.dataset.data.undertakerday = getTodayDate();
        that.dataset.data.invoice_date = getTodayDate();
        that.dataset.data.amt_recd = 0;
        that.dataset.data.recd_date = null;
        that.dataset.data.invoice_code = null;
        // 表身為取回的出貨單明細，複製時清除避免重複請款
        that.dataset.subData[invBodyId].splice(0);
        that.addEmptyRow(that.dataset, invBodyId);
        invRecalc();
    })
    window.injects.injectOnReferenceWrite.push((that, pageData, fromField, referenceData, fields, dataset) => {
        setTimeout(invRecalc, 0);
    })
    window.injects.injectOnRowAdd.push((that, pageData, targetSubDataArray, dataset) => {})
    window.injects.injectOnRowDelete.push((that, pageData, parentDataset, formID, rowIndex) => {
        setTimeout(invRecalc, 0);
    })

    window.injects.injectOnHeadInput.push((that, pageData, field, dataset) => {})
    window.injects.injectOnHeadChange.push((that, pageData, field, dataset) => {})

    window.injects.injectOnHeadClick.push((that, pageData, field, dataset) => {
        const code = field.field_code;

        // ===== 取回出貨單小視窗 =====
        if (pageData.page.page_id == invPopPageId) {
            if (code == 'searchbtn') {
                that.setFormOption(invPopBodyId, 'disableAutoAddOrRemoveRow', true);
                const searchData = {
                    ship_codeS: dataset.data.ship_codeS,
                    ship_codeE: dataset.data.ship_codeE,
                    ship_dateS: dataset.data.ship_dateS,
                    ship_dateE: dataset.data.ship_dateE,
                    product_codeS: dataset.data.product_codeS,
                    product_codeE: dataset.data.product_codeE,
                    client_code: formVue.dataset.data.client_code
                };
                if (!searchData.client_code) { alert("請先選擇客戶代碼"); return; }
                searchbtn(that, searchData, `api/inject/getShipForInvoice`, invPopBodyId, true, [], []);

            } else if (code == 'select_all') {
                for (let index in dataset.subData[invPopBodyId]) {
                    const r = dataset.subData[invPopBodyId][index].data;
                    if (r.ship_code) r.choose = 1;
                }

            } else if (code == 'getbtn') {
                let got = false;
                const schema = formVue.config.schema[invBodyId];
                for (let index in dataset.subData[invPopBodyId]) {
                    const src = dataset.subData[invPopBodyId][index].data;
                    if (src.choose != 1) continue;
                    // 已取回過的出貨單明細不重複取回
                    const dup = (formVue.dataset.subData[invBodyId] || []).some(r => r.data && r.data.source_body_id && r.data.source_body_id == src.source_body_id);
                    if (dup) continue;

                    let last = formVue.dataset.subData[invBodyId].length - 1;
                    if (last < 0 || !formVue.checkRowIsEmpty(formVue.dataset.subData[invBodyId][last], schema)) {
                        that.parentVue().addEmptyRow(that.parentVue().dataset, invBodyId);
                        last = formVue.dataset.subData[invBodyId].length - 1;
                    }
                    const t = formVue.dataset.subData[invBodyId][last].data;
                    ['ship_code', 'ship_date', 'product_code', 'product_name', 'body_num', 'unit_code', 'unit_name', 'body_price', 'body_subtotal', 'source_body_id'].forEach(k => { t[k] = src[k]; });
                    t.remarks = src.remarks;
                    got = true;
                }
                if (got) {
                    that.parentVue().addEmptyRow(that.parentVue().dataset, invBodyId);
                    invRecalc();
                    formVue.$set(formVue.dataset.override.fields, 'client_code', { field_readonly: true });
                    alert("取回完成");
                    that.setFormOption(invPopBodyId, 'disableAutoAddOrRemoveRow', true);
                    searchbtn(that, {
                        ship_codeS: dataset.data.ship_codeS, ship_codeE: dataset.data.ship_codeE,
                        ship_dateS: dataset.data.ship_dateS, ship_dateE: dataset.data.ship_dateE,
                        product_codeS: dataset.data.product_codeS, product_codeE: dataset.data.product_codeE,
                        client_code: formVue.dataset.data.client_code
                    }, `api/inject/getShipForInvoice`, invPopBodyId, true, [], []);
                } else {
                    alert("無需取回項目");
                }
            }

        // ===== 請款單 =====
        } else if (pageData.page.page_id == invMainPageId) {
            if (code == 'print_btn') {
                if (!dataset.data.invoice_code) { alert("請先儲存請款單再列印"); return; }
                fullscreenDimmer.loading();
                sendAPIRequest(getURL(`api/inject/printInvoice`), "post", { invoice_code: dataset.data.invoice_code }).then(async result => {
                    fullscreenDimmer.unloading();
                    if (result.status) {
                        window.open(result.file);
                    } else {
                        alert((result.messages && result.messages[0]) || "{{ $commonTranslations['error.unknown'] . $commonTranslations['contact_maintenance'] }}");
                        console.error(result);
                    }
                })
            }
        }
    })

    window.injects.injectOnBodyInput.push((that, pageData, parentDataset, row, field, formID, subDataArray) => {})
    window.injects.injectOnBodyChange.push((that, pageData, parentDataset, row, field, formID, subDataArray) => {})
    window.injects.injectOnBodyClick.push((that, pageData, parentDataset, row, field, formID, subDataArray) => {})
</script>
@endsection
