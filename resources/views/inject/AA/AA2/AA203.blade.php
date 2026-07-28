
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
let bodyId1 = 33;
window.injects.injectOnInit.push((that, pageData) => {})
window.injects.injectOnAdd.push((that, pageData) => {})
window.injects.injectOnView.push((that, pageData, id) => {})
window.injects.injectOnEdit.push((that, pageData, id) => {})
window.injects.injectOnCopy.push((that, pageData, id) => {})
// window.injects.injectOnReferenceWrite.push((that, pageData, fromField, referenceData, fields, dataset) => {
window.injects.injectOnReferenceWrite.push((that, pageData, fromField, data, fields, dataset) => {
	let form_id = dataset.form_id; 
		
	if( fromField.field_code == "component_code"){
		if( data.product_code == that.dataset.data.product_code ){
		   	alert("{{ $commonTranslations['AA203.message.warning1'] }}");
			let index = formVue.dataset.subData[bodyId1].findIndex(x => x.selected);
			that.$nextTick().then(() => {
				formVue.dataset.subData[bodyId1][index]['data']['component_code'] = null;
				formVue.dataset.subData[bodyId1][index]['data']['component_name'] = null;
			});
		}else{
			let subData = that.dataset.subData[bodyId1];
			const componentTrans = "{{ $commonTranslations['AA203.message.component'] }}";
			let warning = "{{ $commonTranslations['AA202.message.warning2'] }}";
			warning = warning.replace(':item',componentTrans);
			let duplicate = false;
			for (let element of subData) {
				if( element['data']['component_code'] == data.product_code ){
					alert(warning);
					let index = formVue.dataset.subData[bodyId1].findIndex(x => x.selected);
					that.$nextTick().then(() => {
						formVue.dataset.subData[bodyId1][index]['data']['component_code'] = null;
						formVue.dataset.subData[bodyId1][index]['data']['component_name'] = null;
					});
					break;			
				}else{
					element.data.body_rate = "1";
				}
			}
		}
	}else if( fromField.field_code == "product_code" ){/*** */
		let subData = that.dataset.subData[33];
		for (let element of subData) {
			if( element.data.component_code == data.product_code ){
				alert("此產品已做為子件輸入");
				that.$nextTick().then(() => {
					formVue.dataset.data.product_code = null;
					formVue.dataset.data.product_name = null;
				});
				break;
			}
		}
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
	if( filedCode == "component_code"){
		if( row.data.component_code == parentDataset.data.product_code ){
			alert("{{ $commonTranslations['AA203.message.warning1'] }}");
			row.data.component_code = "";
			row.data.component_name = "";
			row.data.unit_code = "";
			row.data.unit_name = "";
			row.data.body_rate = "";
			row.data.purchase_code = "";
			row.data.purchase_name = "";
			row.data.body_depot_code = "";
			row.data.body_depot_name = "";
			row.data.body_remarks = "";
			
		}else{
			row.data.body_rate = "1";
			console.log(subDataArray);
			for (let element of subDataArray) {
				if( !element.selected  && row.data.component_code == element.data.component_code ){
					const componentTrans = "{{ $commonTranslations['AA203.message.component'] }}";
					let warning = "{{ $commonTranslations['AA202.message.warning2'] }}";
					warning = warning.replace(':item',componentTrans);
					alert(warning);
					row.data.component_code = "";
					row.data.component_name = "";
					row.data.unit_code = "";
					row.data.unit_name = "";
					row.data.body_rate = "";
					row.data.purchase_code = "";
					row.data.purchase_name = "";
					row.data.body_depot_code = "";
					row.data.body_depot_name = "";
					row.data.body_remarks = "";
				}else{
					let productData = {
						product_code:row.data.component_code
					};
					sendAPIRequest(getURL(`api/inject/changeProductCode`),"post",productData).then(result => {
					   if( result['product_code'] != "" ){
						   row.data.component_code = result['product_code'];
						   row.data.component_name = result['product_name'];
						   row.data.unit_code = result['unit_code'];
						   row.data.unit_name = result['unit_name'];
						   row.data.purchase_code = result['vendor_code'];
						   row.data.purchase_name = result['vendor_name'];
						   row.data.body_depot_code = result['depot_code'];
						   row.data.body_depot_name = result['depot_name'];
					   }
					});
				}

			}
			
		}
	}
})
window.injects.injectOnBodyClick.push((that, pageData, parentDataset, row, field, formID, subDataArray) => {})


</script>

@endsection
