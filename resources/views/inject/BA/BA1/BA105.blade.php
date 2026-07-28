
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
    const mainPageId = 6272;
    const bodyId1 = 6251;
    window.injects.injectOnInit.push((that, pageData) => {
            // that.dataset.override.fields.aaa = { field_readonly: false}
    })
    window.injects.injectOnAdd.push((that, pageData) => {
        let subData = that.dataset.subData[bodyId1];
        for (let element of subData) {
            element.override.preventDelete = true;
        }
    })
    window.injects.injectOnView.push((that, pageData, id) => {})
    window.injects.injectOnEdit.push((that, pageData, id) => {
    })
    window.injects.injectOnCopy.push((that, pageData, id) => {

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
			if(filedCode == 'searchbtn'){
                data={
                    enter_addr:formVue.dataset.data.enter_addr,
                    enter_product:formVue.dataset.data.enter_product,
                };
                clearTableRow(that,bodyId1);
                fullscreenDimmer.loading();
                url=`api/inject/getaddr`;
                // console.log(data['client_code']);
                    sendAPIRequest(getURL(url),"post",data).then(async result => {
                        // console.log(result);
                        // console.log(result.length);
                        let indexlen=result.length;
                        if(indexlen>0){
                            for(j=0;j<indexlen;j++){
                                that.dataset.subData[bodyId1][j].data.client_code=result[j]["client_code"];
                                that.dataset.subData[bodyId1][j].data.client_name=result[j]["client_name"];
                                that.dataset.subData[bodyId1][j].data.addr=result[j]["addr"];
                                that.dataset.subData[bodyId1][j].data.product_code=result[j]["product_code"];
                                that.dataset.subData[bodyId1][j].data.product_name=result[j]["product_name"];
                                that.dataset.subData[bodyId1][j].data.phone=result[j]["phone"];
                                if(j<indexlen-1){formVue.addEmptyRow(that.dataset,bodyId1);}
                            }
                            fullscreenDimmer.unloading();
                        }else{
                            alert("篩選範圍內無資料");
                            fullscreenDimmer.unloading();
                        }

                        // console.log(sumReceivable);
                    });
            }
        }
    })

    window.injects.injectOnBodyInput.push((that, pageData, parentDataset, row, field, formID, subDataArray) => {})
    window.injects.injectOnBodyChange.push((that, pageData, parentDataset, row, field, formID, subDataArray) => {

    })
    window.injects.injectOnBodyClick.push((that, pageData, parentDataset, row, field, formID, subDataArray) => {})
</script>

@endsection
