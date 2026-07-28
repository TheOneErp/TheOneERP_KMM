
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
	// const mainPageId = 2232;

    window.injects.injectOnInit.push((that, pageData) => {})
    window.injects.injectOnAdd.push((that, pageData) => {})
    window.injects.injectOnView.push((that, pageData, id) => {})
    window.injects.injectOnEdit.push((that, pageData, id) => {})
    window.injects.injectOnCopy.push((that, pageData, id) => {})

    window.injects.injectOnReferenceWrite.push((that, pageData, fromField, data, fields, dataset) => {})

    window.injects.injectOnRowAdd.push((that, pageData, targetSubDataArray, dataset) => {})
    window.injects.injectOnRowDelete.push((that, pageData, parentDataset, formID, rowIndex) => {})

    window.injects.injectOnHeadInput.push((that, pageData, field, dataset) => {})
    window.injects.injectOnHeadChange.push((that, pageData, field, dataset) => {})
  	window.injects.injectOnHeadClick.push((that, pageData, field, dataset) => {
        const filedCode = field.field_code;
		switch (filedCode) {
			case 'import':
				// clearTableRow( that,bodyId1 );
				// that.$nextTick().then(() => {
					let tmpId = formVue.dataset.tmpID;
					const import_id = formVue.dataset.schema.fields.upload_file.field_id;
					let formData = new FormData;
					formData.append('file',formVue.getFile(tmpId,import_id));
					formData.append('method','getSheetData');
					formData.append('parameters',JSON.stringify([1,[]]));
					formData.append('sele_form',"客戶資料");
					// formData.append('month',dataset.data.month);
					// let url = `api/system/file/excel`;
					let url = `api/inject/getImport`;
					sendAPIRequest(getURL(url),"post",formData).then(async result => {
						console.log(result);
						// let days = ['first','second','third','fourth','fifth','sixth','seventh','eighth','ninth','tenth','eleventh','twelfth','thirteenth','fourteenth','fifteenth','sixteenth','seventeenth','eighteenth','nineteenth','twentieth','twenty_first','twenty_second','twenty_third','twenty_fourth','twenty_fifth','twenty_sixth','twenty_seventh','twenty_eighth','twenty_ninth','thirteth','thirty_first'];
						// let subIndex = 0 ;
						if( result['status'] == 'success' ){
							// console.log(result);
							alert("匯入成功");
						}else{
							alert(result['message']);
						}
						
					});
				// });
				break;
			default:;
				break;
		}

      })

    window.injects.injectOnBodyInput.push((that, pageData, parentDataset, row, field, formID, subDataArray) => {})
    window.injects.injectOnBodyChange.push((that, pageData, parentDataset, row, field, formID, subDataArray) => {})
    window.injects.injectOnBodyClick.push((that, pageData, parentDataset, row, field, formID, subDataArray) => {})
</script>

@endsection
