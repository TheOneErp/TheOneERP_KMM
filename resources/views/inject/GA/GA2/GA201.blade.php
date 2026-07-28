
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
    const mainPageId =3247;
    const bodyId1 = 3224;

    window.injects.injectOnInit.push((that, pageData) => {

    })
    window.injects.injectOnAdd.push((that, pageData) => {
        that.dataset.data.undertaker = '{{session("username")}}';
		that.dataset.data.undertakername = '{{session("user_name")}}';
        that.dataset.data.undertakerday = getTodayDate();
    })
    window.injects.injectOnView.push((that, pageData, id) => {})
    window.injects.injectOnEdit.push((that, pageData, id) => {
        that.dataset.status = 'update';
    })
    window.injects.injectOnCopy.push((that, pageData, id) => {})

    window.injects.injectOnReferenceWrite.push((that, pageData, fromField, referenceData, fields, dataset) => {})

    window.injects.injectOnRowAdd.push((that, pageData, targetSubDataArray, dataset) => {})
    window.injects.injectOnRowDelete.push((that, pageData, parentDataset, formID, rowIndex) => {
        if(that.dataset.subData[bodyId1][rowIndex].data.body_subtotal != null && that.dataset.subData[bodyId1][rowIndex].data.body_subtotal !=  0){
            that.dataset.data.total = parseFloat(that.dataset.data.total- that.dataset.subData[bodyId1][rowIndex].data.body_subtotal).toFixed(2);
        }
    })

    window.injects.injectOnHeadInput.push((that, pageData, field, dataset) => {})
    window.injects.injectOnHeadChange.push((that, pageData, field, dataset) => {})
    window.injects.injectOnHeadClick.push((that, pageData, field, dataset) => {
        const filedCode = field.field_code;
        if( filedCode == "getbtn"  ){
            sendAPIRequest(getURL(`api/inject/gettype`), "post").then(result => {
                let subArr = that.dataset.subData[bodyId1];

                let index = 0; // Initialize index outside the loop
                let total = 0;
                result.forEach(resultItem => {
                    // Set the data for 'exp_item' and 'amount' in the subData entry at the current index
                    formVue.dataset.subData[bodyId1][index]['data']['exp_item'] = resultItem['item_name'];
                    formVue.dataset.subData[bodyId1][index]['data']['amount'] = resultItem['fixed_price'];
                    formVue.dataset.subData[bodyId1][index]['data']['body_subtotal'] = resultItem['fixed_price'];
                    if( formVue.dataset.subData[bodyId1][index]['data']['amount'] != null){
                        total = total+parseFloat(resultItem['fixed_price']);
                        
                    }
                    // Increment index to move to the next entry in the next iteration
                    index++;

                    // Add an empty row after setting the data
                    that.addEmptyRow(dataset, bodyId1);
                });
                clearEmptyRow(that,subArr,bodyId1,"exp_item");
                that.dataset.data.total = total;
        });
        }
    })

    window.injects.injectOnBodyInput.push((that, pageData, parentDataset, row, field, formID, subDataArray) => {})
    window.injects.injectOnBodyChange.push((that, pageData, parentDataset, row, field, formID, subDataArray) => {
        const filedCode = field.field_code;

		if( pageData.page.page_id == mainPageId ){
		   if( filedCode == "amount" || filedCode == "quantity"  ){
            let index = subDataArray.findIndex(x => x.selected);
            let oSubtotal=0;
                row.data.body_subtotal = parseFloat(row.data.amount * row.data.quantity).toFixed(2);

	            for (let element of subDataArray) {

		            if( element.data.body_subtotal ){
		                oSubtotal = parseFloat(parseFloat(oSubtotal) + parseFloat(element.data.body_subtotal)).toFixed(2);
		            }
	            }
           that.dataset.data.total=oSubtotal;
            }else if(filedCode == "body_subtotal"){
                let oSubtotal=0;
                for (let element of subDataArray) {

                    if( element.data.body_subtotal ){
                        oSubtotal = parseFloat(parseFloat(oSubtotal) + parseFloat(element.data.body_subtotal)).toFixed(2);
                    }
                }
                that.dataset.data.total=oSubtotal;
            }
        }
    })
    window.injects.injectOnBodyClick.push((that, pageData, parentDataset, row, field, formID, subDataArray) => {
        const filedCode = field.field_code;
        if( pageData.page.page_id == mainPageId ){
        if(filedCode == "preview_img"){

                let index = subDataArray.findIndex(x => x.selected);
                let idid =formVue.dataset.subData[bodyId1][index].data.id;
                let upload_file =encodeURI(formVue.dataset.subData[bodyId1][index].data.upload_file);
                let scr1="https://ul.clouderptw.com:13579/"+"{{env('DB_DATABASE')}}"+"/storage/app/uploads/91711/"+idid+"-"+upload_file;
                    let data={
           				image:scr1
        			};
                    url=`api/inject/checkimg`;
					sendAPIRequest(getURL(url),"post",data).then(result => {
                        if(result['text']=="Exits"){
                            window.open(scr1,"","resizable=1,height=auto,width=auto");
                        }else{
                            if(upload_file==null){
                                alert("請先上傳檔案");
                            }else{
                                alert("請先保存再預覽");
                            }
                        }
        			});


        }
        }
    })
</script>

@endsection
