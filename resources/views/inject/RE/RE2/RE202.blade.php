
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
	const mainPageId = 6258;
	const unfoldId = 6232;
	const bodyId2 = 6233;

	function changeDetail( that,row,money ){
		let rent_rin = 0;
		let tax_rate = that.dataset.data.taxrate;
		if( that.dataset.data.tax == "稅內含" ){

			row.data.rtax = (parseFloat(money) * parseFloat(tax_rate).toFixed(2)); //稅金
			row.data.rent_rin = parseFloat(money) + parseFloat(row.data.rtax); //含稅

		}else{
			rent_rin = (money * tax_rate).toFixed(2); //含稅
			row.data.rtax = rent_rin; //稅金
			row.data.rent_rin = parseFloat(money) + parseFloat(rent_rin); //含稅
		}

	}
	function checkMon( monArr,mon,year,monNum ){
		let monIndex = monArr.indexOf(mon.toString());
		monIndex = parseInt(monIndex) + parseInt(monNum);
		if( monIndex > 11){
		   monIndex = monIndex - 12;
			year++;
		}
		mon = monArr[monIndex];
		return [mon,year];
	}
	function checkDay(year,mon,coll_date){
		firstDate = new Date(year, mon, 0);
		lastDate = firstDate.getDate();
		let fd = 0;
		if( coll_date > lastDate ){
		   fd = lastDate;
		}else{
		   fd = coll_date;
		}
		return fd;
	}
	function countDetail( that ){
		clearTableRow( that,unfoldId );
		let d1 = new Date(that.dataset.data.lease_fdate); //起
		let year = d1.getFullYear();
		let mon = d1.getMonth() + 1;
		let countTime = that.dataset.data.rperiod;  //期數
		let coll_date = that.dataset.data.coll_date; //收租日期
		let lastDate = (new Date(year, mon, 0)).getDate();
		let monArr = ['01','02','03','04','05','06','07','08','09','10','11','12'];
		let firstDate = "";
		let fd = 0;
		if( coll_date > lastDate ){
		   fd = lastDate;
		}else{
		   fd = coll_date;
		}
//		firstDate = new Date(year, mon, coll_date);
		let month = that.dataset.data.month;
		let type = 0;
		let day = 0;
		let rent_rin = 0;
		let rtax = 0;
		let money = parseFloat(that.dataset.data.melectricity) + parseFloat(that.dataset.data.mlease_mprice) + parseFloat(that.dataset.data.mother) + parseFloat(that.dataset.data.msewage) + parseFloat(that.dataset.data.mwater);
		if( that.dataset.data.tax == "稅內含"){
			rent_rin = money; //含稅
			money = (money/(parseFloat(1)+parseFloat(that.dataset.data.taxrate))).toFixed(0);//原幣小記
			rtax = rent_rin - money;

		}else{
			rtax = money * that.dataset.data.taxrate;
			rent_rin = parseFloat(money) + parseFloat(rtax);
		}
		// console.log(money,rent_rin,rtax);
		for (let i = 0; i < countTime; i++) {
			let cmon = 0;
			if( i != 0 ){
				if (month == '0.5') { // 半月收
					coll_date = parseFloat(coll_date) + 14 ;
					firstDate = new Date(year, mon, 0);
					lastDate = firstDate.getDate();
					if( coll_date > lastDate ){
					  	fd = parseFloat(coll_date) - parseFloat(lastDate);
						coll_date = parseFloat(coll_date) - parseFloat(lastDate);
						let value = checkMon(monArr,mon,year,1);
						mon = value[0];
						year = value[1];
					}else{
					   fd = coll_date;
					}
				}else{
					let value = checkMon(monArr,mon,year,month);
					mon = value[0];
					year = value[1];
					fd = checkDay(year,mon,coll_date);
				}
			}else{
				if( mon < 10){
				   mon = "0" + mon;
				}
			}
			if( fd < 10){
			   fd =  "0" + fd;
			}

			let cont_date = year + "-" + mon + "-" + fd;
			that.dataset.subData[unfoldId][i].data.rdate = cont_date;
			that.dataset.subData[unfoldId][i].data.rent_rout = money; //未稅
			that.dataset.subData[unfoldId][i].data.rtax = rtax; //稅金
			that.dataset.subData[unfoldId][i].data.rent_rin = rent_rin; //含稅
			if( i != countTime -1 ){
				that.addEmptyRow(that.dataset,unfoldId);
			}

		}

	}

    window.injects.injectOnInit.push((that, pageData) => {

	})
    window.injects.injectOnAdd.push((that, pageData) => {
		that.dataset.data.undertaker = '{{session("username")}}';
		that.dataset.data.undertakername = '{{session("user_name")}}';
		that.dataset.data.undertakerday = getTodayDate();
		// formVue.dataset.data.sign_in = '{{session("username")}}';
	})
    window.injects.injectOnView.push((that, pageData, id) => {
		let subArr = that.dataset.subData[unfoldId];
		clearEmptyRow(that,subArr,unfoldId,"rdate");
		subArr = that.dataset.subData[bodyId2];
		clearEmptyRow(that,subArr,bodyId2,"rdate");
	})
    window.injects.injectOnEdit.push((that, pageData, id) => {
		let subArr = that.dataset.subData[unfoldId];
//		clearEmptyRow(that,subArr,unfoldId,"rdate",true);
		for( let index in subArr ){
			if( subArr[index].data.pamount && subArr[index].data.pamount != 0 ){
				formVue.$set(formVue.dataset.override.fields, 'unfold' ,{ field_readonly : true });
				formVue.$set(formVue.dataset.override.fields, 'house_id' ,{ field_readonly : true });

				for( let key in subArr[index].schema.attributes ){
					formVue.$set(subArr[index].override.fields, key ,{ field_readonly : true });
				}
			 	subArr[index].override.preventDelete  = true;
			}
		}
		subArr = that.dataset.subData[bodyId2];
		for( let index in subArr ){
			if( subArr[index].data.rdate && subArr[index].data.rdate != "" ){
				formVue.$set(formVue.dataset.override.fields, 'house_id' ,{ field_readonly : true });
				break;
			}
		}
		clearEmptyRow(that,subArr,bodyId2,"rdate",true);

	})
    window.injects.injectOnCopy.push((that, pageData, id) => {
		let subArr = that.dataset.subData[unfoldId];
		clearEmptyRow(that,subArr,unfoldId,"rdate");
		subArr = that.dataset.subData[bodyId2];
		clearEmptyRow(that,subArr,bodyId2,"rdate");
		// formVue.dataset.data.sign_in = '{{session("username")}}';
	})

    window.injects.injectOnReferenceWrite.push((that, pageData, fromField, data, fields, dataset) => {})

    window.injects.injectOnRowAdd.push((that, pageData, targetSubDataArray, dataset) => {
		that.setFormOption(unfoldId,'disableAutoAddOrRemoveRow',true);
	})
    window.injects.injectOnRowDelete.push((that, pageData, parentDataset, formID, rowIndex) => {})

    window.injects.injectOnHeadInput.push((that, pageData, field, dataset) => {})
    window.injects.injectOnHeadChange.push((that, pageData, field, dataset) => {

	})
    window.injects.injectOnHeadClick.push((that, pageData, field, dataset) => {
		if( pageData.page.page_id == mainPageId){
			const filedCode = field.field_code;
			let msg = "";
			switch (filedCode) {
				case 'unfold':

					if( !that.dataset.data.lease_fdate ){
					   msg = msg + "租約起日 " ;
					}
					if( !that.dataset.data.coll_date ){
					   msg = msg + "收租日 " ;
					}
					if( !that.dataset.data.rperiod ){
					    msg = msg + "收租期數 " ;
					}
					if( !that.dataset.data.month ){
					    msg = msg + "每期...月 " ;
					}
					if( !that.dataset.data.house_id ){
					    msg = msg + "房屋代碼 " ;
					}
					if( msg != "" ){
						alert(msg + "尚未填寫，請確認");
					}else{
						countDetail( that );
					}
					break;
				default:
			}
		}
	})

    window.injects.injectOnBodyInput.push((that, pageData, parentDataset, row, field, formID, subDataArray) => {})
    window.injects.injectOnBodyChange.push((that, pageData, parentDataset, row, field, formID, subDataArray) => {
		if( pageData.page.page_id == mainPageId){
			const filedCode = field.field_code;
			switch (filedCode) {
				case 'rent_rout':
//					deleteNewRow( that,unfoldId,parentDataset);
					changeDetail( that,row,row.data.rent_rout );
					break;
				case 'remarks':
				case 'rdate':
				case 'rent_rin':
				case 'rent_rout':
				case 'rtax':
					deleteNewRow( that,unfoldId,parentDataset);
					break;
				default:
			}
		}
	})
    window.injects.injectOnBodyClick.push((that, pageData, parentDataset, row, field, formID, subDataArray) => {
//		if( pageData.page.page_id == mainPageId){
//			const filedCode = field.field_code;
////			switch (filedCode) {
////				case 'rent_rout':
////					break;
////				default:
////			}
//		}
	})
</script>

@endsection
