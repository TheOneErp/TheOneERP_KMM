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
    let con = 1;
	const bodyId1 = 53;
	const mainPageId = 59;
	const littlePageId = 67;
	const littlePageIdBodyFormID = 66;
    const littlePageId1 = 4249;
    const littlePageIdBodyFormID1 = 4229;
    const littlePageId2 = 5248;
    const littlePageIdBodyFormID2 = 5225;
	const kegPageId = 70;
	const kegSubFormId = 72;
	let kegStatus = 0;
	let bucketArr = [];
	let bucketRecord = [];
	let bodyNumRecord = [];
	let pEdit = false;
	let shipBackExist = false;
	let kegClickStatus = false;
	let original_data = {};
	let original_subData = {};

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

	// let editStatus = true ;
//	let gettoody = false;
function checkBucketExist(data,buketNum=null){
		let exist = 0 ;
		let sameBucet = 0;
		for( let element of data ){
			if( element.data.keg ){
				exist = 1 ;
				if( buketNum ){
					if( element.data.keg == buketNum  ){
						exist = element.data ;
						break;
					}else{
						exist = 2 ;
					}

				}
			}
		}
		return exist ;
	}
	function setBucketMark( data,val ){
		for( let element of data ){
			if( element.selected ){
				element.data.keg = val;
				if( val == "" ){
				   	that.$set(element.override.fields, 'body_depot_code' ,{ field_readonly : false });
				}
			}
		}
	}
	function emptyKeg(kegData){
		let index = formVue.dataset.subData[bodyId1].findIndex(x => x.selected);
		formVue.dataset.subData[bodyId1][index]['data']['keg'] = null;
		kegData.data.remarks = null;
		let kegSubData = kegData.subData[kegSubFormId];
		for(let valkey in kegSubData ){
            for( let key in kegSubData[valkey].schema.attributes ){
                kegSubData[valkey].data[key] = null;
            }

		}
	}
	//桶號小視窗
	function createKeg(row,kegData){
		let keg_code = kegData['data']['keg'];
		let num = kegData['data']['num'];
		let kegSubData = row['data']['source_keg']['subData'][kegSubFormId];
		let kegForm = checkBucketExist( kegSubData,keg_code );
		if( kegForm == 0 ){
//			kegSubData[0].data.body_num = ( parseFloat( row['data']['body_num'] / row['data']['body_rate']) + parseFloat(num)).toFixed(2);
			kegSubData[0].data.body_num = num;
			kegSubData[0].data.remaining_num = parseFloat(num);
			kegSubData[0].data.keg = keg_code;
			if( pEdit ){
			   kegSubData[0].status = 'update';
			}
		}else if( kegForm == 2 ){//沒有相同的
			let kegIndex = kegSubData.length - 1 ; //暫時寫在最後一行
			if( kegSubData[kegIndex]['data']['keg'] ){
				row.data.source_keg.vue().addEmptyRow(row.data.source_keg,kegSubFormId);
				kegIndex = kegSubData.length - 1 ;
			}
//			kegSubData[kegIndex].data.body_num = ( parseFloat( row['data']['body_num'] / row['data']['body_rate']) + parseFloat(num)).toFixed(2);
			kegSubData[kegIndex].data.body_num = num;
			kegSubData[kegIndex].data.remaining_num = parseFloat(num);
			kegSubData[kegIndex].data.keg = keg_code;
			if( pEdit ){
			   kegSubData[kegIndex].status = 'update';
			}
			row.data.source_keg.vue().addEmptyRow(row.data.source_keg,kegSubFormId);
		}else{
			alert("此桶號已在小視窗中存在，請確認");
		}

		return kegForm;
	}
	function storeNumRecord(){
		let subArr = formVue.dataset.subData[bodyId1];
		for(let key in subArr ){
			bodyNumRecord[key] = subArr[key].data.body_num * subArr[key].data.body_rate;
		}
	}

	window.injects.injectOnInit.push((that, pageData) => {})
    window.injects.injectOnAdd.push((that, pageData) => {
		that.dataset.override.fields['print_btn']  = { field_readonly : true };
		that.dataset.override.fields['print_btn2']  = { field_readonly : true };
        if('{{session("username")}}' == "phaith" || '{{session("username")}}' == "yltest" || '{{session("username")}}' == "ocean"){
            that.dataset.subData[bodyId1][0].schema.fields.cost.field_show_on_form = false ;
        }
		that.dataset.data.undertaker = '{{session("username")}}';
		that.dataset.data.undertakername = '{{session("user_name")}}';
        that.dataset.data.ship_date = getTodayDate();
        that.dataset.data.undertakerday = getTodayDate();
		let bodytotal= 0;
		let bodytaxrate= 0;
		let oSubtotal= 0;
		let totalprice = 0;
		let totaltax = 0;
		let orate = 0;
		let ototalprice =0;
		let ototaltax =0;
        //let subDataArray = that.dataset.subData[bodyId1];
        //let parentDataset = that.dataset;
        //let taxrate = that.dataset.data.taxrate;
        //let rate = that.dataset.data.body_rate ;
        if(localStorage.hasOwnProperty('shipdata') == true){
            let orderdata = JSON.parse(localStorage.getItem("shipdata"))
            //console.log(orderdata);
            SubDataLas = formVue.dataset.subData[bodyId1].length - 1 ; //暫時寫在最後一行
			for( let key in orderdata) {
                if(key == 'client_code'){
                    formVue.dataset.data.client_code = orderdata[key];
                }else if(key == 'client_name'){
                    formVue.dataset.data.client_name = orderdata[key];
                }else if(key == 'currency'){
                    formVue.dataset.data.currency = orderdata[key];
                }else if(key == 'rate'){
                    formVue.dataset.data.rate = orderdata[key];
                    rate = orderdata[key]
                }else if(key == 'tax'){
                    formVue.dataset.subData[bodyId1][SubDataLas]['data']['b_tax'] = orderdata[key];
                }else if(key == 'taxrate'){
                    formVue.dataset.subData[bodyId1][SubDataLas]['data']['b_taxrate'] = orderdata[key];
                    taxrate = orderdata[key];
                }else if(key == 'orderno'){
                    formVue.dataset.subData[bodyId1][SubDataLas]['data']['order_no'] = orderdata[key];
                }else if(key == 'body_num'){
                    formVue.dataset.subData[bodyId1][SubDataLas]['data']['body_num'] = orderdata['body_num'] - orderdata['body_quantity'];
                }else if(key == 'o_body_price'){
					formVue.dataset.subData[bodyId1][SubDataLas]['data']['o_body_price'] = orderdata[key];
				}
				else{
                    formVue.dataset.subData[bodyId1][SubDataLas]['data'][key] =  orderdata[key];
                }
            }
			for(i=0;i<=formVue.dataset.subData[bodyId1].length-1;i++) {
				formVue.dataset.subData[bodyId1][i].data.body_price=parseFloat(formVue.dataset.subData[bodyId1][i].data.o_body_price * formVue.dataset.subData[bodyId1][i].data.discount/100).toFixed(2);
				formVue.dataset.subData[bodyId1][i].data.body_subtotal=formVue.dataset.subData[bodyId1][i].data.body_price * formVue.dataset.subData[bodyId1][i].data.body_num;
					//console.log(formVue.dataset.subData[bodyId1][i]);
					if( formVue.dataset.subData[bodyId1][i].data.product_code){
						//console.log(formVue.dataset.subData[bodyId1][i].data.b_tax);
						if(formVue.dataset.subData[bodyId1][i].data.b_tax == "稅外加"){
							bodytotal = parseFloat(formVue.dataset.subData[bodyId1][i].data.body_subtotal,10).toFixed(2);
							bodytotal = parseFloat(bodytotal);
							bodytaxrate = formVue.dataset.subData[bodyId1][i].data.b_taxrate;
							bodytax = bodytotal *bodytaxrate;
							bodycurrency = orderdata['rate'];
							totalprice = totalprice+bodytotal;
							totaltax = totaltax+bodytax;
							ototalprice = ototalprice + (bodytotal*bodycurrency)
							ototaltax = ototaltax + (bodytax*bodycurrency)
						}
						else if(formVue.dataset.subData[bodyId1][i].data.b_tax == "稅內含"){
							bodytaxrate = formVue.dataset.subData[bodyId1][i].data.b_taxrate;
							bodytotal = parseFloat(formVue.dataset.subData[bodyId1][i].data.body_subtotal).toFixed(2);
							bodytotal = parseFloat(bodytotal);
							bodytotal2 = parseFloat(bodytotal/(1+bodytaxrate)).toFixed(2);
							bodytotal2 = parseFloat(bodytotal2);
							bodytotal2 =Math.round(bodytotal2);
							//console.log(bodytotal2);
							bodytax= parseFloat(bodytotal-(bodytotal/(1+bodytaxrate))).toFixed(2);
							bodytax= parseFloat(bodytax);
							bodytax= Math.round(bodytax);
							bodycurrency = orderdata['rate'];
							//console.log(bodytotal2);
							//console.log(bodytotal2);
							totalprice = totalprice + bodytotal2;
							totaltax = totaltax + bodytax;
							ototalprice = ototalprice + (bodytotal2*bodycurrency)
							ototaltax = ototaltax + (bodytax*bodycurrency)
						}

						 else if(formVue.dataset.subData[bodyId1][i].data.b_tax == "零稅率" ||formVue.dataset.subData[bodyId1][i].data.b_tax == "免稅" ){
							bodytotal = Number(formVue.dataset.subData[bodyId1][i].data.body_subtotal);
							bodytax = 0;
							bodycurrency = orderdata['rate'];
							totalprice = totalprice + bodytotal;
							totaltax = totaltax + bodytax;
							ototalprice = ototalprice + (bodytotal*bodycurrency)
							ototaltax = ototaltax + (bodytax*bodycurrency)
							//total = Number(total+oSubtotal);

						}
						else{

						}
						//console.log(totalprice);
					}

				}
				//console.log(ototalprice);
				//console.log(ototaltax);
				//console.log(totalprice);
				//console.log(totaltax);
				gettotal=parseFloat(totalprice).toFixed(2);
				getrate=formVue.dataset.subData[bodyId1][0].data.b_rate;
				//console.log(gettotal);
				//console.log(getrate);
				formVue.dataset.data.osubtotal = gettotal;
                formVue.dataset.data.otax = totaltax;
                formVue.dataset.data.ototal =parseFloat(totalprice+totaltax).toFixed(0);
				formVue.dataset.data.ssubtotal = ototalprice;
				formVue.dataset.data.stax =ototaltax;
				formVue.dataset.data.stotal = parseFloat(ototalprice+ototaltax).toFixed(0);
                formVue.dataset.data.final_pmt=parseFloat(Number(totalprice)+Number(totaltax)).toFixed(0);

        }
        localStorage.removeItem("shipdata");
	})
    window.injects.injectOnView.push((that, pageData, id) => {
        if('{{session("username")}}' == "phaith" || '{{session("username")}}' == "yltest" || '{{session("username")}}' == "ocean"){
            that.dataset.subData[bodyId1][0].schema.fields.cost.field_show_on_form = false ;
        }
    })
    window.injects.injectOnEdit.push((that, pageData, id) => {
		original_data = that.dataset.data;
		original_subData = that.dataset.subData;
		pEdit = true;
        if('{{session("username")}}' == "phaith" || '{{session("username")}}' == "yltest" || '{{session("username")}}' == "ocean"){
            that.dataset.subData[bodyId1][0].schema.fields.cost.field_show_on_form = false ;
        }
        that.dataset.status = 'update'
		// storeNumRecord();
        let subData = that.dataset.subData[bodyId1];
		let shipData = {
			ship_code : that.dataset.data.ship_code,
		};
		sendAPIRequest(getURL(`api/inject/checkExisInShipBack`),"post",shipData).then(result => {
		   if( result['status'] != 0 ){
			   shipBackExist = true;
			   let readArr = ['client_code','client_name','currency','depot_code','depot_name','osubtotal','otax','ototal','rate','remarks','ship_date','ssubtotal','stax','stotal','tax','taxrate','undertaker','undertakerday','undertakername'];
				for(let val of readArr){
					 that.$set(that.dataset.override.fields, val ,{ field_readonly : true });
				}
			   for (const [key, value] of Object.entries(result['res'])) {
				   for (let element of subData) {
						if(element.schema && value.ship_no == element.data.id ){
						   for(let key in element.schema.fields){
                            if(key != "payment_status"){
							   that.$set(element.override.fields, key ,{ field_readonly : true });
                            }
						   }
							element.override.preventDelete  = true;
						}

					}
			   }

		   }
		});
        for (let element of subData) {
            if(element.data.id != null){
                element.status = that.dataset.status
            }
            if (element.schema) {
                if( element.data.client_order_code != null ){
                    element.override.fields['product_code'] = {
                        field_readonly: true
                    };
                    formVue.$set(formVue.dataset.override.fields, 'client_code' ,{ field_readonly : true });
                }
            }
        }

        let cited = false
        subData.forEach((row, rowIndex) => {
            //表身no有被其他單引用客戶要鎖
            let shipData = {
                temp: 'ship_no',
                no: row.data.id,
                tables:['BA203_62']
            };
            sendAPIRequest(getURL(`api/inject/cited`),"post",shipData).then(result => {
                if(result == 1){
                    cited = true
                }
            })
        });
        if(cited == true){
            formVue.$set(formVue.dataset.override.fields, 'client_code' ,{ field_readonly : true });
        }

        for(let k in that.dataset.subData[bodyId1]){
            if((that.dataset.subData[bodyId1][k].data.client_order_code != null && that.dataset.subData[bodyId1][k].data.client_order_code != '') || cited == true){
                formVue.$set(formVue.dataset.override.fields, 'client_code' ,{ field_readonly : true });
            }
            if( that.dataset.subData[bodyId1][k].data.keg == 'Y' ){
               that.dataset.subData[bodyId1][k].override.fields['body_depot_code'] = {field_readonly: true};
            }
        }
	})
    window.injects.injectOnCopy.push((that, pageData, id) => {
		// storeNumRecord();
		that.dataset.override.fields['print_btn']  = { field_readonly : true };
		that.dataset.override.fields['print_btn2']  = { field_readonly : true };
        if('{{session("username")}}' == "phaith" || '{{session("username")}}' == "yltest" || '{{session("username")}}' == "ocean"){
            that.dataset.subData[bodyId1][0].schema.fields.cost.field_show_on_form = false ;
        }
        let subData = that.dataset.subData[bodyId1];
        for (let element of subData) {
            if (element.schema) {
                if( element.data.client_order_code != null ){
                    element.override.fields['product_code'] = {
                        field_readonly: true
                    };
                    formVue.$set(formVue.dataset.override.fields, 'client_code' ,{ field_readonly : true });
                }
            }
        }

        let cited = false
        subData.forEach((row, rowIndex) => {
            //表身no有被其他單引用客戶要鎖
            let shipData = {
                temp: 'ship_no',
                no: row.data.id,
                tables:['BA203_62']
            };
            sendAPIRequest(getURL(`api/inject/cited`),"post",shipData).then(result => {
                if(result == 1){
                    cited = true
                }
            })
        });
        if(cited == true){
            formVue.$set(formVue.dataset.override.fields, 'client_code' ,{ field_readonly : true });
        }

        for(let k in that.dataset.subData[bodyId1]){
            if(that.dataset.subData[bodyId1][k].data.client_order_code != null && that.dataset.subData[bodyId1][k].data.client_order_code != ''){
                for(let e in that.dataset.schema.fields){
                    if(e == 'client_code'){
                        formVue.dataset.override.fields[e] = {field_readonly: true};
                    }
                }
            }
            if( that.dataset.subData[bodyId1][k].data.keg == 'Y' ){
               that.dataset.subData[bodyId1][k].override.fields['body_depot_code'] = {field_readonly: true};
            }
        }
	})
    window.injects.injectOnReferenceWrite.push((that, pageData, fromField, data, fields, dataset) => {
		// editStatus = false;
		that.dataset.override.fields['print_btn']  = { field_readonly : true };
		that.dataset.override.fields['print_btn2']  = { field_readonly : true };
		let form_id = dataset.form_id;
		let oSubtotal = 0;
		let otax = 0;
		let pototal = 0;
		let bodytotal =0;
		let bodytaxrate = 0;
		let total = 0;
		let totaltax = 0;
		let alltotal = 0;

		//console.log(data);
		if( pageData.page.page_id == mainPageId ){
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
				}
			}else if( fromField.field_code == "combi_name" ){
				let dataPrice = data.combi_price;
				dataset.data.o_body_price = dataPrice;
			}else if( fromField.field_code == "combi_code" ){
				let dataPrice = data.combi_price;
				dataset.data.o_body_price = dataPrice;
			}else if( fromField.field_code == "product_code" ){//換算率
				dataset.data.body_rate = "1";
				let dataPrice = data.sell_price;
				dataset.data.o_body_price = dataPrice;
				let taxrate = that.dataset.data.taxrate;
				let rate = dataset.data.body_rate ;
				let subDataArray = that.dataset.subData[bodyId1];
                dataset.data.b_pmt_date = that.dataset.data.h_pmt_date;
				let parentDataset = that.dataset;
				let productData = {
					product_code:data.product_code,
				};
				let index = subDataArray.findIndex(x => x.selected);
			    sendAPIRequest(getURL(`api/inject/changeProductCode`),"post",productData).then(result => {
				   if( result['product_code'] !== "undefined" ){
					   if( result['product_kind'] == "費用" ){
						  that.$set(dataset.override.fields, 'clear' ,{ field_readonly : true });
						//   that.$set(dataset.override.fields, 'source_keg' ,{ field_readonly : true });
						//    emptyKeg(dataset.data.source_keg);
						   if( that.dataset.data.depot_code ){
								dataset.data.body_depot_code = that.dataset.data.depot_code;
								dataset.data.body_depot_name = that.dataset.data.depot_name;
						   }
					   }else{
						    // that.$set(dataset.override.fields, 'source_keg' ,{ field_readonly : false });
						    let theSame = 0;
						    /* outer:
							 for( let element of that.dataset.subData[bodyId1] ){
								let subKegData = element.data.source_keg.subData[kegSubFormId];
								for(let arr of subKegData){
									if( arr.data.keg == that.dataset.data.keg){
										theSame = 1 ;
										break outer;
									}
								}
							} */
						   /* if( theSame == 0 && that.dataset.data.keg && bucketArr['status'] == 1 ){
							   if( (!dataset.data.body_depot_code || dataset.data.body_depot_code == bucketArr['data']['depot_code'] ) && bucketArr['data']['product_code'] == dataset.data.product_code ){
								   dataset.data.body_num = bucketArr['data']['num'];
								   dataset.data.body_depot_code = bucketArr['data']['depot_code'];
								   dataset.data.body_depot_name = bucketArr['data']['depot_name'];
								   dataset.data.keg = "Y";
								   that.$set(subDataArray[index].override.fields, 'body_depot_code' ,{ field_readonly : true });
								//    createKeg(dataset,bucketArr);
							   }else{
									dataset.data.body_depot_code = that.dataset.data.depot_code;
									dataset.data.body_depot_name = that.dataset.data.depot_name;
									dataset.data.keg = null
									// emptyKeg(dataset.data.source_keg);
								    that.$set(subDataArray[index].override.fields, 'clear' ,{ field_readonly : true });
									that.$set(subDataArray[index].override.fields, 'body_depot_code' ,{ field_readonly : false });
							   }
						   }else  */
						   if( that.dataset.data.depot_code ){
								dataset.data.body_depot_code = that.dataset.data.depot_code;
								dataset.data.body_depot_name = that.dataset.data.depot_name;
								dataset.data.keg = null
								// emptyKeg(dataset.data.source_keg);
								that.$set(subDataArray[index].override.fields, 'clear' ,{ field_readonly : true });
								that.$set(subDataArray[index].override.fields, 'body_depot_code' ,{ field_readonly : false });
							}else{
								dataset.data.keg = null
								// emptyKeg(dataset.data.source_keg);
								that.$set(subDataArray[index].override.fields, 'clear' ,{ field_readonly : true });
								that.$set(subDataArray[index].override.fields, 'body_depot_code' ,{ field_readonly : false });
							}
					   }
				   }
					osubtotal = countSubtotal2(dataset,subDataArray,parentDataset,"body_subtotal","body_num","o_body_price","osubtotal","discount",dataPrice)
					countOriginal(osubtotal,parentDataset,"osubtotal","otax","ototal",taxrate,bodyId1,"body_subtotal");
					countStandard(osubtotal,parentDataset,rate,"ssubtotal","stax","stotal","otax","ototal");

				});
				// storeNumRecord();

			}else if( fromField.field_code == "currency" ){
				let rate = data.currency_exchrate; //匯率
				let osubtotal = dataset.data.osubtotal;
				//console.log(rate);
				countStandard(osubtotal,dataset,rate,"ssubtotal","stax","stotal","otax","ototal");
				//console.log(data);
				for (let element of dataset.subData[bodyId1]) {

					if( element.data.product_code ){
						if( element.data.b_currency == null ||element.data.b_currency == "" ){
						   element.data.b_currency = data.currency_name;
							element.data.b_rate =data.currency_exchrate;

						}
					}

				}
			}

			else if( fromField.field_code == "b_tax" ){
				let bodytotal= 0;
				 let bodytaxrate= 0;
				 let oSubtotal= 0;
				 let totalprice = 0;
				 let totaltax = 0;
				 let orate = 0;
				 let ototalprice =0;
				 let ototaltax =0;
				 let index = formVue.dataset.subData[bodyId1].findIndex(x => x.selected);
				 
				// console.log(data);
				if(data.tax_name == "稅外加"){
							bodytotal = parseFloat(formVue.dataset.subData[bodyId1][index].data.body_subtotal);

							bodytaxrate = data.tax_taxrate;
							bodytax = bodytotal *bodytaxrate;
							bodycurrency = formVue.dataset.data.rate;
							totalprice = totalprice+bodytotal;
							totaltax = totaltax+bodytax;
							ototalprice = ototalprice + (bodytotal*(bodycurrency))
							ototaltax = ototaltax + (bodytax*(bodycurrency))
							//console.log("a");
						}
						if(data.tax_name == "稅內含"){
							bodytaxrate = data.tax_taxrate;
							bodytotal = parseFloat(formVue.dataset.subData[bodyId1][index].data.body_subtotal);
							bodytotal2 = parseFloat(bodytotal/(1+bodytaxrate));
							//console.log(bodytotal2);
							bodytax= parseFloat(bodytotal-(bodytotal/(1+bodytaxrate)));
							bodycurrency = formVue.dataset.data.rate;
							//console.log(bodytotal2);
							//console.log(bodytotal2);
							totalprice = totalprice + bodytotal2;
							totaltax = totaltax + bodytax;
							ototalprice = ototalprice + (bodytotal2*bodycurrency)
							ototaltax = ototaltax + (bodytax*bodycurrency)
							//console.log("b");
						}

						if(data.tax_name == "零稅率" ||data.tax_name == "免稅" ){
							bodytotal = Number(formVue.dataset.subData[bodyId1][index].data.body_subtotal);
							bodytax = 0;
							bodycurrency = formVue.dataset.data.rate;
							totalprice = totalprice + bodytotal;
							totaltax = totaltax + bodytax;
							ototalprice = ototalprice + (bodytotal*bodycurrency)
							ototaltax = ototaltax + (bodytax*bodycurrency)
						}
						else{

						}
				//console.log(totaltax)

				 for(i=0;i<=formVue.dataset.subData[bodyId1].length-1;i++) {


					if( formVue.dataset.subData[bodyId1][i].data.product_code ){
						if(i == index){

							continue;
						}
						if(formVue.dataset.subData[bodyId1][i].data.b_tax == "稅外加"){

							bodytotal = parseFloat(formVue.dataset.subData[bodyId1][i].data.body_subtotal,10).toFixed(2);
							bodytotal = parseFloat(bodytotal);
							bodytaxrate = formVue.dataset.subData[bodyId1][i].data.b_taxrate;
							bodytax = bodytotal *bodytaxrate;
							bodycurrency = formVue.dataset.data.rate;
							totalprice = totalprice+bodytotal;
							totaltax = totaltax+bodytax;
							ototalprice = ototalprice + (bodytotal*(bodycurrency))
							ototaltax = ototaltax + (bodytax*(bodycurrency))
						}
						else if(formVue.dataset.subData[bodyId1][i].data.b_tax == "稅內含"){

							bodytaxrate = formVue.dataset.subData[bodyId1][i].data.b_taxrate;
							bodytotal = parseFloat(formVue.dataset.subData[bodyId1][i].data.body_subtotal);
							bodytotal = parseFloat(bodytotal);
							bodytotal2 = parseFloat(bodytotal/(1+bodytaxrate));
							bodytax= parseFloat(bodytotal-(bodytotal/(1+bodytaxrate)));
							bodycurrency = formVue.dataset.data.rate;

							totalprice = totalprice + bodytotal2;
							totaltax = totaltax + bodytax;
							ototalprice = ototalprice + (bodytotal2*bodycurrency)
							ototaltax = ototaltax + (bodytax*bodycurrency)
						}

						 else {

							bodytotal = Number(formVue.dataset.subData[bodyId1][i].data.body_subtotal);
							bodytax = 0;
							bodycurrency = formVue.dataset.data.rate;
							totalprice = totalprice + bodytotal;
							totaltax = totaltax + bodytax;
							ototalprice = ototalprice + (bodytotal*bodycurrency)
							ototaltax = ototaltax + (bodytax*bodycurrency)


						}


					}

				}

				gettotal=parseFloat(totalprice).toFixed(2);
				formVue.dataset.data.osubtotal = parseFloat(gettotal).toFixed(2);
                formVue.dataset.data.otax = parseFloat(totaltax).toFixed(2);
                formVue.dataset.data.ototal =parseFloat(totalprice+totaltax).toFixed(0);
				formVue.dataset.data.ssubtotal = parseFloat(ototalprice).toFixed(2);
				formVue.dataset.data.stax =parseFloat(ototaltax).toFixed(2);
				formVue.dataset.data.stotal = parseFloat(ototalprice+ototaltax).toFixed(0);
                formVue.dataset.data.final_pmt = parseFloat(Number(totalprice)+Number(totaltax)).toFixed(0);
		}
		else if( fromField.field_code == "b_currency" ){
			let bodytotal= 0;
				 let bodytaxrate= 0;
				 let oSubtotal= 0;
				 let totalprice = 0;
				 let totaltax = 0;
				 let orate = 0;
				 let ototalprice =0;
				 let ototaltax =0;

				for(i=0;i<=formVue.dataset.subData[bodyId1].length-1;i++) {
					formVue.dataset.subData[bodyId1][i].data.body_subtotal=((formVue.dataset.subData[bodyId1][i].data.body_price * formVue.dataset.subData[bodyId1][i].data.body_num * formVue.dataset.subData[bodyId1][i].data.discount)/100).ToFixed(2);
					//console.log(formVue.dataset.subData[bodyId1][i]);
					if( formVue.dataset.subData[bodyId1][i].data.product_code ){
						//console.log(formVue.dataset.subData[bodyId1][i].data.b_tax);
						if(formVue.dataset.subData[bodyId1][i].data.b_tax == "稅外加"){
							bodytotal = parseFloat(formVue.dataset.subData[bodyId1][i].data.body_subtotal,10).toFixed(2);
							bodytotal = parseFloat(bodytotal);
							bodytaxrate = formVue.dataset.subData[bodyId1][i].data.b_taxrate;
							bodytax = bodytotal *bodytaxrate;
							bodycurrency = formVue.dataset.subData[bodyId1][i].data.b_rate;
							totalprice = totalprice+bodytotal;
							totaltax = totaltax+bodytax;
							ototalprice = ototalprice + (bodytotal*bodycurrency)
							ototaltax = ototaltax + (bodytax*bodycurrency)
						}
						else if(formVue.dataset.subData[bodyId1][i].data.b_tax == "稅內含"){
							bodytaxrate = formVue.dataset.subData[bodyId1][i].data.b_taxrate;
							bodytotal = parseFloat(formVue.dataset.subData[bodyId1][i].data.body_subtotal).toFixed(2);
							bodytotal = parseFloat(bodytotal);
							bodytotal2 = parseFloat(bodytotal/(1+bodytaxrate)).toFixed(2);
							bodytotal2 = parseFloat(bodytotal2);
							bodytotal2 =Math.round(bodytotal2);
							//console.log(bodytotal2);
							bodytax= parseFloat(bodytotal-(bodytotal/(1+bodytaxrate))).toFixed(2);
							bodytax= parseFloat(bodytax);
							bodytax= Math.round(bodytax);
							bodycurrency = formVue.dataset.subData[bodyId1][i].data.b_rate;
							//console.log(bodytotal2);
							//console.log(bodytotal2);
							totalprice = totalprice + bodytotal2;
							totaltax = totaltax + bodytax;
							ototalprice = ototalprice + (bodytotal2*bodycurrency)
							ototaltax = ototaltax + (bodytax*bodycurrency)
						}

						 else if(formVue.dataset.subData[bodyId1][i].data.b_tax == "零稅率" ||formVue.dataset.subData[bodyId1][i].data.b_tax == "免稅" ){
							bodytotal = Number(formVue.dataset.subData[bodyId1][i].data.body_subtotal);
							bodytax = 0;
							bodycurrency = formVue.dataset.subData[bodyId1][i].data.b_rate;
							totalprice = totalprice + bodytotal;
							totaltax = totaltax + bodytax;
							ototalprice = ototalprice + (bodytotal*bodycurrency)
							ototaltax = ototaltax + (bodytax*bodycurrency)
							//total = Number(total+oSubtotal);

						}
						else{

						}
						//console.log(totalprice);
					}

				}
				//console.log(ototalprice);
				//console.log(ototaltax);
				//console.log(totalprice);
				//console.log(totaltax);
				gettotal=parseFloat(totalprice).toFixed(2);
				getrate=formVue.dataset.subData[bodyId1][0].data.b_rate;
				//console.log(gettotal);
				//console.log(getrate);
				formVue.dataset.data.osubtotal = gettotal;
                formVue.dataset.data.otax = totaltax;
                formVue.dataset.data.ototal =parseFloat(totalprice+totaltax).toFixed(0);
				formVue.dataset.data.ssubtotal = ototalprice;
				formVue.dataset.data.stax =ototaltax;
				formVue.dataset.data.stotal = parseFloat(ototalprice+ototaltax).toFixed(0);
			//console.log(sum);
		}





			else if( fromField.field_code == "unit_code" ){
				let subDataArray = that.dataset.subData[bodyId1];
				let parentDataset = that.dataset;
				let taxrate = parentDataset.data.taxrate;
				let rate = parentDataset.data.rate;
				let dataPrice = data.body_sell_price;

				osubtotal = countSubtotal2(dataset,subDataArray,parentDataset,"body_subtotal","body_num","o_body_price","osubtotal",dataPrice)
				countOriginal(osubtotal,parentDataset,"osubtotal","otax","ototal",taxrate,bodyId1,"body_subtotal");
				countStandard(osubtotal,parentDataset,rate,"ssubtotal","stax","stotal","otax","ototal");
                formVue.dataset.data.final_pmt = parseFloat(Number(formVue.dataset.data.ototal)).toFixed(2);
//				const rowRum = dataset.data.body_num * dataset.data.body_rate;
//				bodyNumRecord[index] = rowRum;
				// storeNumRecord();
			}
            if(fromField.field_code == "combi_name" ){
                let index = formVue.dataset.subData[bodyId1].findIndex(x => x.selected);

                if(con % 2==0){
                if(formVue.dataset.subData[bodyId1][index].data.combi_name == ""){
                    alert("因更改組合名稱為空白，請確認單價");
                }
            }
            con=con+1;
            }

            if(fromField.field_code == "client_code" ){
                formVue.dataset.data.source_client_order_code.data.client_code=data.client_code;
            }
		}else if( pageData.page.page_id == kegPageId ){
			if( fromField.field_code == "keg"  ){

				let subData = that.dataset.subData[kegSubFormId];
				let subDataArray = formVue.dataset.subData[bodyId1];
				let parentDataset = formVue.dataset;
				let taxrate = formVue.dataset.data.taxrate;
				let rate = formVue.dataset.data.rate;
				let dataPrice = 0;
				let theSame = 0;
				outer:
				for( let element of that.parentVue().dataset.subData[bodyId1] ){
					let subKegData = element.data.source_keg.subData[kegSubFormId];
					let index = subKegData.findIndex(x => x.selected);
					for( let skIndex in subKegData ){
						if( subKegData[skIndex].data.keg == data.keg){
							if( index == skIndex ){
								theSame = 2 ;
							}else{
								theSame = 1 ;
							}
							break outer;
						}
					}
				}
				if( theSame == 0 ){
					dataset.data.body_num = data.num;
					let numTotal = subData.reduce(function(prev, kegd) {
						if(!kegd.data.body_num){
							kegd.data.body_num = 0;
						}
					    return parseFloat(prev) + parseFloat(kegd.data.body_num);
					}, 0);

					that.addEmptyRow(that.dataset,kegSubFormId);
					for( let element of that.parentVue().dataset.subData[bodyId1] ){
						if( element.selected ){
							element.data.keg = "Y";
							element.data.body_num = (((element.data.body_num * element.data.body_rate) +  parseFloat(data.num) )/element.data.body_rate).toFixed(2);
							// element.data.body_num = parseFloat(numTotal/element.data.body_rate).toFixed(2);
							element.data.body_subtotal = parseFloat(element.data.body_num*element.data.body_price ).toFixed(2);
							dataPrice = element.data.body_price;
							that.$set(element.override.fields, 'body_depot_code' ,{ field_readonly : true });
							that.$set(element.override.fields, 'clear' ,{ field_readonly : false });
						}
					}
					osubtotal = countSubtotal(dataset,subDataArray,parentDataset,"body_subtotal","body_num","o_body_price","osubtotal",dataPrice)
					countOriginal(osubtotal,parentDataset,"osubtotal","otax","ototal",taxrate,bodyId1,"body_subtotal");
					countStandard(osubtotal,parentDataset,rate,"ssubtotal","stax","stotal","otax","ototal");
				}else{
					if( theSame == 1 ){
						for (let key in data) {
							data[key] = null;
						}
					}else{

					}
					alert( "此桶號已在此出貨單中存在，請確認" );
				}
				// storeNumRecord();
			}
		}
	})
    window.injects.injectOnRowAdd.push((that, pageData, targetSubDataArray, dataset) => {})
    window.injects.injectOnRowDelete.push((that, pageData, parentDataset, formID, rowIndex) => {
		if( pageData.page.page_id == mainPageId){
            if(formID==bodyId1){
            let num = 0;
            let subData = that.dataset.subData[bodyId1];
            for (let key in subData) {
                if (subData[key].schema && key != rowIndex ) {
                    if( subData[key].data.client_order_code != null ){
                        num++
                        break;
                    }
                }
            }

            if(num==0 && !shipBackExist ){
                formVue.$set(formVue.dataset.override.fields, 'client_code' ,{ field_readonly : false });
            }else{
                formVue.$set(formVue.dataset.override.fields, 'client_code' ,{ field_readonly : true });
            }
				let rate = parentDataset.data.rate;
				let taxrate = that.dataset.subData[53][rowIndex].data.b_taxrate;
				let deleted_row = that.dataset.subData[53][rowIndex].data.body_subtotal;
				if(that.dataset.subData[53][rowIndex].data.body_subtotal != null && that.dataset.subData[53][rowIndex].data.body_subtotal !=  ".00"){

				if(that.dataset.subData[53][rowIndex].data.b_tax == "稅內含"){
				deleted_subtotal = parseFloat(deleted_row/(1 + taxrate ));
				deleted_tax = parseFloat(deleted_row - deleted_subtotal);
				formVue.dataset.data.osubtotal = parseFloat(that.dataset.data.osubtotal - deleted_subtotal).toFixed(2);
				formVue.dataset.data.otax = parseFloat(that.dataset.data.otax - deleted_tax).toFixed(2);
				formVue.dataset.data.ototal = parseFloat(formVue.dataset.data.osubtotal)+parseFloat(formVue.dataset.data.otax);
                formVue.dataset.data.final_pmt = parseFloat(Number(formVue.dataset.data.final_pmt)-Number(deleted_subtotal)-Number(deleted_tax)).toFixed(2);
				}
				else{
				formVue.dataset.data.osubtotal = parseFloat(that.dataset.data.osubtotal- parseFloat(that.dataset.subData[53][rowIndex].data.body_subtotal)).toFixed(2);
				formVue.dataset.data.otax = parseFloat(that.dataset.data.otax-(parseFloat(deleted_row)*taxrate)).toFixed(2);
				formVue.dataset.data.ototal = parseFloat(parseFloat(formVue.dataset.data.osubtotal) + parseFloat(formVue.dataset.data.otax)).toFixed(2);
				formVue.dataset.data.final_pmt = parseFloat(Number(formVue.dataset.data.final_pmt)-Number(that.dataset.subData[53][rowIndex].data.body_subtotal)-Number(deleted_row*taxrate)).toFixed(2);
                }




				formVue.dataset.data.ssubtotal = parseFloat(formVue.dataset.data.osubtotal * rate).toFixed(2);
				formVue.dataset.data.stax =  parseFloat(formVue.dataset.data.otax * rate).toFixed(2);
				formVue.dataset.data.stotal = parseFloat(formVue.dataset.data.ototal * rate).toFixed(0);

		}

    }
	}
	})
    window.injects.injectOnHeadInput.push((that, pageData, field, dataset) => {})
    window.injects.injectOnHeadChange.push((that, pageData, field, dataset) => {
		const filedCode = field.field_code;
		if( pageData.page.page_id == mainPageId ){
			// editStatus = false;
			that.dataset.override.fields['print_btn']  = { field_readonly : true };
			that.dataset.override.fields['print_btn2']  = { field_readonly : true };
			let taxrate = dataset.data.taxrate;
			let rate = dataset.data.rate;
			let osubtotal = dataset.data.osubtotal;
			if( filedCode == "taxrate" ){
				countOriginal(osubtotal,dataset,"osubtotal","otax","ototal",taxrate,bodyId1,"body_subtotal");
				countStandard(osubtotal,dataset,rate,"ssubtotal","stax","stotal","otax","ototal");
			}else if( filedCode == "rate" ){
				countStandard(osubtotal,dataset,rate,"ssubtotal","stax","stotal","otax","ototal");
			}else if( filedCode == "keg" ){
				if( dataset.data.keg ){
					let bucketData = {
						keg:dataset.data.keg
					};
					sendAPIRequest(getURL(`api/inject/getBucketProduct`),"post",bucketData).then(result => {
						bucketArr = result;
						let subDataArray = that.dataset.subData[bodyId1];
						if(result['status'] == 1){
							kegStatus = 1;
							let res = result['data'];
							let kegProductCode = result['data']['product_code'];
							let kegDepoCode = result['data']['depot_code'];
							let subData = that.dataset.subData[bodyId1];
							let productExist = 0
							let num = result['data']['num'];
							let taxrate = dataset.data.taxrate;
							// let rate = dataset.data.rate;
							for (let element of subData) {
								// let kegSubData = element['data']['source_keg']['subData'][kegSubFormId];
								if( element.data.product_code == kegProductCode && (element.data.body_depot_code == kegDepoCode || !element.data.body_depot_code) ){
									productExist = 1;
									// kegForm = createKeg(element,result);
									if( kegForm == 0 || kegForm == 2 ){
										that.$set(element.override.fields, 'clear' ,{ field_readonly : false });
									    element['data']['product_code'] = kegProductCode;
										element['data']['product_name'] = result['data']['product_name'];
										element['data']['body_num'] = ( parseFloat(element['data']['body_num']) + parseFloat(num/element['data']['body_rate']) ).toFixed(2);
										element['data']['body_price'] = result['data']['body_price'];
										element['data']['body_subtotal'] = parseFloat(result['data']['body_price'] *element['data']['body_num']).toFixed(2);
										element['data']['keg'] = 'Y';
										element['data']['body_depot_code'] = result['data']['depot_code'];
										element['data']['body_depot_name'] = result['data']['depot_name'];

										that.$set(element.override.fields, 'body_depot_code' ,{ field_readonly : true });
										osubtotal = countSubtotal2(element,subData,dataset,"body_subtotal","body_num","o_body_price","osubtotal");
										break;
									}else{
										break;
									}
								}
							}
							if( productExist == 0 ){
								let index = formVue.dataset.subData[bodyId1].length - 1 ; //暫時寫在最後一行
								if( subData[index]['data']['product_code'] != null ){
								   that.addEmptyRow(dataset,bodyId1);
								   index = formVue.dataset.subData[bodyId1].length - 1;
								}
								subData[index]['data']['product_code'] = kegProductCode;
								subData[index]['data']['product_name'] = result['data']['product_name'];
								subData[index]['data']['unit_code'] = result['data']['unit_code'];
								subData[index]['data']['unit_name'] = result['data']['unit_name'];
								subData[index]['data']['body_num'] = num;
								subData[index]['data']['body_price'] = result['data']['body_price'];
								subData[index]['data']['body_subtotal'] = parseFloat(result['data']['body_price'] * num).toFixed(2);
								subData[index]['data']['body_rate'] = '1';
								// subData[index]['data']['keg'] = 'Y';
								subData[index]['data']['body_depot_code'] = result['data']['depot_code'];
								subData[index]['data']['body_depot_name'] = result['data']['depot_name'];

								/* subData[index]['data']['source_keg']['subData'][kegSubFormId][0].data.body_num = num;
								subData[index]['data']['source_keg']['subData'][kegSubFormId][0].data.remaining_num = parseFloat(num);
								subData[index]['data']['source_keg']['subData'][kegSubFormId][0].data.keg = dataset.data.keg;
								subData[index]['data']['source_keg'].vue().addEmptyRow(subData[index]['data']['source_keg'].vue().dataset,kegSubFormId); */

								that.$set(subData[index].override.fields, 'clear' ,{ field_readonly : false });
								that.$set(subData[index].override.fields, 'body_depot_code' ,{ field_readonly : true });
								osubtotal = countSubtotal2(subData[index],subData,dataset,"body_subtotal","body_num","o_body_price","osubtotal");
//								console.log(subData[index]['data']['source_keg']['subData'][kegSubFormId][0].data.body_num);

							}
							kegStatus = 0;
							countOriginal(osubtotal,dataset,"osubtotal","otax","ototal",taxrate,bodyId1,"body_subtotal");
			   				countStandard(osubtotal,dataset,rate,"ssubtotal","stax","stotal","otax","ototal");
						}else{
							alert(result['text']);
						}
						// storeNumRecord();
					});
				}
			}else if( filedCode == "h_pmt_date" ){
            for( let index in dataset.subData[bodyId1]){
                if(dataset.subData[bodyId1][index].data.product_code){
                    dataset.subData[bodyId1][index].data.b_pmt_date=dataset.data.h_pmt_date;
                }
            }
        }
		}
	})
    window.injects.injectOnHeadClick.push((that, pageData, field, dataset) => {
		const filedCode = field.field_code;

		if( pageData.page.page_id == littlePageId ){


			if( filedCode == 'searchbtn' ){

				that.setFormOption(littlePageIdBodyFormID,'disableAutoAddOrRemoveRow',true);
				let serachData = {
					advancedayS:dataset.data.advancedayS,
					advancedayE:dataset.data.advancedayE,
					client_order_codeS:dataset.data.client_order_codeS,
					client_order_codeE:dataset.data.client_order_codeE,
					product_codeS:dataset.data.product_codeS,
					product_codeE:dataset.data.product_codeE,
					client_code:formVue.dataset.data.client_code
				};
				let url = `api/inject/getCustomerOrder`;
				searchbtn(that,serachData,url,littlePageIdBodyFormID,true,readonlyBody=[],readonlyHead=[]);

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
				for( let index in dataset.subData[littlePageIdBodyFormID]){
					if( dataset.subData[littlePageIdBodyFormID][index].data.choose ==  1 ){
						SubDataLas = formVue.dataset.subData[bodyId1].length - 1 ;
						currentRow = that.parentVue().dataset.subData[bodyId1][SubDataLas]
						//console.log(currentRow);
						if( !formVue.checkRowIsEmpty(currentRow, schema) ){
							that.parentVue().addEmptyRow(that.parentVue().dataset,bodyId1);
							SubDataLas = formVue.dataset.subData[bodyId1].length - 1 ; //暫時寫在最後一行
						}
						for( let key in dataset.subData[littlePageIdBodyFormID][index].data) {
							//console.log(key);

							if( key == "body_num" ){
								formVue.dataset.subData[bodyId1][SubDataLas]['data'][key] = dataset.subData[littlePageIdBodyFormID][index]['data'][key] - dataset.subData[littlePageIdBodyFormID][index]['data']['body_quantity'];
							}else if( (key != "body_quantity" && key != "advanceday" && key != "choose" ) ){
								//console.log(key);
								formVue.dataset.subData[bodyId1][SubDataLas]['data'][key] = dataset.subData[littlePageIdBodyFormID][index]['data'][key];
								formVue.dataset.subData[bodyId1][SubDataLas]['data']['b_tax'] = dataset.subData[littlePageIdBodyFormID][index]['data']['tax'];
								formVue.dataset.subData[bodyId1][SubDataLas]['data']['b_taxrate'] = dataset.subData[littlePageIdBodyFormID][index]['data']['taxrate'];
								formVue.dataset.subData[bodyId1][SubDataLas]['data']['b_currency'] = dataset.subData[littlePageIdBodyFormID][index]['data']['currency'];
								formVue.dataset.subData[bodyId1][SubDataLas]['data']['b_rate'] = dataset.subData[littlePageIdBodyFormID][index]['data']['rate'];

                                if(key == "client_order_code"){
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

						that.parentVue().addEmptyRow(that.parentVue().dataset,bodyId1);
					}
			   }
			   let bodytotal= 0;
				 let bodytaxrate= 0;
				 let oSubtotal= 0;
				 let totalprice = 0;
				 let totaltax = 0;
				 let orate = 0;
				 let ototalprice =0;
				 let ototaltax =0;

				 for(i=0;i<=formVue.dataset.subData[bodyId1].length-1;i++) {


					if( formVue.dataset.subData[bodyId1][i].data.product_code ){
                        formVue.dataset.subData[bodyId1][i].data.body_subtotal=parseFloat((formVue.dataset.subData[bodyId1][i].data.o_body_price * formVue.dataset.subData[bodyId1][i].data.discount)/100).toFixed(2);
						formVue.dataset.subData[bodyId1][i].data.body_subtotal=parseFloat(formVue.dataset.subData[bodyId1][i].data.body_subtotal * formVue.dataset.subData[bodyId1][i].data.body_num).toFixed(2);
                        if(formVue.dataset.subData[bodyId1][i].data.b_tax == "稅外加"){

							bodytotal = parseFloat(formVue.dataset.subData[bodyId1][i].data.body_subtotal,10).toFixed(2);
							bodytotal = parseFloat(bodytotal);
							bodytaxrate = formVue.dataset.subData[bodyId1][i].data.b_taxrate;
							bodytax = bodytotal *bodytaxrate;
							bodycurrency = formVue.dataset.data.rate;
							totalprice = totalprice+bodytotal;
							totaltax = totaltax+bodytax;
							ototalprice = ototalprice + (bodytotal*(bodycurrency))
							ototaltax = ototaltax + (bodytax*(bodycurrency))
						}
						else if(formVue.dataset.subData[bodyId1][i].data.b_tax == "稅內含"){

							bodytaxrate = formVue.dataset.subData[bodyId1][i].data.b_taxrate;
							bodytotal = parseFloat(formVue.dataset.subData[bodyId1][i].data.body_subtotal).toFixed(2);
							bodytotal = parseFloat(bodytotal);
							bodytotal2 = parseFloat(bodytotal/(1+bodytaxrate)).toFixed(2);
							bodytotal2 = parseFloat(bodytotal2);
							bodytax= parseFloat(bodytotal-(bodytotal/(1+bodytaxrate))).toFixed(2);
							bodytax= parseFloat(bodytax);
							bodycurrency = formVue.dataset.data.rate;

							totalprice = totalprice + bodytotal2;
							totaltax = totaltax + bodytax;
							ototalprice = ototalprice + (bodytotal2*bodycurrency)
							ototaltax = ototaltax + (bodytax*bodycurrency)
						}

						 else {

							bodytotal = Number(formVue.dataset.subData[bodyId1][i].data.body_subtotal);
							bodytax = 0;
							bodycurrency = formVue.dataset.data.rate;
							totalprice = totalprice + bodytotal;
							totaltax = totaltax + bodytax;
							ototalprice = ototalprice + (bodytotal*bodycurrency)
							ototaltax = ototaltax + (bodytax*bodycurrency)


						}


					}

				}

				gettotal=parseFloat(totalprice).toFixed(2);
				formVue.dataset.data.osubtotal = gettotal;
                formVue.dataset.data.otax = totaltax;
                formVue.dataset.data.ototal =parseFloat(totalprice+totaltax).toFixed(0);
				formVue.dataset.data.ssubtotal = ototalprice;
				formVue.dataset.data.stax =ototaltax;
				formVue.dataset.data.stotal = parseFloat(ototalprice+ototaltax).toFixed(0);
                formVue.dataset.data.final_pmt = parseFloat(Number(totalprice)+Number(totaltax)).toFixed(0);
				if( getBackStatus ){
				   alert("取回完成");
                    formVue.$set(formVue.dataset.override.fields, 'client_code' ,{ field_readonly : true });
				 }else{
					 alert("無需取回項目");
				 }

			}else if(filedCode == 'select_all'){
                for( let index in dataset.subData[littlePageIdBodyFormID]){
					if(dataset.subData[littlePageIdBodyFormID][index].data.product_code==""||dataset.subData[littlePageIdBodyFormID][index].data.product_code==null){

					}else{
						dataset.subData[littlePageIdBodyFormID][index].data.choose=1;
					}
				}
            }
		}if( pageData.page.page_id == littlePageId1 ){
			if( filedCode == 'searchbtn' ){

				that.setFormOption(littlePageIdBodyFormID1,'disableAutoAddOrRemoveRow',true);
				let serachData1 = {
					client_code:formVue.dataset.data.client_code
				};
                // console.log(serachData1);
				let url = `api/inject/getCustomerOrder1`;
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
                    formVue.$set(formVue.dataset.override.fields, 'client_code' ,{ field_readonly : true });
                    that.setFormOption(littlePageIdBodyFormID1,'disableAutoAddOrRemoveRow',true);
				let serachData1 = {
					client_code:formVue.dataset.data.client_code
				};
                // console.log(serachData1);
				let url = `api/inject/getCustomerOrder1`;
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
		}else if( pageData.page.page_id == mainPageId ){
			if( filedCode == 'print_btn' ||  filedCode == 'print_btn2' ){
				// if( formVue.dataset.override.fields.print_btn.field_readonly || formVue.dataset.override.fields.print_btn2.field_readonly ){
					if( filedCode == 'print_btn' ){
						type = "type1"
					}else{
						type = "type2"
					}
					let shipData = {
						ship_code:that.dataset.data.ship_code,
						type:type
					};
					fullscreenDimmer.loading()
					sendAPIRequest(getURL(`api/inject/printShip`),"post",shipData).then( async result => {
						if(result.status){
							window.open(result.file);
							fullscreenDimmer.unloading()
						}else{
							alert("{{ $commonTranslations['error.unknown'] . $commonTranslations['contact_maintenance'] }}")
							console.error(result);
							fullscreenDimmer.unloading()
						}
					})
				// }else{
				// 	alert("此出貨單已被修改，請先儲存再列印出貨單");
				// }


			}else if(filedCode == 'batch_recv'){
                for( let index in dataset.subData[bodyId1]){
					// console.log(index);
					// console.log(dataset.subData[bodyId1][index].data.product_code);
					if(dataset.subData[bodyId1][index].data.product_code==""||dataset.subData[bodyId1][index].data.product_code==null){

					}else{
						// console.log(dataset.subData[bodyId1][index].data.product_code);
						dataset.subData[bodyId1][index].data.payment_status="已收款";
					}
				}
            }
		}
	})
    window.injects.injectOnBodyInput.push((formVue, pageData, parentDataset, row, field, formID, subDataArray) => {})
    window.injects.injectOnBodyChange.push((that, pageData, parentDataset, row, field, formID, subDataArray) => {
		const filedCode = field.field_code;
		if( pageData.page.page_id == mainPageId ){
			editStatus = false;
			that.dataset.override.fields['print_btn']  = { field_readonly : true };
			that.dataset.override.fields['print_btn2']  = { field_readonly : true };
			let osubtotal = 0; //原幣小計
			let otax = 0; //原幣稅金
			let ototal = 0; //原幣合計
			let ssubtotal =0; //本位幣小計
			let stax = 0; //本位幣稅金
			let stotal = 0; //本位幣合計
			let rate = parentDataset.data.rate; //匯率


			if(filedCode == "body_num" || filedCode == "body_price" || filedCode == "o_body_price" || filedCode == "body_price" || filedCode == "discount"){

				let bodytotal= 0;
				 let bodytaxrate= 0;
				 let oSubtotal= 0;
				 let totalprice = 0;
				 let totaltax = 0;
				 let orate = 0;
				 let ototalprice =0;
				 let ototaltax =0;

				for(i=0;i<=formVue.dataset.subData[bodyId1].length-1;i++) {

					//console.log(formVue.dataset.subData[bodyId1][i]);
					if( formVue.dataset.subData[bodyId1][i].data.product_code ){
						formVue.dataset.subData[bodyId1][i].data.body_price = parseFloat((formVue.dataset.subData[bodyId1][i].data.o_body_price * formVue.dataset.subData[bodyId1][i].data.discount)/100).toFixed(2);
						//console.log(formVue.dataset.subData[bodyId1][i].data.body_price);
                        formVue.dataset.subData[bodyId1][i].data.body_subtotal=parseFloat(formVue.dataset.subData[bodyId1][i].data.body_price * formVue.dataset.subData[bodyId1][i].data.body_num).toFixed(2);
						//console.log(formVue.dataset.subData[bodyId1][i].data.b_tax);
						if(formVue.dataset.subData[bodyId1][i].data.b_tax == "稅外加"){
							bodytotal = parseFloat(formVue.dataset.subData[bodyId1][i].data.body_subtotal);
							bodytotal = parseFloat(bodytotal);
							bodytaxrate = formVue.dataset.subData[bodyId1][i].data.b_taxrate;
							bodytax = bodytotal *bodytaxrate;
							bodycurrency = formVue.dataset.data.rate;
							totalprice = totalprice+bodytotal;
							totaltax = totaltax+bodytax;
							ototalprice = ototalprice + (bodytotal*(bodycurrency))
							ototaltax = ototaltax + (bodytax*(bodycurrency))
						}
						else if(formVue.dataset.subData[bodyId1][i].data.b_tax == "稅內含"){
							bodytaxrate = formVue.dataset.subData[bodyId1][i].data.b_taxrate;
							bodytotal = parseFloat(formVue.dataset.subData[bodyId1][i].data.body_subtotal);
							bodytotal = parseFloat(bodytotal);
							bodytotal2 = parseFloat(bodytotal/(1+bodytaxrate));
							bodytotal2 = parseFloat(bodytotal2);

							//console.log(bodytotal2);
							bodytax= parseFloat(bodytotal-(bodytotal/(1+bodytaxrate)));
							bodytax= parseFloat(bodytax);

							bodycurrency = formVue.dataset.data.rate;
							//console.log(bodytotal2);
							//console.log(bodytotal2);
							totalprice = totalprice + bodytotal2;
							totaltax = totaltax + bodytax;
							ototalprice = ototalprice + (bodytotal2*bodycurrency)
							ototaltax = ototaltax + (bodytax*bodycurrency)
						}

						 else if(formVue.dataset.subData[bodyId1][i].data.b_tax == "零稅率" ||formVue.dataset.subData[bodyId1][i].data.b_tax == "免稅" ){
							bodytotal = Number(formVue.dataset.subData[bodyId1][i].data.body_subtotal);
							bodytax = 0;
							bodycurrency = formVue.dataset.data.rate;
							totalprice = totalprice + bodytotal;
							totaltax = totaltax + bodytax;
							ototalprice = ototalprice + (bodytotal*bodycurrency)
							ototaltax = ototaltax + (bodytax*bodycurrency)
							//total = Number(total+oSubtotal);

						}
						else{

						}
						//console.log(totalprice);

					formVue.dataset.data.osubtotal = parseFloat(totalprice).toFixed(2);
					formVue.dataset.data.otax = parseFloat(totaltax).toFixed(2);
					formVue.dataset.data.ototal =parseFloat(totalprice+totaltax).toFixed(0);
					formVue.dataset.data.ssubtotal = parseFloat(ototalprice).toFixed(2);
					formVue.dataset.data.stax =parseFloat(ototaltax).toFixed(2);
					formVue.dataset.data.stotal = parseFloat(ototalprice+ototaltax).toFixed(0);
                    formVue.dataset.data.final_pmt = parseFloat(Number(totalprice)+Number(totaltax)).toFixed(0);
					formVue.dataset.data.amt_recd = 0;
					formVue.dataset.data.amt_outstanding = formVue.dataset.data.stotal;
					}

				}


			//console.log(sum);


			}
			else if(filedCode == "b_taxrate" ){
				let bodytotal= 0;
				 let bodytaxrate= 0;
				 let oSubtotal= 0;
				 let totalprice = 0;
				 let totaltax = 0;
				 let orate = 0;
				 let ototalprice =0;
				 let ototaltax =0;

				 for(i=0;i<=formVue.dataset.subData[bodyId1].length-1;i++) {


					if( formVue.dataset.subData[bodyId1][i].data.product_code ){
                        formVue.dataset.subData[bodyId1][i].data.body_subtotal=parseFloat((formVue.dataset.subData[bodyId1][i].data.o_body_price * formVue.dataset.subData[bodyId1][i].data.body_num * formVue.dataset.subData[bodyId1][i].data.discount)/100).toFixed(2);
						if(formVue.dataset.subData[bodyId1][i].data.b_tax == "稅外加"){

							bodytotal = parseFloat(formVue.dataset.subData[bodyId1][i].data.body_subtotal,10).toFixed(2);
							bodytotal = parseFloat(bodytotal);
							bodytaxrate = formVue.dataset.subData[bodyId1][i].data.b_taxrate;
							bodytax = bodytotal *bodytaxrate;
							bodycurrency = formVue.dataset.data.rate;
							totalprice = totalprice+bodytotal;
							totaltax = totaltax+bodytax;
							ototalprice = ototalprice + (bodytotal*(bodycurrency))
							ototaltax = ototaltax + (bodytax*(bodycurrency))
						}
						else if(formVue.dataset.subData[bodyId1][i].data.b_tax == "稅內含"){

							bodytaxrate = formVue.dataset.subData[bodyId1][i].data.b_taxrate;
							bodytotal = parseFloat(formVue.dataset.subData[bodyId1][i].data.body_subtotal).toFixed(2);
							bodytotal = parseFloat(bodytotal);
							bodytotal2 = parseFloat(bodytotal/(1+bodytaxrate)).toFixed(2);
							bodytotal2 = parseFloat(bodytotal2);
							bodytotal2 =Math.round(bodytotal2);
							bodytax= parseFloat(bodytotal-(bodytotal/(1+bodytaxrate))).toFixed(2);
							bodytax= parseFloat(bodytax);
							bodytax= Math.round(bodytax);
							bodycurrency = formVue.dataset.data.rate;

							totalprice = totalprice + bodytotal2;
							totaltax = totaltax + bodytax;
							ototalprice = ototalprice + (bodytotal2*bodycurrency)
							ototaltax = ototaltax + (bodytax*bodycurrency)
						}

						 else {

							bodytotal = Number(formVue.dataset.subData[bodyId1][i].data.body_subtotal);
							bodytax = 0;
							bodycurrency = formVue.dataset.data.rate;
							totalprice = totalprice + bodytotal;
							totaltax = totaltax + bodytax;
							ototalprice = ototalprice + (bodytotal*bodycurrency)
							ototaltax = ototaltax + (bodytax*bodycurrency)


						}


					}
					formVue.dataset.subData[bodyId1][i].body_num = parseFloat(formVue.dataset.subData[bodyId1][i].body_num).toFixed(0);
				}

				gettotal=parseFloat(totalprice).toFixed(2);
				formVue.dataset.data.osubtotal = gettotal;
                formVue.dataset.data.otax = totaltax;
                formVue.dataset.data.ototal =parseFloat(totalprice+totaltax).toFixed(0);
				formVue.dataset.data.ssubtotal = ototalprice;
				formVue.dataset.data.stax =ototaltax;
				formVue.dataset.data.stotal = parseFloat(ototalprice+ototaltax).toFixed(0);
                formVue.dataset.data.final_pmt = parseFloat(Number(totalprice)+Number(totaltax)).toFixed(0);
		}
		else if(filedCode == "b_rate" ){
			let bodytotal= 0;
				 let bodytaxrate= 0;
				 let oSubtotal= 0;
				 let totalprice = 0;
				 let totaltax = 0;
				 let orate = 0;
				 let ototalprice =0;
				 let ototaltax =0;
				 for(i=0;i<=formVue.dataset.subData[bodyId1].length-1;i++) {

					if( formVue.dataset.subData[bodyId1][i].data.product_code ){
                        formVue.dataset.subData[bodyId1][i].data.body_subtotal=parseFloat((formVue.dataset.subData[bodyId1][i].data.o_body_price * formVue.dataset.subData[bodyId1][i].data.body_num * formVue.dataset.subData[bodyId1][i].data.discount)/100).toFixed(2);
						if(formVue.dataset.subData[bodyId1][i].data.b_tax == "稅外加"){

							bodytotal = parseFloat(formVue.dataset.subData[bodyId1][i].data.body_subtotal,10).toFixed(2);
							bodytotal = parseFloat(bodytotal);
							bodytaxrate = formVue.dataset.subData[bodyId1][i].data.b_taxrate;
							bodytax = bodytotal *bodytaxrate;
							bodycurrency = formVue.dataset.data.rate;
							totalprice = totalprice+bodytotal;
							totaltax = totaltax+bodytax;
							ototalprice = ototalprice + (bodytotal*(bodycurrency))
							ototaltax = ototaltax + (bodytax*(bodycurrency))
						}
						else if(formVue.dataset.subData[bodyId1][i].data.b_tax == "稅內含"){

							bodytaxrate = formVue.dataset.subData[bodyId1][i].data.b_taxrate;
							bodytotal = parseFloat(formVue.dataset.subData[bodyId1][i].data.body_subtotal).toFixed(2);
							bodytotal = parseFloat(bodytotal);
							bodytotal2 = parseFloat(bodytotal/(1+bodytaxrate)).toFixed(2);
							bodytotal2 = parseFloat(bodytotal2);
							bodytotal2 =Math.round(bodytotal2);
							bodytax= parseFloat(bodytotal-(bodytotal/(1+bodytaxrate))).toFixed(2);
							bodytax= parseFloat(bodytax);
							bodytax= Math.round(bodytax);
							bodycurrency = formVue.dataset.data.rate;

							totalprice = totalprice + bodytotal2;
							totaltax = totaltax + bodytax;
							ototalprice = ototalprice + (bodytotal2*bodycurrency)
							ototaltax = ototaltax + (bodytax*bodycurrency)
						}

						 else {

							bodytotal = Number(formVue.dataset.subData[bodyId1][i].data.body_subtotal);
							bodytax = 0;
							bodycurrency = formVue.dataset.data.rate;
							totalprice = totalprice + bodytotal;
							totaltax = totaltax + bodytax;
							ototalprice = ototalprice + (bodytotal*bodycurrency)
							ototaltax = ototaltax + (bodytax*bodycurrency)


						}


					}

				}

				gettotal=parseFloat(totalprice).toFixed(2);
				formVue.dataset.data.osubtotal = gettotal;
                formVue.dataset.data.otax = totaltax;
                formVue.dataset.data.ototal =parseFloat(totalprice+totaltax).toFixed(0);
				formVue.dataset.data.ssubtotal = ototalprice;
				formVue.dataset.data.stax =ototaltax;
				formVue.dataset.data.stotal = parseFloat(ototalprice+ototaltax).toFixed(0);
                formVue.dataset.data.final_pmt = parseFloat(Number(totalprice)+Number(totaltax)).toFixed(0);
		}

		}else if( pageData.page.page_id == kegPageId ){
			if( filedCode == "body_num" ){
				let parentData = that.parentVue().dataset.subData[bodyId1];
				if( parseFloat(row.data.body_num) > parseFloat(row.data.remaining_num) ){
					alert("桶號庫存不足");
				    row.data.body_num = row.data.remaining_num;
				}
//				let selectesRow = parentData.find(element => element.selected );
//				let numTotal = subDataArray.reduce(function(prev, kegd) {
//					if(!kegd.data.body_num){
//						kegd.data.body_num = 0;
//					}
//				  return parseFloat(prev) + parseFloat(kegd.data.body_num);
//				}, 0);
//				selectesRow.data.body_num = parseFloat(numTotal/selectesRow.data.body_rate).toFixed(2);
			}
		}

        else if( pageData.page.page_id == littlePageId ){
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
            // console.log("321");
            let currentDataset1 = parentDataset.subData[littlePageIdBodyFormID1]
            let schema1 = that.config.schema[littlePageIdBodyFormID1]
            let firstRowEmptyFlag1 = false
            currentDataset1.forEach((row, rowIndex) => {
                if (rowIndex == 0 && that.checkRowIsEmpty(row, schema1)) return firstRowEmptyFlag1 = true // Check first row is empty and dont remove it
                if (that.checkRowIsEmpty(row, schema1)) that.deleteRow(parentDataset,littlePageIdBodyFormID1,rowIndex)
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
		//console.log("yo");
		const filedCode = field.field_code;
		if( pageData.page.page_id == mainPageId ){
			if( filedCode == 'source_client_order_code' ){
				row.data.source_client_order_code.data.client_code = parentDataset.data.client_code;
                let subData = that.dataset.subData[bodyId1];
                clearEmptyRowBylittlePage(subData,littlePageId,littlePageIdBodyFormID,'source_client_order_code',"order_no")
			}else if( filedCode == 'source_keg' ){
				let productData = {
					product_code:row.data.product_code,
				};
			   sendAPIRequest(getURL(`api/inject/changeProductCode`),"post",productData).then(async result => {
					kegClickStatus = true;
				   if( result['product_code'] != "" ){
					   if( result['product_kind'] != "費用" ){
						   row.data.source_keg.data.ship_code = parentDataset.data.ship_code;
						   row.data.source_keg.data.ship_no = row.data.id;
						   row.data.source_keg.data.product_code = row.data.product_code;
						   row.data.source_keg.data.product_name = row.data.product_name;
						   row.data.source_keg.data.depot_code = row.data.body_depot_code;
						   row.data.source_keg.data.depot_name = row.data.body_depot_name;
					   }else{
						   alert("費用無法選取桶號，請確認");
					   }
				   }
				   kegClickStatus = false;
				});
			}else if( filedCode == 'clear' ){
				if( !row.data.keg ){
					that.$set(row.override.fields, 'clear' ,{ field_readonly : true });
				   alert("沒有桶數則無法出清");
				}
			}

		}else if( filedCode == 'b_tax' ){
			//console.log('hi');
		}
		else if( pageData.page.page_id == kegPageId ){

		}

	})
</script>

@endsection
