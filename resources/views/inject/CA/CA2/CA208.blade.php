
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
	const mainPageId = 4248;
    const bodyId1 = 4227;
    window.injects.injectOnInit.push((that, pageData) => {
            // that.dataset.override.fields.aaa = { field_readonly: false}
    })
    window.injects.injectOnAdd.push((that, pageData) => {
        that.dataset.data.h_pmt_date = getTodayDate();
        let subData = that.dataset.subData[bodyId1];
        for (let element of subData) {
            element.override.preventDelete = true;
        }
    })
    window.injects.injectOnView.push((that, pageData, id) => {})
    window.injects.injectOnEdit.push((that, pageData, id) => {
        that.dataset.override.fields['charge_off']  = { field_readonly : true };
        that.dataset.override.fields['searchbtn']  = { field_readonly : true };
        that.dataset.override.fields['select_all']  = { field_readonly : true };
        that.dataset.override.fields['unselect_all']  = { field_readonly : true };
        let subData = that.dataset.subData[bodyId1];
        for (let element of subData) {
            for(let key in element.schema.fields){
				that.$set(element.override.fields, key ,{ field_readonly : true });
			}
            element.override.preventDelete = true;
        }
    })
    window.injects.injectOnCopy.push((that, pageData, id) => {
        // that.dataset.override.fields['charge_off']  = { field_readonly : true };
        // that.dataset.override.fields['searchbtn']  = { field_readonly : true };
        clearTableRow(that,bodyId1);
        let subData = that.dataset.subData[bodyId1];
        for (let element of subData) {
            element.override.preventDelete = true;
        }
    })

    window.injects.injectOnReferenceWrite.push((that, pageData, fromField, referenceData, fields, dataset) => {})

    window.injects.injectOnRowAdd.push((that, pageData, targetSubDataArray, dataset) => {
        if(targetSubDataArray[0].form_id==bodyId1){
            if(that.dataset){
        let subData = that.dataset.subData[bodyId1];

        for (let element of subData) {

                element.override.preventDelete = true;

        }
        }
        }
    })
    window.injects.injectOnRowDelete.push((that, pageData, parentDataset, formID, rowIndex) => {})

    window.injects.injectOnHeadInput.push((that, pageData, field, dataset) => {})
    window.injects.injectOnHeadChange.push((that, pageData, field, dataset) => {})
    window.injects.injectOnHeadClick.push((that, pageData, field, dataset) => {
        const filedCode = field.field_code;
		if( pageData.page.page_id == mainPageId ){
			if( filedCode == 'charge_off' ){
                // console.log("1112333");

                // console.log(data['vendor_code']);
                fullscreenDimmer.loading();
                if(formVue.dataset.data.vendor_code != null){
                    let indexlen=that.dataset.subData[bodyId1].length;
                    let codearray = [];
                    let discountarray = [];
                    for(let i=0;i<=indexlen-1;i++){
                        if(that.dataset.subData[bodyId1][i].data.choose=="1"){
                            if(that.dataset.subData[bodyId1][i].data.source_code!=null){
                                // console.log(that.dataset.subData[bodyId1][i].data.source_code);
                                codearray.push(that.dataset.subData[bodyId1][i].data.source_code);
                                discountarray.push(that.dataset.subData[bodyId1][i].data.amt_discount);
                            }
                        }
                    }
                    data1={
                        source_code:codearray,
                        discount:discountarray,
                    };
                    // console.log(data1["source_code"].length);
                if(data1["source_code"].length!=0){


                url=`api/inject/getChargeOff1`;
                    sendAPIRequest(getURL(url),"post",data1).then(async result => {
                        that.dataset.override.fields['charge_off']  = { field_readonly : true };
                        that.dataset.override.fields['searchbtn']  = { field_readonly : true };
                        that.dataset.override.fields['select_all']  = { field_readonly : true };
                        that.dataset.override.fields['unselect_all']  = { field_readonly : true };
                        let subData = that.dataset.subData[bodyId1];
                        for (let element of subData) {
                            for(let key in element.schema.fields){
				                that.$set(element.override.fields, key ,{ field_readonly : true });
			                }
                        }
                        alert(result['text']);
                        fullscreenDimmer.unloading();
                    });
                }else{
                    alert("請先勾選需沖帳項目，再進行沖帳");
                    fullscreenDimmer.unloading();
                }
                }else{
                    alert("廠商代碼為必填");
                    fullscreenDimmer.unloading();
                }

            }else if(filedCode == 'searchbtn'){
                data={
                    receive_day_s:formVue.dataset.data.receive_day_s,
                    receive_day_e:formVue.dataset.data.receive_day_e,
                    vendor_code:formVue.dataset.data.vendor_code,
                };
                clearTableRow(that,bodyId1);
                formVue.dataset.data.charge_total=0;
                formVue.dataset.data.discount_total=0;
                fullscreenDimmer.loading();
                url=`api/inject/getPayable`;
                // console.log(data['vendor_code']);
                if(data['vendor_code']){
                    sendAPIRequest(getURL(url),"post",data).then(async result => {
                        // console.log(result);
                        // console.log(result.length);
                        let indexlen=result.length;
                        if(indexlen>0){
                            for(j=0;j<indexlen;j++){
                                that.dataset.subData[bodyId1][j].data.source_code=result[j]["receive_code"];
                                that.dataset.subData[bodyId1][j].data.source_date=result[j]["receive_day"];
                                that.dataset.subData[bodyId1][j].data.amt_payable=result[j]["ototal"];//應付
                                that.dataset.subData[bodyId1][j].data.amt_paid=result[j]["amt_paid"];//已付
                                that.dataset.subData[bodyId1][j].data.all_discount=result[j]["amt_discount"];//累計折讓
                                that.dataset.subData[bodyId1][j].data.amt_unpaid=result[j]["amt_unpaid"];//未付
                                that.dataset.subData[bodyId1][j].data.paid=result[j]["amt_unpaid"];
                                that.dataset.subData[bodyId1][j].data.amt_discount=0;
                                if(j<indexlen-1){formVue.addEmptyRow(that.dataset,bodyId1);}
                            }
                            fullscreenDimmer.unloading();
                        }else{
                            alert("篩選範圍內無資料");
                            fullscreenDimmer.unloading();
                        }

                        // console.log(sumReceivable);
                    });
                }else{
                    alert("廠商代碼為必填");
                    fullscreenDimmer.unloading();
                }
            }else if(filedCode == 'select_all'){
                let indexlen=that.dataset.subData[bodyId1].length;
                let sum = 0;
                let sum1 = 0;
                let sum2 = 0;
                let sum3 = 0;
                for(let i=0;i<=indexlen-1;i++){
                    if(that.dataset.subData[bodyId1][i].data.source_code!=null){
                        that.dataset.subData[bodyId1][i].data.choose="1";
                        sum=parseFloat(parseFloat(sum)+parseFloat(formVue.dataset.subData[bodyId1][i].data.paid)).toFixed(2);
                        sum1=parseFloat(parseFloat(sum1)+parseFloat(formVue.dataset.subData[bodyId1][i].data.amt_discount)).toFixed(2);
                        
                    }
                }
                formVue.dataset.data.charge_total=sum;
                formVue.dataset.data.discount_total=sum1;
        
            }else if(filedCode == 'unselect_all'){
                let indexlen=that.dataset.subData[bodyId1].length;
                for(let i=0;i<=indexlen-1;i++){
                    if(that.dataset.subData[bodyId1][i].data.source_code!=null){
                        that.dataset.subData[bodyId1][i].data.choose="0";
                    }
                }
                formVue.dataset.data.charge_total=0;
                formVue.dataset.data.discount_total=0;

            }
        }
    })

    window.injects.injectOnBodyInput.push((that, pageData, parentDataset, row, field, formID, subDataArray) => {})
    window.injects.injectOnBodyChange.push((that, pageData, parentDataset, row, field, formID, subDataArray) => {
        const filedCode = field.field_code;
		if( pageData.page.page_id == mainPageId ){
			if( filedCode == 'choose' ){
                let index = formVue.dataset.subData[bodyId1].findIndex(x => x.selected);

                if(formVue.dataset.subData[bodyId1][index].data.choose=="1"){
                    if(formVue.dataset.subData[bodyId1][index].data.source_code!=null){
                        // console.log("Y");

                        formVue.dataset.data.charge_total=parseFloat(parseFloat(formVue.dataset.data.charge_total)+parseFloat(formVue.dataset.subData[bodyId1][index].data.paid)).toFixed(2);
                        formVue.dataset.data.discount_total=parseFloat(parseFloat(formVue.dataset.data.discount_total)+parseFloat(formVue.dataset.subData[bodyId1][index].data.amt_discount)).toFixed(2);
                       
                    }

                }else{
                    if(formVue.dataset.subData[bodyId1][index].data.source_code!=null){
                        // console.log("N");
                        formVue.dataset.data.charge_total=parseFloat(parseFloat(formVue.dataset.data.charge_total)-parseFloat(formVue.dataset.subData[bodyId1][index].data.paid)).toFixed(2);
                        formVue.dataset.data.discount_total=parseFloat(parseFloat(formVue.dataset.data.discount_total)-parseFloat(formVue.dataset.subData[bodyId1][index].data.amt_discount)).toFixed(2);
                       
                    }
                }
                let currentDataset = parentDataset.subData[bodyId1]
                let schema = that.config.schema[bodyId1]
                let firstRowEmptyFlag = false
                currentDataset.forEach((row, rowIndex) => {
                    if (rowIndex == 0 && that.checkRowIsEmpty(row, schema)) return firstRowEmptyFlag = true // Check first row is empty and dont remove it
                    if (that.checkRowIsEmpty(row, schema)) that.deleteRow(parentDataset,bodyId1,rowIndex)
                });
            }else if(filedCode == 'amt_discount' || filedCode == 'paid' || filedCode == 'prepaid_add' || filedCode == 'prepaid_sub'){
                let indexlen=that.dataset.subData[bodyId1].length;
                let sum = 0;
                let sum1 = 0;
                let sum2 = 0;
                let sum3 = 0;
                for(let i=0;i<=indexlen-1;i++){
                    if(that.dataset.subData[bodyId1][i].data.source_code!=null){
                        if(that.dataset.subData[bodyId1][i].data.choose=="1"){
                            sum=parseFloat(parseFloat(sum)+parseFloat(formVue.dataset.subData[bodyId1][i].data.paid)).toFixed(2);
                            sum1=parseFloat(parseFloat(sum1)+parseFloat(formVue.dataset.subData[bodyId1][i].data.amt_discount)).toFixed(2);

                        }
                    }
                }
                formVue.dataset.data.charge_total=sum;
                formVue.dataset.data.discount_total=sum1;

                let currentDataset = parentDataset.subData[bodyId1]
                let schema = that.config.schema[bodyId1]
                let firstRowEmptyFlag = false
                currentDataset.forEach((row, rowIndex) => {
                    if (rowIndex == 0 && that.checkRowIsEmpty(row, schema)) return firstRowEmptyFlag = true // Check first row is empty and dont remove it
                    if (that.checkRowIsEmpty(row, schema)) that.deleteRow(parentDataset,bodyId1,rowIndex)
                });
            }else{
                let currentDataset = parentDataset.subData[bodyId1]
                let schema = that.config.schema[bodyId1]
                let firstRowEmptyFlag = false
                currentDataset.forEach((row, rowIndex) => {
                    if (rowIndex == 0 && that.checkRowIsEmpty(row, schema)) return firstRowEmptyFlag = true // Check first row is empty and dont remove it
                    if (that.checkRowIsEmpty(row, schema)) that.deleteRow(parentDataset,bodyId1,rowIndex)
                });
            }
        }
    })
    window.injects.injectOnBodyClick.push((that, pageData, parentDataset, row, field, formID, subDataArray) => {})
</script>

@endsection
