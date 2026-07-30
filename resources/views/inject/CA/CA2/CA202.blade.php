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
	const bodyId1 = 55;
	const mainPageId = 60;
	const littlePageId = 72;
	const littlePageIdBodyFormID = 76;
    const littlePageId1 = 4250;
	const littlePageIdBodyFormID1 = 4231;
    const littlePageId2 = 5249;
    const littlePageIdBodyFormID2 = 5227;
	const batchBodyFormID = 2072;
	const batchPageId = 2072;
    let isview = false;

    window.injects.injectOnInit.push((that, pageData) => {

	})

    window.injects.injectOnAdd.push((that, pageData) => {
		that.dataset.data.undertaker = '{{session("username")}}';
		that.dataset.data.undertakername = '{{session("user_name")}}';
        that.dataset.data.receive_day = getTodayDate();
        that.dataset.data.undertakerday = getTodayDate();
        isview = false
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
        isview = true
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
        isview = false
        if('{{session("username")}}' == "phaith" || '{{session("username")}}' == "yltest" || '{{session("username")}}' == "ocean"){
            that.dataset.schema.fields.osubtotal.field_show_on_form = false ;
            that.dataset.schema.fields.otax.field_show_on_form = false ;
            that.dataset.schema.fields.ototal.field_show_on_form = false ;
            that.dataset.schema.fields.ssubtotal.field_show_on_form = false ;
            that.dataset.schema.fields.stax.field_show_on_form = false ;
            that.dataset.schema.fields.stotal.field_show_on_form = false ;
            that.dataset.subData[bodyId1][0].schema.fields.body_subtotal.field_show_on_form = false ;
        }
        that.dataset.status = 'update'
        let subData = that.dataset.subData[bodyId1];
        for (let element of subData) {
            if(element.data.id != null){
                element.status = that.dataset.status
            }
            if (element.schema) {
				if( element.data.batch == 'Y' ){
				    for (let key in element.schema.fields) {
						element.override.fields[key] = {
							field_readonly: true
						};
						element.override.preventDelete = true;
					}
				}
                if( element.data.purchase_code != null ){
                    element.override.fields['product_code'] = {
                        field_readonly: true
                    };
                    formVue.$set(formVue.dataset.override.fields, 'vendor_code' ,{ field_readonly : true });
                }
            }
        }

        let receiveData = {
            headid:that.dataset.data.receive_code,
        };
        sendAPIRequest(getURL(`api/inject/havecited`), "post", receiveData).then(result => {
            if (result['status'] != 0) {
                let readArr = ['receive_code', 'receive_day', 'undertaker', 'undertakername', 'undertakerday', 'vendor_code', 'vendor_name', 'currency', 'rate', 'tax', 'taxrate', 'osubtotal', 'otax', 'ototal', 'ssubtotal', 'stax', 'stotal', 'remarks', 'depot_code', 'depot_name'];
                for (let val of readArr) {
                    that.$set(that.dataset.override.fields, val, {
                        field_readonly: true
                    });
                }
                for (const [key, value] of Object.entries(result['res'])) {
                    for (let element of subData) {
                        if (element.schema && value.receive_no == element.data.id) {
                            for (let key in element.schema.fields) {
                                if(key != "pay_status"){
                                that.$set(element.override.fields, key, {
                                    field_readonly: true
                                });
                                }
                            }
                            element.override.preventDelete = true;
                        }

                    }
                }

            }
        });
        let cited = false
        let readstatus = false
        let count = 0
        subData.forEach((row, rowIndex) => {
            //表身no有被其他單引用廠商要鎖
            let shipData = {
                temp: 'purchase_no',
                no: row.data.id,
                tables:['CA203_64']
            };
            sendAPIRequest(getURL(`api/inject/cited`),"post",shipData).then(result => {
                if(result == 1){
                    cited = true
                }
            })
        });
        if(cited == true){
            formVue.$set(formVue.dataset.override.fields, 'vendor_code' ,{ field_readonly : true });
        }

    })
    window.injects.injectOnCopy.push((that, pageData, id) => {
        isview = false
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
            if (element.schema) {
				if( element.data.batch == 'Y' ){
				    for (let key in element.schema.fields) {
						element.override.fields[key] = {
							field_readonly: true
						};
						element.override.preventDelete = true;
					}
				}
                if( element.data.purchase_code != null ){
                    element.override.fields['product_code'] = {
                        field_readonly: true
                    };
                    formVue.$set(formVue.dataset.override.fields, 'vendor_code' ,{ field_readonly : true });
                }
            }
        }

        let cited = false
        let readstatus = false
        let count = 0
        subData.forEach((row, rowIndex) => {
            //表身no有被其他單引用廠商要鎖
            let shipData = {
                temp: 'purchase_no',
                no: row.data.id,
                tables:['CA203_64']
            };
            sendAPIRequest(getURL(`api/inject/cited`),"post",shipData).then(result => {
                if(result == 1){
                    cited = true
                }
            })
        });
        if(cited == true){
            formVue.$set(formVue.dataset.override.fields, 'vendor_code' ,{ field_readonly : true });
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
            dataset.data.b_pmt_date = that.dataset.data.h_pmt_date;
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
		}else if(fromField.field_code == "vendor_code" ){
                formVue.dataset.data.source_purchase_code.data.vendor_code=data.vendor_code;
        }
	})

    window.injects.injectOnRowAdd.push((that, pageData, targetSubDataArray, dataset) => {})
    window.injects.injectOnRowDelete.push((that, pageData, parentDataset, formID, rowIndex) => {
        let rate = parentDataset.data.rate;
		let taxrate = parentDataset.data.taxrate;
        if( pageData.page.page_id == mainPageId ){
            if(formID==bodyId1){
            let subData = that.dataset.subData[bodyId1];
        let num = 0;

            for (let key in subData) {
                if (subData[key].schema && key != rowIndex ) {
//                    console.log(element)
                    if( subData[key].data.purchase_code != null ){
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
		if(that.dataset.subData[55][rowIndex].data.body_subtotal != null && that.dataset.subData[55][rowIndex].data.body_subtotal !=  ".00"){
		osubtotal = parseFloat(that.dataset.data.osubtotal- parseFloat(that.dataset.subData[55][rowIndex].data.body_subtotal)).toFixed(2);
		osubtotal2 = parseFloat(that.dataset.data.ototal- parseFloat(that.dataset.subData[55][rowIndex].data.body_subtotal)).toFixed(2);
		if(formVue.dataset.data.tax =="稅內含"){
		formVue.dataset.data.osubtotal = parseFloat(osubtotal2/(1 + taxrate )).toFixed(2);
		formVue.dataset.data.otax = parseFloat(osubtotal2 - formVue.dataset.data.osubtotal).toFixed(2);
		formVue.dataset.data.ototal = osubtotal2;
		}else{
		formVue.dataset.data.osubtotal = parseFloat(osubtotal).toFixed(2);
		formVue.dataset.data.otax = parseFloat(osubtotal*taxrate).toFixed(2);
		formVue.dataset.data.ototal = parseFloat(parseFloat(formVue.dataset.data.osubtotal) + parseFloat(formVue.dataset.data.otax)).toFixed(2);
		}
		formVue.dataset.data.ssubtotal = parseFloat(formVue.dataset.data.osubtotal * rate).toFixed(2);
		formVue.dataset.data.stax =  parseFloat(formVue.dataset.data.otax * rate).toFixed(2);
		formVue.dataset.data.stotal = parseFloat(formVue.dataset.data.ototal * rate).toFixed(2);



        }
    }
        }else if( pageData.page.page_id == batchPageId ){
			let batchDataSet = that.dataset.subData[batchBodyFormID];
			let count = 0;
			for( let batchKey in batchDataSet ){
				if( batchKey != rowIndex &&  batchDataSet[batchKey].data.batch_code ){
					count++;
					break;
				}
			}
			if( count == 0 ){
				let index = formVue.dataset.subData[bodyId1].findIndex(x => x.selected);
				formVue.dataset.subData[bodyId1][index].data.batch = null;
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

		   countOriginal(osubtotal,dataset,"osubtotal","otax","ototal",taxrate,bodyId1,"body_subtotal");
		   countStandard(osubtotal,dataset,rate,"ssubtotal","stax","stotal","otax","ototal");
		}else if( filedCode == "rate" ){
			let rate = dataset.data.rate;
			let osubtotal = dataset.data.osubtotal;
			countStandard(osubtotal,dataset,rate,"ssubtotal","stax","stotal","otax","ototal");

		}else if( filedCode == "h_pmt_date" ){
            for( let index in dataset.subData[bodyId1]){
                if(dataset.subData[bodyId1][index].data.product_code){
                    dataset.subData[bodyId1][index].data.b_pmt_date=dataset.data.h_pmt_date;
                }
            }
        }
	})
    window.injects.injectOnHeadClick.push((that, pageData, field, dataset) => {
        const filedCode = field.field_code;
        let subData = that.dataset.subData[bodyId1];
		if( pageData.page.page_id == littlePageId ){
			if( filedCode == 'searchbtn' ){
                that.setFormOption(littlePageIdBodyFormID,'disableAutoAddOrRemoveRow',true);
				let serachData = {
					advancedayS:dataset.data.advancedayS,
					advancedayE:dataset.data.advancedayE,
					purchase_codeS:dataset.data.purchase_codeS,
					purchase_codeE:dataset.data.purchase_codeE,
					product_codeS:dataset.data.product_codeS,
					product_codeE:dataset.data.product_codeE,
					vendor_code:dataset.data.vendor_code
				};
				let url =`api/inject/getCompanyOrder`;
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
                           }else if(key == 'body_num'){
                               formVue.dataset.subData[bodyId1][SubDataLas]['data']['body_num'] =  dataset.subData[littlePageIdBodyFormID][index]['data']['body_num'] - dataset.subData[littlePageIdBodyFormID][index]['data']['body_quantity'];
                           }else{
                               formVue.dataset.subData[bodyId1][SubDataLas]['data'][key] =  dataset.subData[littlePageIdBodyFormID][index]['data'][key];
                                if(key == "purchase_code"){
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
						countOriginal(osubtotal,parentDataset,"osubtotal","otax","ototal",taxrate,bodyId1,"body_subtotal");
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
		}else if( pageData.page.page_id == littlePageId1 ){
			if( filedCode == 'searchbtn' ){

				that.setFormOption(littlePageIdBodyFormID1,'disableAutoAddOrRemoveRow',true);
				let serachData1 = {
					vendor_code:formVue.dataset.data.vendor_code
				};
                // console.log(serachData1);
				let url = `api/inject/getVendorOrder1`;
				searchbtn(that,serachData1,url,littlePageIdBodyFormID1,true,readonlyBody=[],readonlyHead=[]);

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
				for( let index in dataset.subData[littlePageIdBodyFormID1]){
					if( dataset.subData[littlePageIdBodyFormID1][index].data.choose ==  1 ){
						SubDataLas = formVue.dataset.subData[bodyId1].length - 1 ;
						currentRow = that.parentVue().dataset.subData[bodyId1][SubDataLas]
						//console.log(currentRow);
						if( !formVue.checkRowIsEmpty(currentRow, schema) ){
							that.parentVue().addEmptyRow(that.parentVue().dataset,bodyId1);
							SubDataLas = formVue.dataset.subData[bodyId1].length - 1 ; //暫時寫在最後一行
						}
						for( let key in dataset.subData[littlePageIdBodyFormID1][index].data) {
							//console.log(key);

							if( ( key != "choose" ) ){
								formVue.dataset.subData[bodyId1][SubDataLas]['data'][key] = dataset.subData[littlePageIdBodyFormID1][index]['data'][key];
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
                    formVue.$set(formVue.dataset.override.fields, 'vendor_code' ,{ field_readonly : true });
                    that.setFormOption(littlePageIdBodyFormID1,'disableAutoAddOrRemoveRow',true);
				let serachData1 = {
					vendor_code:formVue.dataset.data.vendor_code
				};
                // console.log(serachData1);
				let url = `api/inject/getVendorOrder1`;
				searchbtn(that,serachData1,url,littlePageIdBodyFormID1,true,readonlyBody=[],readonlyHead=[]);
				 }else{
					 alert("無需取回項目");
				 }

			}
		}if( pageData.page.page_id == littlePageId2 ){
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

    window.injects.injectOnBodyInput.push((that, pageData, parentDataset, row, field, formID, subDataArray) => {
    })
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
        let subData = that.dataset.subData[bodyId1];

        if( filedCode == "body_quantity"){

        }else if( filedCode == "batch_code" ){

            let index = formVue.dataset.subData[bodyId1].findIndex(x => x.selected);
            formVue.dataset.subData[bodyId1][index].data.batch = "Y";
        }



        osubtotal = counSubtotal(row,subDataArray,parentDataset,"body_subtotal","body_num","body_price","osubtotal","discount");
        //原幣
        let taxrate = parentDataset.data.taxrate;
        countOriginal(osubtotal,parentDataset,"osubtotal","otax","ototal",taxrate,bodyId1,"body_subtotal");
        //本位幣
        parentDataset.data.ssubtotal = parseFloat(parentDataset.data.osubtotal * rate).toFixed(2);
        parentDataset.data.stax = parseFloat(parentDataset.data.otax * rate).toFixed(2);
        parentDataset.data.stotal =parseFloat(parentDataset.data.ototal * rate).toFixed(2);
        parentDataset.data.amt_paid = 0;
        parentDataset.data.amt_unpaid = parentDataset.data.stotal;
        }else if( pageData.page.page_id == littlePageId ){
        if(filedCode == "choose"){
            let currentDataset = parentDataset.subData[littlePageIdBodyFormID]
            let schema = that.config.schema[littlePageIdBodyFormID]
            let firstRowEmptyFlag = false
            currentDataset.forEach((row, rowIndex) => {
                if (rowIndex == 0 && that.checkRowIsEmpty(row, schema)) return firstRowEmptyFlag = true // Check first row is empty and dont remove it
                if (that.checkRowIsEmpty(row, schema)) that.deleteRow(parentDataset,littlePageIdBodyFormID,rowIndex)
            });
        }
        }else if( pageData.page.page_id == littlePageId1 ){
        if(filedCode == "choose"){
            let currentDataset = parentDataset.subData[littlePageIdBodyFormID1]
            let schema = that.config.schema[littlePageIdBodyFormID1]
            let firstRowEmptyFlag = false
            currentDataset.forEach((row, rowIndex) => {
                if (rowIndex == 0 && that.checkRowIsEmpty(row, schema)) return firstRowEmptyFlag = true // Check first row is empty and dont remove it
                if (that.checkRowIsEmpty(row, schema)) that.deleteRow(parentDataset,littlePageIdBodyFormID1,rowIndex)
            });
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
//        if (pageData.page.page_id == mainPageId) {
//            let subData = that.dataset.subData[bodyId1];
//            console.log(isview)
//            if (filedCode == 'source_purchase_code' && isview) {
//                for (let element of subData) {
//                    for (let index of element.data.source_purchase_code.subData[littlePageIdBodyFormID]) {
//                        console.log(index)
////                        clearEmptyRowBylittlePage(subData,littlePageId,littlePageIdBodyFormID,'source_purchase_code',"purchase_no")
//                    }
//                }
//            }
//        }
        if (filedCode == 'source_purchase_code') {
            row.data.source_purchase_code.data.vendor_code = parentDataset.data.vendor_code;
        }

        if( filedCode == 'source_batch_code' ){
			row.data.source_batch_code.data.receive_code = parentDataset.data.receive_code;
			row.data.source_batch_code.data.product_code = row.data.product_code;
			row.data.source_batch_code.data.receive_no = row.data.id;
		}

        if(row.override.preventDelete == true &&  filedCode != 'source_batch_code' ){
			if( row.form_id == bodyId1){
			   if( row.data.batch == "Y" ){
				   alert("該列已有批號管理，不可修改及刪除");
			   }else{
                if(filedCode != 'pay_status'){
                    alert("該筆資料已被進貨退回單引用，故無法進行修改及刪除")
                   }
			   }
			}

        }
    })



</script>

@endsection
