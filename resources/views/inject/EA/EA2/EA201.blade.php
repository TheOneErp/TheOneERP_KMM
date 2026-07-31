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
const bodyId1 = 51;
const littlePageId2 = 6279;
const littlePageIdBodyFormID2 = 6262;
    
    //防止在多產品取回中的搜尋欄按下enter會重新整理整個頁面
    document.addEventListener('submit', function(e){
    e.preventDefault();
    let form = e.target;
    let searchBtn = form.querySelector('button.ts.primary.button');
    if( searchBtn && searchBtn.textContent.trim() === '搜尋' ){
        searchBtn.click();
    }
}, true);
	
    window.injects.injectOnInit.push((that, pageData) => {
		
	})
    window.injects.injectOnAdd.push((that, pageData) => {
		that.dataset.data.undertaker = '{{session("username")}}';
		that.dataset.data.undertakername = '{{session("user_name")}}';
        that.dataset.data.undertakerday = getTodayDate();
	})
    window.injects.injectOnView.push((that, pageData, id) => {})
    window.injects.injectOnEdit.push((that, pageData, id) => {
    })
    window.injects.injectOnCopy.push((that, pageData, id) => {
    })

    window.injects.injectOnReferenceWrite.push((that, pageData, fromField, data, fields, dataset) => {
		if( fromField.field_code == "product_code" ){//換算率
			dataset.data.unit_code = data.unit_code;
			dataset.data.body_rate = "1";
		}
	})

    window.injects.injectOnRowAdd.push((that, pageData, targetSubDataArray, dataset) => {})
    window.injects.injectOnRowDelete.push((that, pageData, parentDataset, formID, rowIndex) => {})

    window.injects.injectOnHeadInput.push((that, pageData, field, dataset) => {})
    window.injects.injectOnHeadChange.push((that, pageData, field, dataset) => {})
    window.injects.injectOnHeadClick.push((that, pageData, field, dataset) => {
        const filedCode = field.field_code;
     // console.log(pageData.page.page_id,dataset);
        if( pageData.page.page_id == littlePageId2 ){
			if( filedCode == 'searchbtn' ){

				that.setFormOption(littlePageIdBodyFormID2,'disableAutoAddOrRemoveRow',true);
				let serachData2 = {
					serach:dataset.data.search_content
				};
                // console.log(serachData1);
				let url = `api/inject/getProduct`;
				searchbtn3(that,serachData2,url,littlePageIdBodyFormID2,true,readonlyBody=[],readonlyHead=[]);

			}else if( filedCode == 'getbtn' ){
				let SubDataLas = 0;
				let getBackStatus = false;
				let depot_code = formVue.dataset.data.depot_code;
				let depot_name = formVue.dataset.data.depot_name;
				let currentRow = "";
				let b_tax="";
				let b_taxrate="";
				let b_currency="";
				let b_rate="";
                let schema = formVue.config.schema[bodyId1];
				for( let index in dataset.subData[littlePageIdBodyFormID2]){
					if( dataset.subData[littlePageIdBodyFormID2][index].data.choose ==  1 ){
						SubDataLas = formVue.dataset.subData[bodyId1].length - 1 ;
						currentRow = that.parentVue().dataset.subData[bodyId1][SubDataLas]
						//console.log(currentRow);
						if( !formVue.checkRowIsEmpty(currentRow, schema) ){
							that.parentVue().addEmptyRow(that.parentVue().dataset,bodyId1);
							SubDataLas = formVue.dataset.subData[bodyId1].length - 1 ; //暫時寫在最後一行
						}
						for( let key in dataset.subData[littlePageIdBodyFormID2][index].data) {
							//console.log(key);

							if( ( key != "choose" ) ){
								formVue.dataset.subData[bodyId1][SubDataLas]['data'][key] = dataset.subData[littlePageIdBodyFormID2][index]['data'][key];
							}
                            if(depot_code != null){
								that.parentVue().dataset.subData[bodyId1][SubDataLas]['data']['body_depot_code'] = depot_code;
								that.parentVue().dataset.subData[bodyId1][SubDataLas]['data']['body_depot_name'] = depot_name;
							}
							getBackStatus = true;

						}
						let row = that.parentVue().dataset.subData[bodyId1][SubDataLas];
						let subDataArray = that.parentVue().dataset.subData[bodyId1];
						let parentDataset = that.parentVue().dataset;
						let taxrate = parentDataset.data.taxrate;
						let rate = parentDataset.data.rate;

						that.parentVue().addEmptyRow(that.parentVue().dataset,bodyId1);
					}
			   }


				if( getBackStatus ){
				   alert("取回完成");
                    that.setFormOption(littlePageIdBodyFormID2,'disableAutoAddOrRemoveRow',true);
                let serachData2 = {
					serach:dataset.data.search_content
				};
                // console.log(serachData1);
				let url = `api/inject/getProduct`;
				searchbtn(that,serachData2,url,littlePageIdBodyFormID2,true,readonlyBody=[],readonlyHead=[]);
				 }else{
					 alert("無需取回項目");
				 }

			}
        }
    })

    window.injects.injectOnBodyInput.push((that, pageData, parentDataset, row, field, formID, subDataArray) => {})
    window.injects.injectOnBodyChange.push((that, pageData, parentDataset, row, field, formID, subDataArray) => {
		const filedCode = field.field_code;
//		if( filedCode == "product_code" ){
//			row.data.body_rate = "1";
//			let productData = {
//				product_code:row.data.product_code,
//				product_unit:row.data.unit_code
//			};
//		   sendAPIRequest(getURL(`api/inject/changeProductCode`),"post",productData).then(result => {
//			   if( result['product_code'] != "" ){
//				   row.data.product_name = result['product_name'];
//				   row.data.unit_name = result['unit_name'];
//			   }
//			});
//
//		}
	})
    window.injects.injectOnBodyClick.push((that, pageData, parentDataset, row, field, formID, subDataArray) => {})

</script>

@endsection