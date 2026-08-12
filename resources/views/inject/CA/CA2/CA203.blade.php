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
	const bodyId1 = 64;
    const mainPageId = 64;
    const littlePageId = 73;
    const littlePageIdBodyFormID = 78;

    //防止表單欄位按下enter造成瀏覽器「隱式送出」，進而誤觸表單內第一個按鈕(例如取回小視窗的圖示按鈕)跳出視窗
    document.addEventListener('keydown', function(e){
    if( e.key === 'Enter' && e.target.tagName === 'INPUT' ){
        let form = e.target.closest('form');
        if( form ){
            e.preventDefault();
            let searchBtn = form.querySelector('button.ts.primary.button');
            if( searchBtn && searchBtn.textContent.trim() === '搜尋' ){
                searchBtn.click();
            }
        }
    }
}, true);

    window.injects.injectOnInit.push((that, pageData) => {

	})
    window.injects.injectOnAdd.push((that, pageData) => {
		that.dataset.data.undertaker = '{{session("username")}}';
		that.dataset.data.undertakername = '{{session("user_name")}}';
        that.dataset.data.abort_day = getTodayDate();
        that.dataset.data.undertakerday = getTodayDate();
        /*
        if('{{session("username")}}' == "phaith" || '{{session("username")}}' == "yltest" || '{{session("username")}}' == "ocean"){
            that.dataset.schema.fields.osubtotal.field_show_on_form = false ;
            that.dataset.schema.fields.otax.field_show_on_form = false ;
            that.dataset.schema.fields.ototal.field_show_on_form = false ;
            that.dataset.schema.fields.ssubtotal.field_show_on_form = false ;
            that.dataset.schema.fields.stax.field_show_on_form = false ;
            that.dataset.schema.fields.stotal.field_show_on_form = false ;
            that.dataset.subData[bodyId1][0].schema.fields.body_subtotal.field_show_on_form = false ;
        }*/
	})
    window.injects.injectOnView.push((that, pageData, id) => {
        /*
        if('{{session("username")}}' == "phaith" || '{{session("username")}}' == "yltest" || '{{session("username")}}' == "ocean"){
            that.dataset.schema.fields.osubtotal.field_show_on_form = false ;
            that.dataset.schema.fields.otax.field_show_on_form = false ;
            that.dataset.schema.fields.ototal.field_show_on_form = false ;
            that.dataset.schema.fields.ssubtotal.field_show_on_form = false ;
            that.dataset.schema.fields.stax.field_show_on_form = false ;
            that.dataset.schema.fields.stotal.field_show_on_form = false ;
            that.dataset.subData[bodyId1][0].schema.fields.body_subtotal.field_show_on_form = false ;
        }*/
    })
    window.injects.injectOnEdit.push((that, pageData, id) => {
        that.dataset.status = 'update'
        /*
        if('{{session("username")}}' == "phaith" || '{{session("username")}}' == "yltest" || '{{session("username")}}' == "ocean"){
            that.dataset.schema.fields.osubtotal.field_show_on_form = false ;
            that.dataset.schema.fields.otax.field_show_on_form = false ;
            that.dataset.schema.fields.ototal.field_show_on_form = false ;
            that.dataset.schema.fields.ssubtotal.field_show_on_form = false ;
            that.dataset.schema.fields.stax.field_show_on_form = false ;
            that.dataset.schema.fields.stotal.field_show_on_form = false ;
            that.dataset.subData[bodyId1][0].schema.fields.body_subtotal.field_show_on_form = false ;
        }*/
        let subData = that.dataset.subData[bodyId1];
        for (let element of subData) {
            if (element.schema) {
                if( element.data.receive_code != null ){
                    element.override.fields['product_code'] = {
                        field_readonly: true
                    };
                    formVue.$set(formVue.dataset.override.fields, 'vendor_code' ,{ field_readonly : true });
                }
            }
        }
    })
    window.injects.injectOnCopy.push((that, pageData, id) => {
        let subData = that.dataset.subData[bodyId1];
        /*
        if('{{session("username")}}' == "phaith" || '{{session("username")}}' == "yltest" || '{{session("username")}}' == "ocean"){
            that.dataset.schema.fields.osubtotal.field_show_on_form = false ;
            that.dataset.schema.fields.otax.field_show_on_form = false ;
            that.dataset.schema.fields.ototal.field_show_on_form = false ;
            that.dataset.schema.fields.ssubtotal.field_show_on_form = false ;
            that.dataset.schema.fields.stax.field_show_on_form = false ;
            that.dataset.schema.fields.stotal.field_show_on_form = false ;
            that.dataset.subData[bodyId1][0].schema.fields.body_subtotal.field_show_on_form = false ;
        }*/
        for (let element of subData) {
            if (element.schema) {
                if( element.data.receive_code != null ){
                    element.override.fields['product_code'] = {
                        field_readonly: true
                    };
                    formVue.$set(formVue.dataset.override.fields, 'vendor_code' ,{ field_readonly : true });
                }
            }
        }
    })

    window.injects.injectOnReferenceWrite.push((that, pageData, fromField, data, fields, dataset) => {
		let form_id = dataset.form_id;
		if( fromField.field_code == "depot_code" ){
			for (let element of dataset.subData[bodyId1]) {
				if( element.data.product_code ){
					element.data.body_depot_code = data.depot_code;
					element.data.body_depot_name = data.depot_name;
				}
			}
		}else if( fromField.field_code == "product_code"){//換算率
		    // dataset.data.unit_code = data.unit_code;
			dataset.data.body_rate = "1";
			dataset.data.body_depot_code = that.dataset.data.depot_code;
			dataset.data.body_depot_name = that.dataset.data.depot_name;
			let subDataArray = that.dataset.subData[bodyId1];
			let parentDataset = that.dataset;
			let taxrate = parentDataset.data.taxrate;
			let rate = parentDataset.data.rate;
			let dataPrice = data.purchase_price;

			osubtotal = counSubtotal(dataset,subDataArray,parentDataset,"body_subtotal","body_num","body_price","osubtotal","discount",dataPrice)
			countOriginal(osubtotal,parentDataset,"osubtotal","otax","ototal",taxrate,subDataArray,"body_subtotal");
		    dataset.data.ssubtotal = parseFloat(dataset.data.osubtotal * rate).toFixed(2);
            dataset.data.stax = parseFloat(dataset.data.otax * rate).toFixed(2);
            dataset.data.stotal =parseFloat(dataset.data.ototal * rate).toFixed(2);
		}else if( fromField.field_code == "currency" ){
			let rate = data.currency_exchrate; //匯率
			let osubtotal = dataset.data.osubtotal;
			dataset.data.ssubtotal = parseFloat(dataset.data.osubtotal * rate).toFixed(2);
            dataset.data.stax = parseFloat(dataset.data.otax * rate).toFixed(2);
            dataset.data.stotal =parseFloat(dataset.data.ototal * rate).toFixed(2);
		}else if( fromField.field_code == "tax" ){
			let taxrate = data.tax_taxrate;
			let rate = dataset.data.rate;
			let osubtotal = dataset.data.osubtotal;
            let subDataArray = dataset.subData[bodyId1];
			countOriginal(osubtotal,dataset,"osubtotal","otax","ototal",taxrate,bodyId1,"body_subtotal");
		    dataset.data.ssubtotal = parseFloat(dataset.data.osubtotal * rate).toFixed(2);
            dataset.data.stax = parseFloat(dataset.data.otax * rate).toFixed(2);
            dataset.data.stotal =parseFloat(dataset.data.ototal * rate).toFixed(2);
		}else if( fromField.field_code == "unit_code" ){
			let subDataArray = that.dataset.subData[bodyId1];
			let parentDataset = that.dataset;
			let taxrate = parentDataset.data.taxrate;
			let rate = parentDataset.data.rate;
			let dataPrice = data.body_purchase_price;

			osubtotal = counSubtotal(dataset,subDataArray,parentDataset,"body_subtotal","body_num","body_price","osubtotal","discount",dataPrice)
			countOriginal(osubtotal,parentDataset,"osubtotal","otax","ototal",taxrate,subDataArray,"body_subtotal");
			countStandard(osubtotal,parentDataset,rate,"ssubtotal","stax","stotal","otax","ototal");
		}else if(fromField.field_code == "vendor_code" ){
                formVue.dataset.data.source_receive_code.data.vendor_code=data.vendor_code;
        }else if( fromField.field_code == "source_receive_code" ){
        } 
	})

    window.injects.injectOnRowAdd.push((that, pageData, targetSubDataArray, dataset) => {})
    window.injects.injectOnRowDelete.push((that, pageData, parentDataset, formID, rowIndex) => {
        if( pageData.page.page_id == mainPageId ){
            if(formID==bodyId1){
        let subData = that.dataset.subData[bodyId1];

            let num = 0;
            for (let key in subData) {
                if (subData[key].schema && key != rowIndex ) {
//                    console.log(element)
                    if( subData[key].data.receive_code != null ){
                        num++
                        break;
                    }
                }
            }

            if(num==0){
                formVue.$set(formVue.dataset.override.fields, 'vendor_code' ,{ field_readonly : false });
            }else{
                formVue.$set(formVue.dataset.override.fields, 'vendor_code' ,{ field_readonly : true });
            }
        let rate = parentDataset.data.rate;
		let taxrate = parentDataset.data.taxrate;
		if(that.dataset.subData[64][rowIndex].data.body_subtotal != null && that.dataset.subData[64][rowIndex].data.body_subtotal !=  ".00"){
		osubtotal = parseFloat(that.dataset.data.osubtotal- parseFloat(that.dataset.subData[64][rowIndex].data.body_subtotal)).toFixed(2);
		osubtotal2 = parseFloat(that.dataset.data.ototal- parseFloat(that.dataset.subData[64][rowIndex].data.body_subtotal)).toFixed(2);
		if(formVue.dataset.data.tax =="稅內含"){
		formVue.dataset.data.osubtotal = parseFloat(osubtotal2/(1 + taxrate )).toFixed(2);
		formVue.dataset.data.otax = parseFloat(osubtotal2 - formVue.dataset.data.osubtotal).toFixed(2);
		formVue.dataset.data.ototal = osubtotal2;
		}else{
		formVue.dataset.data.osubtotal = osubtotal;
		formVue.dataset.data.otax = parseFloat(osubtotal*taxrate).toFixed(2);
		formVue.dataset.data.ototal = parseFloat(parseFloat(formVue.dataset.data.osubtotal) + parseFloat(formVue.dataset.data.otax)).toFixed(2);
		}
		formVue.dataset.data.ssubtotal = parseFloat(formVue.dataset.data.osubtotal * rate).toFixed(2);
		formVue.dataset.data.stax = parseFloat(formVue.dataset.data.otax * rate).toFixed(2);
		formVue.dataset.data.stotal = parseFloat(formVue.dataset.data.ototal * rate).toFixed(2);
		}
    }
}
    })

    window.injects.injectOnHeadInput.push((that, pageData, field, dataset) => {})
    window.injects.injectOnHeadChange.push((that, pageData, field, dataset) => {
		const filedCode = field.field_code;
		if( filedCode == "taxrate" ){
			let taxrate = dataset.data.taxrate;
			let rate = dataset.data.rate;
			let osubtotal = dataset.data.osubtotal;
            let subDataArray = dataset.subData[bodyId1];

		   countOriginal(osubtotal,dataset,"osubtotal","otax","ototal",taxrate,bodyId1,"body_subtotal");
		   countStandard(osubtotal,dataset,rate,"ssubtotal","stax","stotal","otax","ototal");
		}else if( filedCode == "rate" ){
			let rate = dataset.data.rate;
			let osubtotal = dataset.data.osubtotal;
			countStandard(osubtotal,dataset,rate,"ssubtotal","stax","stotal","otax","ototal");

		}
	})
    window.injects.injectOnHeadClick.push((that, pageData, field, dataset) => {
        const filedCode = field.field_code;
		if( pageData.page.page_id == littlePageId ){
			if( filedCode == 'searchbtn' ){
                that.setFormOption(littlePageIdBodyFormID,'disableAutoAddOrRemoveRow',true);
				let serachData = {
					receive_codeS:dataset.data.receive_codeS,
					receive_codeE:dataset.data.receive_codeE,
					receive_dayS:dataset.data.receive_dayS,
					receive_dayE:dataset.data.receive_dayE,
					product_codeS:dataset.data.product_codeS,
					product_codeE:dataset.data.product_codeE,
					vendor_code:dataset.data.vendor_code
				};
				let url =`api/inject/getCompanyReceive`;
				searchbtn(that,serachData,url,littlePageIdBodyFormID,true,readonlyBody=[],readonlyHead=[]);
			}else if( filedCode == 'getbtn' ){
                let SubDataLas = 0;
                let getBackStatus = false;
				let mainSubData = that.parentDataset;
                let depot_code = formVue.dataset.data.depot_code;
				let depot_name = formVue.dataset.data.depot_name;
                let currentRow = "";
                let schema = formVue.config.schema[bodyId1];
				for( let index in dataset.subData[littlePageIdBodyFormID]){
                    if( dataset.subData[littlePageIdBodyFormID][index].data.choose ==  1 ){
                        SubDataLas = formVue.dataset.subData[bodyId1].length - 1 ; //暫時寫在最後一行
                        currentRow = that.parentVue().dataset.subData[bodyId1][SubDataLas];
                        if( !formVue.checkRowIsEmpty(currentRow, schema) ){
							that.parentVue().addEmptyRow(that.parentVue().dataset,bodyId1);
							SubDataLas = formVue.dataset.subData[bodyId1].length - 1 ; //暫時寫在最後一行
						}
                        for( let key in dataset.subData[littlePageIdBodyFormID][index].data) {
                           if(key == 'depot_code'){
                               formVue.dataset.subData[bodyId1][SubDataLas]['data']['body_depot_code'] =  dataset.subData[littlePageIdBodyFormID][index]['data'][key];
                           }else if(key == 'depot_name'){
                               formVue.dataset.subData[bodyId1][SubDataLas]['data']['body_depot_name'] =  dataset.subData[littlePageIdBodyFormID][index]['data'][key];
                           }else if(key == 'remarks'){
                               formVue.dataset.subData[bodyId1][SubDataLas]['data']['body_remarks'] =  dataset.subData[littlePageIdBodyFormID][index]['data'][key];
                           }else{
                               formVue.dataset.subData[bodyId1][SubDataLas]['data'][key] =  dataset.subData[littlePageIdBodyFormID][index]['data'][key];
                               if(key == "receive_code"){
                                    formVue.$set(formVue.dataset.subData[bodyId1][SubDataLas].override.fields, 'product_code' ,{ field_readonly : true });
                                }
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
						osubtotal = counSubtotal(row,subDataArray,parentDataset,"body_subtotal","body_num","body_price","osubtotal","discount");
                        if( row.data.body_subtotal != null ){
                            row.data.body_subtotal = parseFloat(row.data.body_subtotal).toFixed(2);
                        }
						countOriginal(osubtotal,parentDataset,"osubtotal","otax","ototal",taxrate,subDataArray,"body_subtotal");
						countStandard(osubtotal,parentDataset,rate,"ssubtotal","stax","stotal","otax","ototal");
                        that.parentVue().addEmptyRow(that.parentVue().dataset,bodyId1);
                    }

			   }
				if( getBackStatus ){
				   alert("取回完成");
                    formVue.$set(formVue.dataset.override.fields, 'vendor_code' ,{ field_readonly : true });
				 }else{
					 alert("無需取回項目");
				 }
			}
		}
    })

    window.injects.injectOnBodyInput.push((that, pageData, parentDataset, row, field, formID, subDataArray) => {})
    window.injects.injectOnBodyChange.push((that, pageData, parentDataset, row, field, formID, subDataArray) => {
		const filedCode = field.field_code;
		let osubtotal = 0; //原幣小計
		let otax = 0; //原幣稅金
		let ototal = 0; //原幣合計

		let ssubtotal =0; //本位幣小計
		let stax = 0; //本位幣稅金
		let stotal = 0; //本位幣合計
		let rate = parentDataset.data.rate; //匯率
        if(filedCode == "choose"){
            let currentDataset = parentDataset.subData[littlePageIdBodyFormID]
            let schema = that.config.schema[littlePageIdBodyFormID]
            let firstRowEmptyFlag = false
            currentDataset.forEach((row, rowIndex) => {
                if (rowIndex == 0 && that.checkRowIsEmpty(row, schema)) return firstRowEmptyFlag = true // Check first row is empty and dont remove it
                if (that.checkRowIsEmpty(row, schema)) that.deleteRow(parentDataset,littlePageIdBodyFormID,rowIndex)
            });
        }
        osubtotal = counSubtotal(row,subDataArray,parentDataset,"body_subtotal","body_num","body_price","osubtotal","discount");
        if( row.data.body_subtotal != null ){
           row.data.body_subtotal = parseFloat(row.data.body_subtotal).toFixed(2);
        }
        //原幣
        let taxrate = parentDataset.data.taxrate;
        countOriginal(osubtotal,parentDataset,"osubtotal","otax","ototal",taxrate,subDataArray,"body_subtotal");
        //本位幣
		parentDataset.data.ssubtotal = parseFloat(parentDataset.data.osubtotal * rate).toFixed(2);
				parentDataset.data.stax = parseFloat(parentDataset.data.otax * rate).toFixed(2);
				parentDataset.data.stotal =parseFloat(parentDataset.data.ototal * rate).toFixed(2);
	})
    window.injects.injectOnBodyClick.push((that, pageData, parentDataset, row, field, formID, subDataArray) => {
        const filedCode = field.field_code;
		if( pageData.page.page_id == mainPageId ){
			if( filedCode == 'source_receive_code' ){
				row.data.source_receive_code.data.vendor_code = parentDataset.data.vendor_code;
                let subData = that.dataset.subData[bodyId1];
                clearEmptyRowBylittlePage(subData,littlePageId,littlePageIdBodyFormID,'source_receive_code',"receive_no")
			}
		}
	})


</script>

@endsection
