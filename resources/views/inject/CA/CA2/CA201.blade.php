
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
	const bodyId1 = 43;
    const mainPageId = 55;
    let viewstatus = false;
    const littlePageId2 = 5249;
    const littlePageIdBodyFormID2 = 5227;
    window.injects.injectOnInit.push((that, pageData) => {
	})
    window.injects.injectOnAdd.push((that, pageData) => {
		formVue.dataset.data.undertaker = '{{session("username")}}';
		formVue.dataset.data.undertakername = '{{session("user_name")}}';
        that.dataset.data.advanceday = getTodayDate();
        that.dataset.data.undertakerday = getTodayDate();
        if('{{session("username")}}' == "phaith" || '{{session("username")}}' == "yltest" || '{{session("username")}}' == "ocean"){
            that.dataset.schema.fields.osubtotal.field_show_on_form = false ;
            that.dataset.schema.fields.otax.field_show_on_form = false ;
            that.dataset.schema.fields.ototal.field_show_on_form = false ;
            that.dataset.schema.fields.ssubtotal.field_show_on_form = false ;
            that.dataset.schema.fields.stax.field_show_on_form = false ;
            that.dataset.schema.fields.stotal.field_show_on_form = false ;
            that.dataset.subData[bodyId1][0].schema.fields.body_subtotal.field_show_on_form = false ;
        }
	})
    window.injects.injectOnView.push((that, pageData, id) => {
        viewstatus = true;
        if('{{session("username")}}' == "phaith" || '{{session("username")}}' == "yltest" || '{{session("username")}}' == "ocean"){
            that.dataset.schema.fields.osubtotal.field_show_on_form = false ;
            that.dataset.schema.fields.otax.field_show_on_form = false ;
            that.dataset.schema.fields.ototal.field_show_on_form = false ;
            that.dataset.schema.fields.ssubtotal.field_show_on_form = false ;
            that.dataset.schema.fields.stax.field_show_on_form = false ;
            that.dataset.schema.fields.stotal.field_show_on_form = false ;
            that.dataset.subData[bodyId1][0].schema.fields.body_subtotal.field_show_on_form = false ;
        }
    })
     window.injects.injectOnEdit.push((that, pageData, id) => {
        that.dataset.status = 'update';
        viewstatus = false;
        if('{{session("username")}}' == "phaith" || '{{session("username")}}' == "yltest" || '{{session("username")}}' == "ocean"){
            that.dataset.schema.fields.osubtotal.field_show_on_form = false ;
            that.dataset.schema.fields.otax.field_show_on_form = false ;
            that.dataset.schema.fields.ototal.field_show_on_form = false ;
            that.dataset.schema.fields.ssubtotal.field_show_on_form = false ;
            that.dataset.schema.fields.stax.field_show_on_form = false ;
            that.dataset.schema.fields.stotal.field_show_on_form = false ;
            that.dataset.subData[bodyId1][0].schema.fields.body_subtotal.field_show_on_form = false ;
        }
		let subData = that.dataset.subData[bodyId1];
        let len = that.dataset.subData[bodyId1].length;
        let a1 =1;
		for (let element of subData) {
            // if(element.status==undefined){
            //     element.status="update";
            // }
            // if(a1!=len){
            // element.status='update';
            // }else{
            //     element.status='add';
            // }
            // a1=a1+1;
			if(element.schema){
			   for(let key in element.schema.fields){
				   if( element.data.body_quantity != 0 && key != 'body_cancel' ){
                       element.override.fields[key]  = { field_readonly : true };
                       element.override.preventDelete  = true;
                   }
				}
			}
		}

        //表身no有被其他單引用廠商要鎖
        subData.forEach((row, rowIndex) => {
			if( row.data.id ){
				let shipData = {
					temp: 'purchase_no',
					no: row.data.id,
					tables:['CA202_55','CA203_64']
				};
				sendAPIRequest(getURL(`api/inject/cited`),"post",shipData).then(result => {
					if(result == 1){
						that.$set(that.dataset.override.fields, 'vendor_code' ,{ field_readonly : true });
					}
				})
			}
        });

	})
    window.injects.injectOnCopy.push((that, pageData, id) => {
        viewstatus = true;
        if('{{session("username")}}' == "phaith" || '{{session("username")}}' == "yltest" || '{{session("username")}}' == "ocean"){
            that.dataset.schema.fields.osubtotal.field_show_on_form = false ;
            that.dataset.schema.fields.otax.field_show_on_form = false ;
            that.dataset.schema.fields.ototal.field_show_on_form = false ;
            that.dataset.schema.fields.ssubtotal.field_show_on_form = false ;
            that.dataset.schema.fields.stax.field_show_on_form = false ;
            that.dataset.schema.fields.stotal.field_show_on_form = false ;
            that.dataset.subData[bodyId1][0].schema.fields.body_subtotal.field_show_on_form = false ;
        }
        let subData = that.dataset.subData[bodyId1];
		for (let element of subData) {
			element.data.body_quantity = 0;
		}
    })

    window.injects.injectOnReferenceWrite.push((that, pageData, fromField, data, fields, dataset) => {
		let form_id = dataset.form_id;
		if( fromField.field_code == "depot_code" ){
            for (let element of dataset.subData[bodyId1]) {
                if( element.data.product_code ){
                    if( !element.override.fields.body_depot_code ){
                        element.data.body_depot_code = data.depot_code;
                        element.data.body_depot_name = data.depot_name;
                    }else if( !element.override.fields.body_depot_code.field_readonly ){
                        element.data.body_depot_code = data.depot_code;
                        element.data.body_depot_name = data.depot_name;
                    }
                }
                element.status = that.dataset.status;
            }
        }else if( fromField.field_code == "product_code"){//換算率
		//    dataset.data.unit_code = data.unit_code;
			dataset.data.body_rate = "1";
			dataset.data.body_depot_code = that.dataset.data.depot_code;
			dataset.data.body_depot_name = that.dataset.data.depot_name;
			let subDataArray = that.dataset.subData[bodyId1];
			let parentDataset = that.dataset;
			let taxrate = parentDataset.data.taxrate;
			let rate = parentDataset.data.rate;
			let dataPrice = data.purchase_price;

			osubtotal = counSubtotal(dataset,subDataArray,parentDataset,"body_subtotal","body_num","body_price","osubtotal","discount",dataPrice)
			countOriginal(osubtotal,dataset,"osubtotal","otax","ototal",taxrate,bodyId1,"body_subtotal");
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
			countOriginal(osubtotal,dataset,"osubtotal","otax","ototal",taxrate,bodyId1,"body_subtotal",data.tax_name);
			dataset.data.ssubtotal = parseFloat(dataset.data.osubtotal * rate).toFixed(2);
            dataset.data.stax = parseFloat(dataset.data.otax * rate).toFixed(2);
            dataset.data.stotal =parseFloat(dataset.data.ototal * rate).toFixed(2);
		}else if( fromField.field_code == "unit_code"){
			let subDataArray = that.dataset.subData[bodyId1];
			let parentDataset = that.dataset;
			let taxrate = parentDataset.data.taxrate;
			let rate = parentDataset.data.rate;
			let dataPrice = data.body_purchase_price;

			osubtotal = counSubtotal(dataset,subDataArray,parentDataset,"body_subtotal","body_num","body_price","osubtotal","discount",dataPrice)
			countOriginal(osubtotal,dataset,"osubtotal","otax","ototal",taxrate,bodyId1,"body_subtotal");
			dataset.data.ssubtotal = parseFloat(dataset.data.osubtotal * rate).toFixed(2);
            dataset.data.stax = parseFloat(dataset.data.otax * rate).toFixed(2);
            dataset.data.stotal =parseFloat(dataset.data.ototal * rate).toFixed(2);
		}


	})

    window.injects.injectOnRowAdd.push((that, pageData, targetSubDataArray, dataset) => {})
    window.injects.injectOnRowDelete.push((that, pageData, parentDataset, formID, rowIndex) => {
        if( pageData.page.page_id == mainPageId ){
            if(formID==bodyId1){
			let rate = parentDataset.data.rate;
		let taxrate = parentDataset.data.taxrate;
		if(that.dataset.subData[43][rowIndex].data.body_subtotal != null && that.dataset.subData[43][rowIndex].data.body_subtotal !=  ".00"){
		osubtotal = parseFloat(that.dataset.data.osubtotal- parseFloat(that.dataset.subData[43][rowIndex].data.body_subtotal)).toFixed(2);
		osubtotal2 = parseFloat(that.dataset.data.ototal- parseFloat(that.dataset.subData[43][rowIndex].data.body_subtotal)).toFixed(2);
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
		formVue.dataset.data.stax =  parseFloat(formVue.dataset.data.otax * rate).toFixed(2);
		formVue.dataset.data.stotal = parseFloat(formVue.dataset.data.ototal * rate).toFixed(2);

        }
		}
    }
	})

    window.injects.injectOnHeadInput.push((that, pageData, field, dataset) => {})
    window.injects.injectOnHeadChange.push((that, pageData, field, dataset) => {
		const filedCode = field.field_code;
        if( pageData.page.page_id == mainPageId ){
		if( filedCode == "taxrate" ){
			let taxrate = dataset.data.taxrate;
			let rate = dataset.data.rate;
			let osubtotal = dataset.data.osubtotal;

		   countOriginal(osubtotal,dataset,"osubtotal","otax","ototal",taxrate,bodyId1,"body_subtotal");
		   countStandard(osubtotal,dataset,rate,"ssubtotal","stax","stotal","otax","ototal");
		}else if( filedCode == "rate" ){
			let rate = dataset.data.rate;
			let osubtotal = dataset.data.osubtotal;
			countStandard(osubtotal,dataset,rate,"ssubtotal","stax","stotal","otax","ototal");

		}
    }
	})
    window.injects.injectOnHeadClick.push((that, pageData, field, dataset) => {
        const filedCode = field.field_code;
        if( pageData.page.page_id == littlePageId2 ){
			if( filedCode == 'searchbtn' ){

				that.setFormOption(littlePageIdBodyFormID2,'disableAutoAddOrRemoveRow',true);
				let serachData2 = {
					serach:dataset.data.search_content
				};
                // console.log(serachData1);
				let url = `api/inject/getProduct1`;
				searchbtn(that,serachData2,url,littlePageIdBodyFormID2,true,readonlyBody=[],readonlyHead=[]);

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
				let url = `api/inject/getProduct1`;
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
        if( pageData.page.page_id == mainPageId ){
		let osubtotal = 0; //原幣小計
		let otax = 0; //原幣稅金
		let ototal = 0; //原幣合計

		let ssubtotal =0; //本位幣小計
		let stax = 0; //本位幣稅金
		let stotal = 0; //本位幣合計
		let rate = parentDataset.data.rate; //匯率

		if( row.selected){
//			row.override.fields.product_code.field_readonly = true

		   	if( row.data.body_quantity == 0 ){
				if( filedCode == "body_quantity"){

				}else if(filedCode == "body_num" || filedCode == "body_price"){

					osubtotal = counSubtotal(row,subDataArray,parentDataset,"body_subtotal","body_num","body_price","osubtotal","discount");
					//原幣
					let taxrate = parentDataset.data.taxrate;
					countOriginal(osubtotal,parentDataset,"osubtotal","otax","ototal",taxrate,bodyId1,"body_subtotal");
					//本位幣
					parentDataset.data.ssubtotal = parseFloat(parentDataset.data.osubtotal * rate).toFixed(2);
					parentDataset.data.stax = parseFloat(parentDataset.data.otax * rate).toFixed(2);
					parentDataset.data.stotal =parseFloat(parentDataset.data.ototal * rate).toFixed(2);
				}
                if(parentDataset.data.depot_code != null){
                    row.data.body_depot_code = parentDataset.data.depot_code;
				    row.data.body_depot_name = parentDataset.data.depot_name;
                }
                // row.status = that.dataset.status;
			 }
		}
    }else if( pageData.page.page_id == littlePageId2 ){
        if(filedCode == "choose"){
            // console.log("321");
            let currentDataset2 = parentDataset.subData[littlePageIdBodyFormID2]
            let schema2 = that.config.schema[littlePageIdBodyFormID2]
            let firstRowEmptyFlag2 = false
            currentDataset2.forEach((row, rowIndex) => {
                if (rowIndex == 0 && that.checkRowIsEmpty(row, schema2)) return firstRowEmptyFlag2 = true // Check first row is empty and dont remove it
                if (that.checkRowIsEmpty(row, schema2)) that.deleteRow(parentDataset,littlePageIdBodyFormID2,rowIndex)
            });
        }
        }
	})
    window.injects.injectOnBodyClick.push((that, pageData, parentDataset, row, field, formID, subDataArray) => {
        const filedCode = field.field_code;
        if( pageData.page.page_id == mainPageId ){
        if( row.data.body_quantity != 0 && filedCode != 'body_cancel' && viewstatus == false ){
             alert("已有對應進貨紀錄，不可修改!");
        }
    }
    })
</script>

@endsection
