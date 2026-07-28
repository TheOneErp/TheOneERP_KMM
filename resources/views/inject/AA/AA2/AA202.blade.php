
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
const bodyId1 = 31;
window.injects.injectOnInit.push((that, pageData) => {})
window.injects.injectOnAdd.push((that, pageData) => {
    let subArr = that.dataset.subData[bodyId1];
    // clearEmptyRow(that.dataset,subArr,bodyId1,"body_unit_code");
    if('{{session("username")}}' == "phaith" || '{{session("username")}}' == "yltest" || '{{session("username")}}' == "ocean"){
        that.dataset.schema.fields.purchase_price.field_show_on_form = false ;
        that.dataset.subData[bodyId1][0].schema.fields.body_purchase_price.field_show_on_form = false ;
    }
})
window.injects.injectOnView.push((that, pageData, id) => {
    let subArr = that.dataset.subData[bodyId1];
    if('{{session("username")}}' == "phaith" || '{{session("username")}}' == "yltest" || '{{session("username")}}' == "ocean"){
        that.dataset.schema.fields.purchase_price.field_show_on_form = false ;
        that.dataset.subData[bodyId1][0].schema.fields.body_purchase_price.field_show_on_form = false ;
    }
    // clearEmptyRow(that.dataset,subArr,bodyId1,"body_unit_code");
})
window.injects.injectOnEdit.push((that, pageData, id) => {
    let subArr = that.dataset.subData[bodyId1];
    if('{{session("username")}}' == "phaith" || '{{session("username")}}' == "yltest" || '{{session("username")}}' == "ocean"){
        that.dataset.schema.fields.purchase_price.field_show_on_form = false ;
        that.dataset.subData[bodyId1][0].schema.fields.body_purchase_price.field_show_on_form = false ;
    }
    // clearEmptyRow(that.dataset,subArr,bodyId1,"body_unit_code");
})
window.injects.injectOnCopy.push((that, pageData, id) => {
    let subArr = that.dataset.subData[bodyId1];
    if('{{session("username")}}' == "phaith" || '{{session("username")}}' == "yltest" || '{{session("username")}}' == "ocean"){
        that.dataset.schema.fields.purchase_price.field_show_on_form = false ;
        that.dataset.subData[bodyId1][0].schema.fields.body_purchase_price.field_show_on_form = false ;
    }
    // clearEmptyRow(that.dataset,subArr,bodyId1,"body_unit_code");
})
//資料引入
window.injects.injectOnReferenceWrite.push((that, pageData, fromField, data, fields, dataset) => {
	let form_id = dataset.form_id;
//	console.log(dataset);
	if( fromField.field_code == "body_unit_code"){
		if( data.unit_code == that.dataset.data.unit_code){
			const unitTrans = "{{ $commonTranslations['AA202.message.unit'] }}";
			const standardUnitTrans = "{{ $commonTranslations['AA202.message.standard_unit'] }}";
			let warning = "{{ $commonTranslations['AA202.message.warning1'] }}";
			warning = warning.replace(/:unit|:standard_unit/g, function(Str) {
				var replaceStr = {
					':unit': unitTrans,
					':standard_unit': standardUnitTrans
				};
				return replaceStr[Str];
			});
			let index = formVue.dataset.subData[bodyId1].findIndex(x => x.selected);

		   	alert(warning);
			that.$nextTick().then(() => {
				formVue.dataset.subData[bodyId1][index]['data']['body_unit_code'] = null;
				formVue.dataset.subData[bodyId1][index]['data']['body_unit_name'] = null;
			});

		}else{
			let subData = that.dataset.subData[bodyId1];
			const unitTrans = "{{ $commonTranslations['AA202.message.unit'] }}";
			let warning = "{{ $commonTranslations['AA202.message.warning2'] }}";
			warning = warning.replace(':item',unitTrans);
			for (let element of subData) {
				if( element['data']['body_unit_code'] == data.unit_code ){
					alert(warning);
					let index = formVue.dataset.subData[bodyId1].findIndex(x => x.selected);
					that.$nextTick().then(() => {
						formVue.dataset.subData[bodyId1][index]['data']['body_unit_code'] = null;
						formVue.dataset.subData[bodyId1][index]['data']['body_unit_name'] = null;
					});
                    break;
				}
			}
		}
	}else if( fromField.field_code == "unit_code" ){
		let subData = that.dataset.subData[31];
		const unitTrans = "{{ $commonTranslations['AA202.message.unit'] }}";
		const standardUnitTrans = "{{ $commonTranslations['AA202.message.standard_unit'] }}";
		let warning = "{{ $commonTranslations['AA202.message.warning1'] }}";
		warning = warning.replace(/:unit|:standard_unit/g, function(Str) {
			var replaceStr = {
				':unit': standardUnitTrans,
				':standard_unit': unitTrans
			};
			return replaceStr[Str];
		});
		for (let element of subData) {
			if( element.data.body_unit_code == data.unit_code ){
				alert(warning);
				that.$nextTick().then(() => {
					formVue.dataset.data.unit_code = null;
					formVue.dataset.data.unit_name = null;
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
window.injects.injectOnBodyChange.push((that, pageData, parentDataset, row, field, formID, subDataArray) => {})
window.injects.injectOnBodyClick.push((that, pageData, parentDataset, row, field, formID, subDataArray) => {})
</script>

@endsection
