
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
const bodyId1 = 41;
const mainPageId = 53;
let editStatus = true ;
let isviewla = false ;
let machiningCodeEmpty = true ; //有空的
const littlePageId2 = 5248;
const littlePageIdBodyFormID2 = 5225;

    //防止表單欄位按下enter造成瀏覽器「隱式送出」，進而誤觸表單內第一個按鈕(例如取回小視窗的圖示按鈕)跳出視窗
    //改在keydown階段就攔截並阻止預設行為，只有在該欄位所屬表單內確實存在"搜尋"按鈕(多產品取回小視窗)時才代為觸發
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
    that.dataset.data.advanceday = getTodayDate();
    that.dataset.data.undertakerday = getTodayDate();
	that.dataset.override.fields['toworkbtn']  = { field_readonly : true };

})
window.injects.injectOnView.push((that, pageData, id) => {
    isviewla = true
})
window.injects.injectOnEdit.push((that, pageData, id) => {
	that.dataset.status = 'update'
    editStatus = true ;
    isviewla = false
	let subData = that.dataset.subData[bodyId1];

    //表身no有被其他單引用客戶要鎖
    subData.forEach((row, rowIndex) => {
		if( row.data.id ){
		    let orderData = {
				temp: 'order_no',
				no: row.data.id,
				tables:['BA202_53','BA203_62','DA201_45']
			};
			sendAPIRequest(getURL(`api/inject/cited`),"post",orderData).then(result => {
				if(result == 1){
					that.$set(that.dataset.override.fields, 'client_code' ,{ field_readonly : true });
				}
			})
	    }

    });


	if( that.dataset.data.client_order_code ){
		let lockToWork = false;
		let count = 0;
		for (let element of subData) {
			if( element.schema ){
				if( element.data.body_quantity != 0 ){
					element.override.preventDelete = true;
					for(let key in element.schema.fields){
						if( key != 'body_cancel' ){
							that.$set(element.override.fields, key ,{ field_readonly : true });
						}
					}
				}
				if( element.data.machining_code && element.data.id) {
					lockToWork = true; //有一個沒轉供單
                    element.override.preventDelete = true;
					for(let key in element.schema.fields){
						if( key != 'body_cancel' ){
                        	that.$set(element.override.fields, key ,{ field_readonly : true });
						}
					}
				}else{
					count++;
				}
			}
            if(element.data.id != null){
                element.status = that.dataset.status
            }
		}
		if(lockToWork ){
//			editStatus = false
			machiningCodeEmpty = true;

//			that.dataset.override.fields['toworkbtn'] = { field_readonly : true };
			let readArr = ['advanceday','client_code','client_name','currency','remarks','undertaker','undertakerday','undertakername','tax','taxrate','rate','transport_code'];
			for(let val of readArr){
				 that.$set(that.dataset.override.fields, val ,{ field_readonly : true });
			}
		}else{
		}
	}

})
window.injects.injectOnCopy.push((that, pageData, id) => {
	that.dataset.override.fields['toworkbtn']  = { field_readonly : true };
    let subData = that.dataset.subData[bodyId1];
    for (let element of subData) {
        element.data.body_quantity = 0;
    }
})

window.injects.injectOnReferenceWrite.push((that, pageData, fromField, data, fields, dataset) => {
	editStatus = false;
	if(  pageData.page.page_id == mainPageId){
//         if(fromField.field_code == "client_code"){
// //            console.log(data.client_code)
//             let clientData = {
// 				client_code: data.client_code,
// 			};
// 			sendAPIRequest(getURL(`api/inject/getclientcurrencytax`),"post",clientData).then(result => {
// 				console.log(that.dataset.data.currency)
//                 if(result != 0){
//                     that.dataset.data.currency = result[0]['currency']
//                     that.dataset.data.rate = result[0]['rate']
//                     that.dataset.data.tax = result[0]['tax']
//                     that.dataset.data.taxrate = result[0]['taxrate']

//                 }console.log(that.dataset.data.currency)
// 			});
//         }else
         if( fromField.field_code== "product_code" ){//換算率
			// dataset.data.unit_code = data.unit_code;
			dataset.data.body_rate = "1";
		    let subDataArray = that.dataset.subData[bodyId1];
			let parentDataset = that.dataset;
			let taxrate = parentDataset.data.taxrate;
			let rate = parentDataset.data.rate;
			let dataPrice = data.sell_price;
			dataset.data.o_body_price = dataPrice;
			osubtotal = countSubtotal2(dataset,subDataArray,parentDataset,"body_subtotal","body_num","o_body_price","osubtotal","discount",dataPrice)
			countOriginal(osubtotal,parentDataset,"osubtotal","otax","ototal",taxrate,bodyId1,"body_subtotal");
			dataset.data.ssubtotal = parseFloat(dataset.data.osubtotal * rate).toFixed(2);
            dataset.data.stax = parseFloat(dataset.data.otax * rate).toFixed(2);
            dataset.data.stotal =parseFloat(dataset.data.ototal * rate).toFixed(0);
		}else if( fromField.field_code == "combi_name" ){
            console.log(data);
			let dataPrice = data.combi_price;
			dataset.data.o_body_price = dataPrice;
		}else if( fromField.field_code == "combi_code" ){
            console.log(data);
			let dataPrice = data.combi_price;
			dataset.data.o_body_price = dataPrice;
		}else if( fromField.field_code == "currency" ){
			let parentDataset = that.dataset;
			let rate = data.currency_exchrate; //匯率
			let osubtotal = parentDataset.data.osubtotal;
			countStandard(osubtotal,parentDataset,rate,"ssubtotal","stax","stotal","otax","ototal");
		}else if( fromField.field_code == "tax" ){
            let taxrate = data.tax_taxrate;
            let rate = dataset.data.rate;
            let osubtotal = dataset.data.osubtotal;
            countOriginal(osubtotal,dataset,"osubtotal","otax","ototal",taxrate,bodyId1,"body_subtotal",data.tax_name);
			dataset.data.ssubtotal = parseFloat(dataset.data.osubtotal * rate).toFixed(2);
            dataset.data.stax = parseFloat(dataset.data.otax * rate).toFixed(2);
            dataset.data.stotal =parseFloat(dataset.data.ototal * rate).toFixed(0);
            if(formVue.dataset.data.yn_n_sales==1){
                formVue.dataset.data.final_pmt=0;
                formVue.dataset.data.amt_recd=formVue.dataset.data.ototal;
            }else{
                formVue.dataset.data.final_pmt = parseFloat(Number(formVue.dataset.data.ototal-formVue.dataset.data.amt_recd)).toFixed(0);
            }
		}else if( fromField.field_code == "unit_code"){
			let subDataArray = that.dataset.subData[bodyId1];
			let parentDataset = that.dataset;
			let taxrate = parentDataset.data.taxrate;
			let rate = parentDataset.data.rate;
			let dataPrice = data.body_sell_price;
			osubtotal = countSubtotal2(dataset,subDataArray,parentDataset,"body_subtotal","body_num","o_body_price","osubtotal","discount",dataPrice)
			countOriginal(osubtotal,parentDataset,"osubtotal","otax","ototal",taxrate,bodyId1,"body_subtotal");
			dataset.data.ssubtotal = parseFloat(dataset.data.osubtotal * rate).toFixed(2);
            dataset.data.stax = parseFloat(dataset.data.otax * rate).toFixed(2);
            dataset.data.stotal =parseFloat(dataset.data.ototal * rate).toFixed(0);
            formVue.dataset.data.final_pmt = parseFloat(Number(dataset.data.ototal-dataset.data.amt_recd)).toFixed(0);
		}
        	that.$nextTick().then(() => {
				let subData = that.dataset.subData[bodyId1];

			formVue.dataset.subData[bodyId1].forEach(function(item) {
				if(item.data.product_code != null){
				formVue.$set(item.override.fields, 'payment_status', { field_readonly: true });
				if (formVue.dataset.data.yn_cnt_cust == 1) {
			formVue.$set(item.data, 'payment_status', '已收款');
			}
		}
			});
				
			});
		
	
	
    }

})

window.injects.injectOnRowAdd.push((that, pageData, targetSubDataArray, dataset) => {})
window.injects.injectOnRowDelete.push((that, pageData, parentDataset, formID, rowIndex) => {
    if(pageData.page.page_id == mainPageId){
        if(formID==bodyId1){
		let rate = parentDataset.data.rate;
		let taxrate = parentDataset.data.taxrate;
		if(that.dataset.subData[41][rowIndex].data.body_subtotal != null && that.dataset.subData[41][rowIndex].data.body_subtotal !=  ".00"){
		osubtotal = parseFloat(that.dataset.data.osubtotal- parseFloat(that.dataset.subData[41][rowIndex].data.body_subtotal)).toFixed(2);
		osubtotal2 = parseFloat(that.dataset.data.ototal- parseFloat(that.dataset.subData[41][rowIndex].data.body_subtotal)).toFixed(2);
		if(formVue.dataset.data.tax =="稅內含"){
		formVue.dataset.data.osubtotal = parseFloat(osubtotal2/(1 + taxrate )).toFixed(2);
		formVue.dataset.data.otax = parseFloat(osubtotal2 - formVue.dataset.data.osubtotal).toFixed(2);
		formVue.dataset.data.ototal = parseFloat(osubtotal2).toFixed(2);
        formVue.dataset.data.final_pmt = parseFloat(Number(formVue.dataset.data.ototal-formVue.dataset.data.amt_recd)).toFixed(2);
		}else{
		formVue.dataset.data.osubtotal = osubtotal;
		formVue.dataset.data.otax = parseFloat(osubtotal*taxrate).toFixed(2);
		formVue.dataset.data.ototal = parseFloat(parseFloat(formVue.dataset.data.osubtotal) + parseFloat(formVue.dataset.data.otax)).toFixed(2);
        formVue.dataset.data.final_pmt = parseFloat(Number(formVue.dataset.data.ototal-formVue.dataset.data.amt_recd)).toFixed(2);
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
	editStatus = false;
	if( filedCode == "taxrate" ){
		let taxrate = dataset.data.taxrate;
		let rate = dataset.data.rate;
		let osubtotal = dataset.data.osubtotal;

	   countOriginal(osubtotal,dataset,"osubtotal","otax","ototal",taxrate,bodyId1,"body_subtotal");
	   countStandard(osubtotal,dataset,rate,"ssubtotal","stax","stotal","otax","ototal");
       formVue.dataset.data.final_pmt = parseFloat(Number(dataset.data.ototal-dataset.data.amt_recd)).toFixed(0);
	}else if( filedCode == "rate" ){
		let rate = dataset.data.rate;
		let osubtotal = dataset.data.osubtotal;
		countStandard(osubtotal,dataset,rate,"ssubtotal","stax","stotal","otax","ototal");

	}
})
window.injects.injectOnHeadClick.push((that, pageData, field, dataset) => {
	const filedCode = field.field_code;
	if( pageData.page.page_id == mainPageId &&　filedCode == "toworkbtn" ){
		let readArr = ['advanceday','client_code','client_name','client_order_code','currency','osubtotal','otax','ototal','rate','remarks','ssubtotal','stax','stotal','tax','taxrate','undertaker','undertakerday','undertakername','transport_code'];

		if( editStatus && machiningCodeEmpty ){
		   let orderData = {
				client_order_code : that.dataset.data.client_order_code,
				action : formVue.dataset.status
			};
		   sendAPIRequest(getURL(`api/inject/transToWork`),"post",orderData).then(result => {
//			   console.log(result);
			   alert(result['text']);
			   if( result['status'] == true ){
//				   editStatus = true;
				}else{
//					editStatus = false;
					let subData = dataset.subData[bodyId1];
					for( let subRow of subData ){
						for( let [key, value] of Object.entries(result['number']) ){
							if( value['order_no'] == subRow.data.id ){
								subRow.data.machining_code = value['number'];
							}
						}
						if( subRow.data.machining_code ){
							subRow.override.fields['product_code'] = {field_readonly: true};
							subRow.override.fields['unit_code'] = {field_readonly: true};
							subRow.override.fields['body_num'] = {field_readonly: true};
							subRow.override.fields['body_price'] = {field_readonly: true};
							subRow.override.fields['body_rate'] = {field_readonly: true};
							subRow.override.fields['body_formula'] = {field_readonly: true};
							subRow.override.fields['body_remarks'] = {field_readonly: true};
							subRow.override.fields['packing_code'] = {field_readonly: true};
							subRow.override.preventDelete = true;
						}
						for(let val of readArr){
							 that.$set(that.dataset.override.fields, val ,{ field_readonly : true });
						}
					}
				}
			});
		}else{
			if( !editStatus ){
			   alert("此客戶訂單已被修改，請先儲存再轉加工單");
			}else if( !machiningCodeEmpty ){
				alert("此客戶訂單表身皆已轉加工單");
			}

		}

	}else if( pageData.page.page_id == mainPageId &&　filedCode == "print_btn" ){
        type = "type1";
        let orderData = {
			order_code:that.dataset.data.client_order_code,
			type:type
		};
		fullscreenDimmer.loading()
		sendAPIRequest(getURL(`api/inject/printOrder1`),"post",orderData).then( async result => {
			if(result.status){
				window.open(result.file);
				fullscreenDimmer.unloading()
			}else{
				alert("{{ $commonTranslations['error.unknown'] . $commonTranslations['contact_maintenance'] }}")
				console.error(result);
				fullscreenDimmer.unloading()
			}
		})
    }else if( pageData.page.page_id == mainPageId &&　filedCode == "print_btn2" ){
        type = "type1";
        let orderData = {
			order_code:that.dataset.data.client_order_code,
			type:type
		};
		fullscreenDimmer.loading()
		sendAPIRequest(getURL(`api/inject/printOrder2`),"post",orderData).then( async result => {
			if(result.status){
				window.open(result.file);
				fullscreenDimmer.unloading()
			}else{
				alert("{{ $commonTranslations['error.unknown'] . $commonTranslations['contact_maintenance'] }}")
				console.error(result);
				fullscreenDimmer.unloading()
			}
		})
    }else if( pageData.page.page_id == mainPageId &&　filedCode == "print_btn3" ){
        type = "type1";
        let orderData = {
			order_code:that.dataset.data.client_order_code,
			type:type
		};
		fullscreenDimmer.loading()
		sendAPIRequest(getURL(`api/inject/printOrder3`),"post",orderData).then( async result => {
			if(result.status){
				window.open(result.file);
				fullscreenDimmer.unloading()
			}else{
				alert("{{ $commonTranslations['error.unknown'] . $commonTranslations['contact_maintenance'] }}")
				console.error(result);
				fullscreenDimmer.unloading()
			}
		})
    }else if(pageData.page.page_id == mainPageId && filedCode == 'batch_recv'){
                for( let index in dataset.subData[bodyId1]){
					if(dataset.subData[bodyId1][index].data.product_code==""||dataset.subData[bodyId1][index].data.product_code==null){

					}else{
						dataset.subData[bodyId1][index].data.payment_status="已收款";
					}
				}
                that.dataset.data.final_pmt = 0;
                that.dataset.data.amt_recd = that.dataset.data.ototal;
            }
    else if( pageData.page.page_id == littlePageId2 ){
			if( filedCode == 'searchbtn' ){

				that.setFormOption(littlePageIdBodyFormID2,'disableAutoAddOrRemoveRow',true);
				let serachData2 = {
					serach:dataset.data.search_content
				};
                // console.log(serachData1);
				let url = `api/inject/getProduct`;
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

							if( key == "body_price"){
								formVue.dataset.subData[bodyId1][SubDataLas]['data']["o_body_price"] = dataset.subData[littlePageIdBodyFormID2][index]['data'][key];
								formVue.dataset.subData[bodyId1][SubDataLas]['data'][key] = dataset.subData[littlePageIdBodyFormID2][index]['data'][key];
							}
							else if( ( key != "choose" ) ){
								formVue.dataset.subData[bodyId1][SubDataLas]['data'][key] = dataset.subData[littlePageIdBodyFormID2][index]['data'][key];

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
				that.$nextTick().then(() => {
				let subData = that.dataset.subData[bodyId1];

			formVue.dataset.subData[bodyId1].forEach(function(item) {
				if(item.data.product_code != null){
				formVue.$set(item.override.fields, 'payment_status', { field_readonly: true });
				if (formVue.dataset.data.yn_cnt_cust == 1) {
			formVue.$set(item.data, 'payment_status', '已收款');
			}
		}
			});
				
			});
				 }else{
					 alert("無需取回項目");
				 }

			}
		}
})

window.injects.injectOnBodyInput.push((that, pageData, parentDataset, row, field, formID, subDataArray) => {})
window.injects.injectOnBodyChange.push((that, pageData, parentDataset, row, field, formID, subDataArray) => {
	editStatus = false;
	const filedCode = field.field_code;

	let osubtotal = 0; //原幣小計
	let otax = 0; //原幣稅金
	let ototal = 0; //原幣合計

	let ssubtotal =0; //本位幣小計
	let stax = 0; //本位幣稅金
	let stotal = 0; //本位幣合計
	let rate = parentDataset.data.rate; //匯率

				let subData = that.dataset.subData[bodyId1];

formVue.dataset.subData[bodyId1].forEach(function(item) {
    formVue.$set(item.override.fields, 'payment_status', { field_readonly: true });
});
	//	console.log(filedCode);
	
	if( row.selected && pageData.page.page_id == mainPageId){
		
		if( row.data.body_quantity == 0 ){
			if( filedCode == "body_quantity"){

			}else if(filedCode == "body_num" || filedCode == "body_price" || filedCode == "o_body_price" || filedCode == "discount" ){

				osubtotal = countSubtotal2(row,subDataArray,parentDataset,"body_subtotal","body_num","o_body_price","osubtotal","discount");
				//原幣
				let taxrate = parentDataset.data.taxrate;
				countOriginal(osubtotal,parentDataset,"osubtotal","otax","ototal",taxrate,bodyId1,"body_subtotal");
				//本位幣
				parentDataset.data.ssubtotal = parseFloat(parentDataset.data.osubtotal * rate).toFixed(2);
				parentDataset.data.stax = parseFloat(parentDataset.data.otax * rate).toFixed(2);
				parentDataset.data.stotal =parseFloat(parentDataset.data.ototal * rate).toFixed(0);
                if(formVue.dataset.data.yn_n_sales == 1){
                    row.data.payment_status="已收款";
                    formVue.dataset.data.final_pmt=0;
                    formVue.dataset.data.amt_recd=formVue.dataset.data.ototal;
                }else{
                    formVue.dataset.data.final_pmt = parseFloat(Number(formVue.dataset.data.ototal-formVue.dataset.data.amt_recd)).toFixed(0);
                }
				w
			}
			if( filedCode == "payment_status"){


						//console.log("hi");
			let index = formVue.dataset.subData[bodyId1].findIndex(x => x.selected);
			let tax = formVue.dataset.data.tax;
			let taxrate = formVue.dataset.data.taxrate;
			// 判斷稅制，計算 multiplier
			let multiplier = (tax == "稅外加") ? (1 + taxrate) : 1;
			// 將 body_subtotal 乘上 multiplier
			let amount = formVue.dataset.subData[bodyId1][index].data.body_subtotal * multiplier;
			//console.log(formVue.dataset.subData[bodyId1][index].data.);
			that.$nextTick().then(() => {
				//console.log()
				

			formVue.dataset.subData[bodyId1].forEach(function(item) {
				formVue.$set(item.override.fields, 'payment_status', { field_readonly: true });
				if (formVue.dataset.data.yn_cnt_cust == 1) {
			formVue.$set(item.data, 'payment_status', '已收款');
			}
			});
				
		
				if(formVue.dataset.subData[bodyId1][index].data.payment_status == "已收款"){
					formVue.dataset.data.amt_recd += amount;
				    formVue.dataset.data.final_pmt -= amount;
			} else {
				formVue.dataset.data.amt_recd -= amount;
				formVue.dataset.data.final_pmt += amount;
			
				}
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

				}
})
window.injects.injectOnBodyClick.push((that, pageData, parentDataset, row, field, formID, subDataArray) => {
	const filedCode = field.field_code;
if(pageData.page.page_id == mainPageId){
    if( (row.data.body_quantity != 0 || row.data.machining_code != null) && filedCode != 'body_cancel' && !isviewla ){
		 alert("已有對應出貨紀錄，不可修改!");
	}
}
})

</script>

@endsection
