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
	const bodyId1 = 45;
	const bodyId2 = 46;

    
    window.injects.injectOnInit.push((that, pageData) => {
		
	})
    window.injects.injectOnAdd.push((that, pageData) => {
		that.dataset.data.undertaker = '{{session("username")}}';
		that.dataset.data.undertakername = '{{session("user_name")}}';
        that.dataset.data.machining_finished = getTodayDate();
        that.dataset.data.undertakerday = getTodayDate();
	})
    window.injects.injectOnView.push((that, pageData, id) => {})
    window.injects.injectOnEdit.push((that, pageData, id) => {
        that.dataset.status = 'update'
		let subData = that.dataset.subData[bodyId1];
		for (let element of subData) {
			if(element.schema){
			   for(let key in element.schema.fields){
				   if( element.data.body_quantity != 0 && key != 'body_cancel' ){
                       element.override.fields[key]  = { field_readonly : true };
                       element.override.preventDelete  = true;
                       let comData = element.subData[46];
                        for(let comele of comData){
                            if(comele.schema){
                                for(let comkey in comele.schema.fields){
                                    comele.override.fields[comkey]  = { field_readonly : true };
                                    comele.override.preventDelete  = true;
                                }
                            }
                        }
                       clearEmptyRow(element,comData,bodyId2,"component_code");
                   }
					  
				}
                
			}
		}
        
        //表身no有被其他單引用廠商要鎖
        let cited = false
        subData.forEach((row, rowIndex) => {
            let codeData = {
                temp: 'machining_code',
                no: that.dataset.data.machining_code,
                tables:['BA201_41']
            };
            sendAPIRequest(getURL(`api/inject/cited`),"post",codeData).then(result => {
                if(result['status'] == 1){
                    that.$set(that.dataset.override.fields, 'station_code' ,{ field_readonly : true });
                }
            })
        });
        subData.forEach((row, rowIndex) => {
            let codeData = {
                temp: 'machining_no',
                no: row.data.id,
                tables:['DA202_57']
            };
            sendAPIRequest(getURL(`api/inject/cited`),"post",codeData).then(result => {
                if(result == 1){
                    that.$set(that.dataset.override.fields, 'station_code' ,{ field_readonly : true });
                }
            })
        });

	})
    window.injects.injectOnCopy.push((that, pageData, id) => {
        let subData = that.dataset.subData[bodyId1];
		for (let element of subData) {
			element.data.body_quantity = 0;
		}
    })

    window.injects.injectOnReferenceWrite.push((that, pageData, fromField, data, fields, dataset) => {
		if( fromField.field_code == "product_code" ){//換算率
			dataset.data.unit_code = data.unit_code;
			dataset.data.body_rate = "1";
		}else if( fromField.field_code == "component_code" ){
			dataset.data.component_unit = data.unit_code;
			dataset.data.component_rate = "1";
		}
	})

    window.injects.injectOnRowAdd.push((that, pageData, targetSubDataArray, dataset) => {})
    window.injects.injectOnRowDelete.push((that, pageData, parentDataset, formID, rowIndex) => {})

    window.injects.injectOnHeadInput.push((that, pageData, field, dataset) => {})
    window.injects.injectOnHeadChange.push((that, pageData, field, dataset) => {})
    window.injects.injectOnHeadClick.push((that, pageData, field, dataset) => {})

    window.injects.injectOnBodyInput.push((that, pageData, parentDataset, row, field, formID, subDataArray) => {})
    window.injects.injectOnBodyChange.push((that, pageData, parentDataset, row, field, formID, subDataArray) => {
		const filedCode = field.field_code;
		if( row.selected && row.data.body_quantity && row.data.body_quantity != 0 ){
            
        }else{
			if( filedCode == "product_code" ){
//				row.data.body_rate = "1";
//				let productData = {
//					product_code:row.data.product_code,
//					product_unit:row.data.unit_code
//				};
//			   sendAPIRequest(getURL(`api/inject/changeProductCode`),"post",productData).then(result => {
//				   if( result['product_code'] != "" ){
//					   row.data.product_name = result['product_name'];
//					   row.data.unit_name = result['unit_name'];
//				   }
//				});

			}else if( filedCode == "component_code" ){
				row.data.component_rate = "1";
				let productData = {
					product_code:row.data.component_code,
					product_unit:row.data.component_unit
				};
			   sendAPIRequest(getURL(`api/inject/changeProductCode`),"post",productData).then(result => {
				   if( result['product_code'] != "" ){
					   row.data.component_name = result['product_name'];
					   row.data.component_unitname = result['unit_name'];
				   }
				});
			}
		}
		
	})
    window.injects.injectOnBodyClick.push((that, pageData, parentDataset, row, field, formID, subDataArray) => {
        const filedCode = field.field_code;
        if (row.override.fields[filedCode] != undefined && row.override.fields[filedCode].field_readonly == true) {
            alert("已有對應完工紀錄，不可修改!");
            let subData = that.dataset.subData[bodyId1];
            for (let element of subData) {
                let comData = element.subData[46];
                clearEmptyRow(element, comData, bodyId2, "component_code");
            }
        } else {

        }
    })

</script>

@endsection