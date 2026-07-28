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
	const bodyId1 = 57;
	const subBodyId1 = 58;
    const mainPageId = 61;
	const littlePageId = 1067;
	const littlePageIdBodyFormID = 1066;
    const componentId = 58;
	const batchBodyFormID = 2074;
	const batchPageId = 2073;
	
    let viewstatus = false;
    let useform = '';
    
    
    window.injects.injectOnInit.push((that, pageData) => {
	})
    window.injects.injectOnAdd.push((that, pageData) => {
		that.dataset.data.undertaker = '{{session("username")}}';
		that.dataset.data.undertakername = '{{session("user_name")}}';
        that.dataset.data.advanceday = getTodayDate();
        that.dataset.data.undertakerday = getTodayDate();
        
        if(localStorage.hasOwnProperty('finsheddata') == true){
            let orderno = localStorage.getItem("finsheddata")
            let allData = {
                orderno : orderno,
            };
            sendAPIRequest(getURL(`api/inject/addfinished`),"post",allData).then(result => {
                if(result['status'] == 1){
                    formVue.dataset.data.station_code = result['machiningdata']['station_code']
                    formVue.dataset.data.station_name = result['machiningdata']['station_name']
                    formVue.dataset.data.remarks = getTodayDate() + '看板功能轉出。'
                    for( let index in result['machiningdata']){
                        SubDataLas = formVue.dataset.subData[bodyId1].length - 1 ; //暫時寫在最後一行
                        for( let key in result['machiningdata'][index]) {
                            if(key == 'station_code'){
                                formVue.dataset.data.station_code = result['machiningdata'][index][key];
                            }else if(key == 'station_name'){
                                formVue.dataset.data.station_name = result['machiningdata'][index][key];
                            }else if(key == 'body_num'){
                                formVue.dataset.subData[bodyId1][SubDataLas]['data'][key] =  result['machiningdata'][index]['body_num'] - result['machiningdata'][index]['body_quantity'];
                            }else{
                                formVue.dataset.subData[bodyId1][SubDataLas]['data'][key] =  result['machiningdata'][index][key];
                            }
                       }
                    }
                    let currentRow2 = "";
                    let schema2 = formVue.config.schema[componentId];
                    for( let index2 in result['machiningcomp']){
                        comDataLas = formVue.dataset.subData[bodyId1][index]['subData'][componentId].length - 1; //暫時寫在最後一行
                        currentRow2 = that.dataset.subData[bodyId1][index]['subData'][componentId][comDataLas]; 
//                        formVue.addEmptyRow(formVue.dataset.subData[bodyId1][index],componentId);    
                        if( !formVue.checkRowIsEmpty(currentRow2, schema2) ){
                            formVue.addEmptyRow(that.dataset.subData[bodyId1][index],componentId);
                            comDataLas = formVue.dataset.subData[bodyId1][index]['subData'][componentId].length - 1 ; //暫時寫在最後一行
                        }
                        for( let key in result['machiningcomp'][index2]) {
                            formVue.dataset.subData[bodyId1][index]['subData'][componentId][comDataLas]['data'][key] = result['machiningcomp'][index2][key];
                        }
                    }
                }else{
                    alert('無法轉出完工單');
                    that.closeForm();
                }
            })
            localStorage.removeItem("finsheddata");
        }
        
	})
    window.injects.injectOnView.push((that, pageData, id) => {
        viewstatus = true;
    })
    window.injects.injectOnEdit.push((that, pageData, id) => {
        that.dataset.status = 'update'
        viewstatus = false;
		let subData = formVue.dataset.subData[bodyId1];
		for (let element of subData) {
			if(element.schema){
				if( element.data.batch == 'Y' ){
				    for (let key in element.schema.fields) {
						element.override.fields[key] = {
							field_readonly: true
						};
					}
					element.override.preventDelete = true;
					for (let subElement of element.subData[subBodyId1]) {
						for (let subkey in subElement.schema.fields) {
							subElement.override.fields[subkey] = {
								field_readonly: true
							};
						}
						subElement.override.preventDelete = true;
					}
				}
                if( element.data.machining_code != null ){
                    element.override.fields['product_code'] = {
                        field_readonly: true
                    };
                    formVue.$set(formVue.dataset.override.fields, 'station_code' ,{ field_readonly : true });
                }
			}
		}
    })
    window.injects.injectOnCopy.push((that, pageData, id) => {
        viewstatus = true;
        let subData = formVue.dataset.subData[bodyId1];
		for (let element of subData) {
			if(element.schema){
				if( element.data.batch == 'Y' ){
				    for (let key in element.schema.fields) {
						element.override.fields[key] = {
							field_readonly: true
						};
					}
					element.override.preventDelete = true;
					for (let subElement of element.subData[subBodyId1]) {
						for (let subkey in subElement.schema.fields) {
							subElement.override.fields[subkey] = {
								field_readonly: true
							};
						}
						subElement.override.preventDelete = true;
					}
				}
                if( element.data.machining_code != null ){
                    element.override.fields['product_code'] = {
                        field_readonly: true
                    };
                    formVue.$set(formVue.dataset.override.fields, 'station_code' ,{ field_readonly : true });
                }
			}
		}
    })

    window.injects.injectOnReferenceWrite.push((that, pageData, fromField, data, fields, dataset) => {
		if( fromField.field_code == "product_code" ){//換算率
			dataset.data.unit_code = data.unit_code;
			dataset.data.body_rate = "1";
		}else if( fromField.field_code == "component_code" ){
			dataset.data.component_unit = data.component_unit;
			dataset.data.component_rate = "1";
		}else{
            
        }
	})

    window.injects.injectOnRowAdd.push((that, pageData, targetSubDataArray, dataset) => {})
    window.injects.injectOnRowDelete.push((that, pageData, parentDataset, formID, rowIndex) => {
        let subData = that.dataset.subData[bodyId1];
		if( pageData.page.page_id == mainPageId ){
			let num = 0;
            for (let key in subData) {
                if (subData[key].schema && key != rowIndex ) {
//                    console.log(element)
                    if( subData[key].data.machining_code != null ){
                        num++
                        break;
                    }
                }
            }
            
            if(num==0){
                formVue.$set(formVue.dataset.override.fields, 'station_code' ,{ field_readonly : false });
            }else{
                formVue.$set(formVue.dataset.override.fields, 'station_code' ,{ field_readonly : true });
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
        for(let key in formVue.dataset.subData[bodyId1]){
            if(formVue.dataset.subData[bodyId1][key].machining_code != null && formVue.dataset.subData[bodyId1][key].machining_code != ''){
                that.$set(that.dataset.override.fields, 'station_code' ,{ field_readonly : true });
            }
        }
	})

    window.injects.injectOnHeadInput.push((that, pageData, field, dataset) => {})
    window.injects.injectOnHeadChange.push((that, pageData, field, dataset) => {})
    window.injects.injectOnHeadClick.push((that, pageData, field, dataset) => {
        const filedCode = field.field_code;
		if( pageData.page.page_id == littlePageId ){
			if( filedCode == 'searchbtn' ){
                that.setFormOption(littlePageIdBodyFormID,'disableAutoAddOrRemoveRow',true);
				let serachData = {
					client_order_codeS:dataset.data.client_order_codeS,
					client_order_codeE:dataset.data.client_order_codeE,
					machining_codeS:dataset.data.machining_codeS,
					machining_codeE:dataset.data.machining_codeE,
					machining_finishedS:dataset.data.machining_finishedS,
					machining_finishedE:dataset.data.machining_finishedE,
					product_codeS:dataset.data.product_codeS,
					product_codeE:dataset.data.product_codeE,
					station_code:dataset.data.station_code
				};
				let url =`api/inject/getStation`;
				searchbtn(that,serachData,url,littlePageIdBodyFormID,true,readonlyBody=[],readonlyHead=[]);
			}
            if( filedCode == 'getbtn' ){	
                let SubDataLas = 0;
                let comDataLas = 0;
                let getBackStatus = false;
				let mainSubData = that.parentDataset;
                let searchArr = [];
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
                            if(key == 'body_num'){
                                formVue.dataset.subData[bodyId1][SubDataLas]['data']['body_num'] = (dataset.subData[littlePageIdBodyFormID][index]['data']['body_num']-dataset.subData[littlePageIdBodyFormID][index]['data']['body_quantity']).toFixed(2);
                            }else{
                                formVue.dataset.subData[bodyId1][SubDataLas]['data'][key] =  dataset.subData[littlePageIdBodyFormID][index]['data'][key];
                                if(key == "machining_code"){
                                    formVue.$set(formVue.dataset.subData[bodyId1][SubDataLas].override.fields, 'product_code' ,{ field_readonly : true });
                                }
                            }
                            getBackStatus = true;
                       }
                       
                        let componentdata = {
                            station_id:dataset.subData[littlePageIdBodyFormID][index].data.machining_no
                        };
                        searchArr[SubDataLas] = componentdata;
                    }
				   
			   }
                for( let index in searchArr ){
                    sendAPIRequest(getURL(`api/inject/getStationComponent`),"post",searchArr[index]).then(componentresult => {
                        let currentRow2 = "";
                        let schema2 = formVue.config.schema[componentId];
                        for( let index2 in componentresult){
                            comDataLas = formVue.dataset.subData[bodyId1][index]['subData'][componentId].length - 1; //暫時寫在最後一行
                            currentRow2 = that.parentVue().dataset.subData[bodyId1][index]['subData'][componentId][comDataLas]; //formVue.addEmptyRow(formVue.dataset.subData[bodyId1][index],componentId);
                            
                            if( !formVue.checkRowIsEmpty(currentRow2, schema2) ){
                                that.parentVue().addEmptyRow(that.parentVue().dataset.subData[bodyId1][index],componentId);
                                comDataLas = formVue.dataset.subData[bodyId1][index]['subData'][componentId].length - 1 ; //暫時寫在最後一行
                            }
                            for( let key in componentresult[index2]) {
                                formVue.dataset.subData[bodyId1][index]['subData'][componentId][comDataLas]['data'][key] = componentresult[index2][key];
                            }
                        }
                    });
                }
				if( getBackStatus ){
				   alert("取回完成");
                    formVue.$set(formVue.dataset.override.fields, 'station_code' ,{ field_readonly : true });
				 }else{
					 alert("無須取回項目");
				 }
			}
		}
    })

    window.injects.injectOnBodyInput.push((that, pageData, parentDataset, row, field, formID, subDataArray) => {})
    window.injects.injectOnBodyChange.push((that, pageData, parentDataset, row, field, formID, subDataArray) => {
        const filedCode = field.field_code;
        if(row.data.component_code == null){
            row.data.component_unit = null;
        }
//		if( filedCode == "batch_code" ){
//			let index = formVue.dataset.subData[bodyId1].findIndex(x => x.selected);
//			formVue.dataset.subData[bodyId1][index].data.batch = "Y";
//		}
        
        if(filedCode == "choose"){
            let currentDataset = parentDataset.subData[littlePageIdBodyFormID]
            let schema = that.config.schema[littlePageIdBodyFormID]
            let firstRowEmptyFlag = false
            currentDataset.forEach((row, rowIndex) => {
                if (rowIndex == 0 && that.checkRowIsEmpty(row, schema)) return firstRowEmptyFlag = true // Check first row is empty and dont remove it
                if (that.checkRowIsEmpty(row, schema)) that.deleteRow(parentDataset,littlePageIdBodyFormID,rowIndex)
            });
        }
        
        if( filedCode == "product_code" && pageData.page.page_id == mainPageId ){
            let readstatus = false
            let count = 0
        }
    })
    window.injects.injectOnBodyClick.push((that, pageData, parentDataset, row, field, formID, subDataArray) => {
        const filedCode = field.field_code;
		if( pageData.page.page_id == mainPageId ){
			if( filedCode == 'source_machining_code' ){
				row.data.source_machining_code.data.station_code = parentDataset.data.station_code;
                let subData = that.dataset.subData[bodyId1];
                clearEmptyRowBylittlePage(subData,littlePageId,littlePageIdBodyFormID,'source_machining_code',"machining_no")
			}
		}

		if( filedCode == "product_code" && row.data.product_code != null ){

		}else if( filedCode == 'source_batch_code' ){
			row.data.source_batch_code.data.finished_code = parentDataset.data.finished_code;
			row.data.source_batch_code.data.product_code = row.data.product_code;
			row.data.source_batch_code.data.finished_no = row.data.id;
		}

        if( row.override.preventDelete == true && viewstatus == false &&  filedCode != 'source_batch_code' ) {
			if( row.form_id == bodyId1){
			   if( row.data.batch == "Y" ){
				   alert("該列已有批號管理，不可修改");
				}else{
//				   alert("該桶號已被"+useform+"引用，不可修改");
				}
			}else if( row.form_id == subBodyId1 ){
				if( parentDataset.data.batch == "Y" ){
				   alert("該列已有批號管理，不可修改");
				}else{
//				   alert("該桶號已被"+useform+"引用，不可修改");
				}
			}
			
        }
    })

</script>

@endsection