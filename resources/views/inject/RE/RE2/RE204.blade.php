
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
	const mainPageId = 6260;
	const formId = 6237;
	function updatePamount(that,formId){
		let subDataArray = that.dataset.subData[formId];
		let serachData = {
			charge_fdate:that.dataset.data.charge_fdate,
			charge_tdate:that.dataset.data.charge_tdate,
			contract_fno:that.dataset.data.contract_fno,
			contract_tno:that.dataset.data.contract_tno,
			house_fid:that.dataset.data.house_fid,
			house_tid:that.dataset.data.house_tid,
			con_id:that.dataset.data.con_id,
			type:"forCheck"
		};
		let url = `api/inject/getRentDetails`;
		sendAPIRequest(getURL(url),"post",serachData).then(result => {
			let resLen = result.length;
			if (resLen != 0) {
				for1:
				for(let val of result ){
					for2:
					for( let subVal of subDataArray ){
						if( (subVal.data.contract_id == val.contract_id) && (subVal.data.r_a_no == val.r_a_no) ){
						   subVal.data.pamount = val.pamount;
							break for2;
					   	}
					}
				}
			}
		})
	}
    window.injects.injectOnInit.push((that, pageData) => {
//            that.dataset.override.fields.aaa = { field_readonly: false}
    })
    window.injects.injectOnAdd.push((that, pageData) => {
		that.dataset.data.undertaker = '{{session("username")}}';
		that.dataset.data.undertakername = '{{session("user_name")}}';
		that.dataset.data.undertakerday = getTodayDate();
		// formVue.dataset.data.sign_in = '{{session("username")}}';
	})
    window.injects.injectOnView.push((that, pageData, id) => {
		updatePamount(that,formId);
	})
    window.injects.injectOnEdit.push((that, pageData, id) => {
		that.setFormOption(formId,'disableAutoAddOrRemoveRow',true);
		updatePamount(that,formId);
	})
    window.injects.injectOnCopy.push((that, pageData, id) => {
		that.setFormOption(formId,'disableAutoAddOrRemoveRow',true);
		updatePamount(that,formId);
		// formVue.dataset.data.sign_in = '{{session("username")}}';
	})

    window.injects.injectOnReferenceWrite.push((that, pageData, fromField, data, fields, dataset) => {})

    window.injects.injectOnRowAdd.push((that, pageData, targetSubDataArray, dataset) => {
		that.setFormOption(formId,'disableAutoAddOrRemoveRow',true);
	})
    window.injects.injectOnRowDelete.push((that, pageData, parentDataset, formID, rowIndex) => {})

    window.injects.injectOnHeadInput.push((that, pageData, field, dataset) => {})
    window.injects.injectOnHeadChange.push((that, pageData, field, dataset) => {})
    window.injects.injectOnHeadClick.push((that, pageData, field, dataset) => {
		if( pageData.page.page_id == mainPageId){
			const filedCode = field.field_code;
			switch (filedCode) {
				case 'filter':
					let serachData = {
						charge_fdate:dataset.data.charge_fdate,
						charge_tdate:dataset.data.charge_tdate,
						contract_fno:dataset.data.contract_fno,
						contract_tno:dataset.data.contract_tno,
						house_fid:dataset.data.house_fid,
						house_tid:dataset.data.house_tid,
						con_id:dataset.data.con_id,
					};
					let url = `api/inject/getRentDetails`;
					searchbtn2(that,serachData,url,formId,true);
					break;
				case 'delete':
					let subData = that.dataset.subData[formId];
					let index = subData.length - 1;
					while ( index >= 0 ) {
						if( subData[index].data.select == 1 ){
						   that.deleteRow(that.dataset,formId,index);
						}
						index--;
					}
					break;
				default:
			}
		}
	})

    window.injects.injectOnBodyInput.push((that, pageData, parentDataset, row, field, formID, subDataArray) => {})
    window.injects.injectOnBodyChange.push((that, pageData, parentDataset, row, field, formID, subDataArray) => {})
    window.injects.injectOnBodyClick.push((that, pageData, parentDataset, row, field, formID, subDataArray) => {})
</script>

@endsection
