
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
	// _.throttle(func, [wait=0], [options={}])
	const mainPageId = 6259;
	const bodyId1 = 6235;
	let isClick = true;
	function updatePamount(that,formId){
		let subDataArray = that.dataset.subData[formId];
		let serachData = {
			docu_number:that.dataset.data.docu_number,
		};
		let url = `api/inject/checkPayment`;
		sendAPIRequest(getURL(url),"post",serachData).then(result => {
			let resLen = result.length;
			if (resLen != 0) {
				for1:
				for(let val of result ){
					for2:
					for( let index in subDataArray ){
						if( (subDataArray[index].data.id == val.RE203_bodyno) ){
							that.dataset.subData[formId][index].override.preventDelete  = true;
							that.$set(subDataArray[index].override.fields, 'contract_id' ,{ field_readonly : true });
							that.$set(subDataArray[index].override.fields, 'item_id' ,{ field_readonly : true });
							that.$set(subDataArray[index].override.fields, 'rent_rout' ,{ field_readonly : true });
							that.$set(subDataArray[index].override.fields, 'tax' ,{ field_readonly : true });
							that.$set(subDataArray[index].override.fields, 'taxrate' ,{ field_readonly : true });
							that.$set(subDataArray[index].override.fields, 'remarks' ,{ field_readonly : true });
							that.$set(subDataArray[index].override.fields, 'rdate' ,{ field_readonly : true });
						}
					}
				}
			}
		})
	}
	function countPamount( tax,row ){
		if( tax == "稅內含" ){
			row.data.rent_rin = parseFloat(row.data.rent_rout);
			row.data.rent_rout = (row.data.rent_rout/(parseFloat(1)+parseFloat(row.data.taxrate))).toFixed(0);//原幣小記
			row.data.rtax = row.data.rent_rin - row.data.rent_rout;
		}else{
			if( row.data.taxrate && row.data.taxrate ){
				row.data.rtax = parseFloat(row.data.rent_rout) * parseFloat(row.data.taxrate);
				row.data.rent_rin = parseFloat(row.data.rent_rout) + parseFloat(row.data.rtax);
			}
		}
	}
    window.injects.injectOnInit.push((that, pageData) => {
//            that.dataset.override.fields.aaa = { field_readonly: false}
    })
    window.injects.injectOnAdd.push((that, pageData) => {
		that.dataset.data.undertaker = '{{session("username")}}';
		that.dataset.data.undertakername = '{{session("user_name")}}';
		that.dataset.data.undertakerday = getTodayDate();
        for(let index in that.dataset.subData[bodyId1]){
            that.dataset.subData[bodyId1][index].data.rdate = getTodayDate();
        }
	})
    window.injects.injectOnView.push((that, pageData, id) => {})
    window.injects.injectOnEdit.push((that, pageData, id) => {
		updatePamount(that,bodyId1);
	})
    window.injects.injectOnCopy.push((that, pageData, id) => {})

    window.injects.injectOnReferenceWrite.push((that, pageData, fromField, data, fields, dataset) => {
		if( pageData.page.page_id == mainPageId){
			switch (fromField.field_code) {

				case 'tax':
					if( isClick ){
						/**避免重複執行 */
						isClick = false;
						setTimeout(function(){
							isClick = true;
						},1000);
						if( dataset.data.rent_rout ){
							if( data.tax_name == "稅內含" ){

								dataset.data.rent_rin = parseFloat(dataset.data.rent_rout);
								dataset.data.rent_rout = (dataset.data.rent_rout/(parseFloat(1)+parseFloat(data.tax_taxrate))).toFixed(0);//原幣小記
								dataset.data.rtax = parseFloat(dataset.data.rent_rin - dataset.data.rent_rout).toFixed(2);
							}else{
								dataset.data.rtax = parseFloat(parseFloat(dataset.data.rent_rout) * parseFloat(data.tax_taxrate)).toFixed(2);
								dataset.data.rent_rin = parseFloat(dataset.data.rent_rout) + parseFloat(dataset.data.rtax);
							}
						}
					}
					break;
				default:
			}
		}
	})

    window.injects.injectOnRowAdd.push((that, pageData, targetSubDataArray, dataset) => {})
    window.injects.injectOnRowDelete.push((that, pageData, parentDataset, formID, rowIndex) => {})

    window.injects.injectOnHeadInput.push((that, pageData, field, dataset) => {})
    window.injects.injectOnHeadChange.push((that, pageData, field, dataset) => {})
    window.injects.injectOnHeadClick.push((that, pageData, field, dataset) => {})

    window.injects.injectOnBodyInput.push((that, pageData, parentDataset, row, field, formID, subDataArray) => {
    })
    window.injects.injectOnBodyChange.push((that, pageData, parentDataset, row, field, formID, subDataArray) => {
		const filedCode = field.field_code;
		if( pageData.page.page_id == mainPageId){
			switch (filedCode) {

				case 'rent_rout':
					if( row.data.tax == "稅內含" ){
						row.data.rent_rin = parseFloat(row.data.rent_rout);
						row.data.rent_rout = (row.data.rent_rout/(parseFloat(1)+parseFloat(row.data.taxrate))).toFixed(0);//原幣小記
						row.data.rtax = row.data.rent_rin - row.data.rent_rout;
					}else{
						if( row.data.taxrate && row.data.taxrate ){
							row.data.rtax = parseFloat(row.data.rent_rout) * parseFloat(row.data.taxrate);
							row.data.rent_rin = parseFloat(row.data.rent_rout) + parseFloat(row.data.rtax);
						}
					}

					break;
				case 'taxrate':
					if( row.data.rent_rout ){
						row.data.rtax = parseFloat(row.data.rent_rout) * parseFloat(row.data.taxrate);
						row.data.rent_rin = parseFloat(row.data.rent_rout) + parseFloat(row.data.rtax);
					}
					break;
				default:
			}
		}
    })
    window.injects.injectOnBodyClick.push((that, pageData, parentDataset, row, field, formID, subDataArray) => {
		if( !row.data.rdate){
		   row.data.rdate = getTodayDate();
		}

    })
</script>

@endsection
