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
//	const bodyId1 = 45;

    window.injects.injectOnInit.push((that, pageData) => {
		
	})
    window.injects.injectOnAdd.push((that, pageData) => {
        that.dataset.status = 'update'
		let product = {
			product_code : that.dataset.data.product_code,
		};
		sendAPIRequest(getURL(`api/inject/getsafenum`),"post",product).then(result => {
           // console.log(result);
		});
	})
    window.injects.injectOnView.push((that, pageData, id) => {
        let product = {
			product_code : that.dataset.data.product_code,
		};
		sendAPIRequest(getURL(`api/inject/getsafenum`),"post",product).then(result => {
     
            formVue.dataset.data.safe1=+result[0].year+"年"+result[0].month+"月出貨量: "+result[0].sum+'\n'
            console.log(formVue.dataset.data.safe1);
            for(i=1;i<=result.length-1;i++){
            formVue.dataset.data.safe1=formVue.dataset.data.safe1+result[i].year+"年"+result[i].month+"月出貨量: "+result[i].sum+'\n';
            }
            ;
		});
    })
    window.injects.injectOnEdit.push((that, pageData, id) => {
        that.dataset.status = 'update'
		let product = {
			product_code : that.dataset.data.product_code,
		};
		sendAPIRequest(getURL(`api/inject/getsafenum`),"post",product).then(result => {
     
            formVue.dataset.data.safe1=+result[0].year+"年"+result[0].month+"月出貨量: "+result[0].sum+'\n'
            console.log(formVue.dataset.data.safe1);
            for(i=1;i<=result.length-1;i++){
            formVue.dataset.data.safe1=formVue.dataset.data.safe1+result[i].year+"年"+result[i].month+"月出貨量: "+result[i].sum+'\n';
            };
            
		});
    })
    window.injects.injectOnCopy.push((that, pageData, id) => {
  
    })

    window.injects.injectOnReferenceWrite.push((that, pageData, fromField, data, fields, dataset) => {})

    window.injects.injectOnRowAdd.push((that, pageData, targetSubDataArray, dataset) => {})
    window.injects.injectOnRowDelete.push((that, pageData, parentDataset, formID, rowIndex) => {})

    window.injects.injectOnHeadInput.push((that, pageData, field, dataset) => {})
    window.injects.injectOnHeadChange.push((that, pageData, field, dataset) => {})
    window.injects.injectOnHeadClick.push((that, pageData, field, dataset) => {})

    window.injects.injectOnBodyInput.push((that, pageData, parentDataset, row, field, formID, subDataArray) => {})
    window.injects.injectOnBodyChange.push((that, pageData, parentDataset, row, field, formID, subDataArray) => {})
    window.injects.injectOnBodyClick.push((that, pageData, parentDataset, row, field, formID, subDataArray) => {

    })
 

</script>

@endsection