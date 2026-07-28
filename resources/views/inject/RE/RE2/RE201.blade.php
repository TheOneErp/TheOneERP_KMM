
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
//	const mainPageId = 54;
	const bodyId1 = 47;
	const bodyId2 = 48;
	const bodyId3 = 51;
	const bodyId4 = 52;

    //View表
    function houseview(that,house_id,tables,bodyid){	
        
    }
    
    window.injects.injectOnInit.push((that, pageData) => {
//            that.dataset.override.fields.aaa = { field_readonly: false}
    })
    window.injects.injectOnAdd.push((that, pageData) => {
//		that.dataset.data.con_id = '{{session("username")}}';
//		that.dataset.data.con_name = '{{session("user_name")}}';
		that.dataset.data.creat_date = getTodayDate();
	})
    window.injects.injectOnView.push((that, pageData, id) => {
        houseview(that,that.dataset.data.house_id,'re207',bodyId2);
        houseview(that,that.dataset.data.house_id,'re208',bodyId3);
        houseview(that,that.dataset.data.house_id,'re209',bodyId4);
    })
    window.injects.injectOnEdit.push((that, pageData, id) => {
        houseview(that,that.dataset.data.house_id,'re207',bodyId2);
        houseview(that,that.dataset.data.house_id,'re208',bodyId3);
        houseview(that,that.dataset.data.house_id,'re209',bodyId4);
    })
    window.injects.injectOnCopy.push((that, pageData, id) => {})

    window.injects.injectOnReferenceWrite.push((that, pageData, fromField, data, fields, dataset) => {})

    window.injects.injectOnRowAdd.push((that, pageData, targetSubDataArray, dataset) => {})
    window.injects.injectOnRowDelete.push((that, pageData, parentDataset, formID, rowIndex) => {})

    window.injects.injectOnHeadInput.push((that, pageData, field, dataset) => {})
    window.injects.injectOnHeadChange.push((that, pageData, field, dataset) => {})
    window.injects.injectOnHeadClick.push((that, pageData, field, dataset) => {})

    window.injects.injectOnBodyInput.push((that, pageData, parentDataset, row, field, formID, subDataArray) => {})
    window.injects.injectOnBodyChange.push((that, pageData, parentDataset, row, field, formID, subDataArray) => {})
    window.injects.injectOnBodyClick.push((that, pageData, parentDataset, row, field, formID, subDataArray) => {})
</script>

@endsection
