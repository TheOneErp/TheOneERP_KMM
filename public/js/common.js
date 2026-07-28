function countOriginal(osubtotal_num, Dataset, osubtotal, otax, pototal, taxrate, formId, subtotal = null, tax = null) {
    let oSubtotal = 0;
    let subDataArray = Dataset.subData[formId];

    for (let element of subDataArray) {
        if (element.data[subtotal]) {
            oSubtotal += parseFloat(element.data[subtotal]); // 加總小計
        }
    }

    if (!osubtotal_num) {
        osubtotal_num = 0;
        Dataset.data[osubtotal] = 0;
    }
    tax = Dataset.data['tax'];

    if (tax == '稅外加') {
        Dataset.data[osubtotal] = parseFloat((oSubtotal).toFixed(2)); // 四捨五入到小數點2位
        Dataset.data[otax] = parseFloat((oSubtotal * taxrate).toFixed(2)); // 原幣稅金
        Dataset.data[pototal] = parseFloat((oSubtotal + Dataset.data[otax]).toFixed(2)); // 原幣合計
    } else {
        Dataset.data[osubtotal] = parseFloat((oSubtotal / (1 + Dataset.data['taxrate'])).toFixed(2));
        Dataset.data[otax] = parseFloat((oSubtotal - Dataset.data[osubtotal]).toFixed(2));
        Dataset.data[pototal] = parseFloat((oSubtotal).toFixed(2)); // 原幣合計
    }
}

function countStandard(osubtotal_num, Dataset, rate, ssubtotal, stax, stotal, otax, ototal) {
    if (!osubtotal_num) osubtotal_num = 0;

    Dataset.data[ssubtotal] = parseFloat((osubtotal_num * rate).toFixed(2)); // 本位幣小計
    Dataset.data[stax] = parseFloat((Dataset.data[otax] * rate).toFixed(2)); // 本位幣稅金
    Dataset.data[stotal] = parseFloat((Dataset.data[ototal] * rate).toFixed(2)); // 本位幣合計
}

function counSubtotal(row, subDataArray, parentDataset, subtotal, num, price, osubtotal, discount, dataPrice = null) {
    let oSubtotal = 0;

    if (dataPrice) {
        row.data[subtotal] = parseFloat((row.data[num] * dataPrice * row.data[discount] / 100).toFixed(2));
    } else {
        row.data[subtotal] = parseFloat((row.data[num] * row.data[price] * row.data[discount] / 100).toFixed(2));
    }

    for (let element of subDataArray) {
        if (element.data[subtotal]) {
            oSubtotal += parseFloat(element.data[subtotal]); // 加總小計
        }
    }

    if (parentDataset.data.tax == "稅內含") {
        parentDataset.data[osubtotal] = parseFloat((oSubtotal / (1 + parentDataset.data['taxrate'])).toFixed(2)); // 原幣小計
    } else {
        parentDataset.data[osubtotal] = parseFloat((oSubtotal).toFixed(2)); // 原幣小計
    }

    return parentDataset.data[osubtotal];
}

function countSubtotal2(row, subDataArray, parentDataset, subtotal, num, price, osubtotal, discount, dataPrice = null) {
    let oSubtotal = 0;

    if (dataPrice) {
        row.data["body_price"] = parseFloat((dataPrice * row.data[discount] / 100).toFixed(2));
        row.data[subtotal] = parseFloat((row.data[num] * dataPrice).toFixed(2));
    } else {
        row.data["body_price"] = parseFloat((row.data["o_body_price"] * row.data[discount] / 100).toFixed(2));
        row.data[subtotal] = parseFloat((row.data[num] * row.data["body_price"]).toFixed(2));
    }

    for (let element of subDataArray) {
        if (element.data[subtotal]) {
            oSubtotal += parseFloat(element.data[subtotal]); // 加總小計
        }
    }

    if (parentDataset.data.tax == "稅內含") {
        parentDataset.data[osubtotal] = parseFloat((oSubtotal / (1 + parentDataset.data['taxrate'])).toFixed(2)); // 原幣小計
    } else {
        parentDataset.data[osubtotal] = parseFloat((oSubtotal).toFixed(2)); // 原幣小計
    }

    return parentDataset.data[osubtotal];
}


function counSubtotaldiscount(row, subDataArray, parentDataset, subtotal, num, price, discount, osubtotal, dataPrice = null) {
    let oSubtotal = 0;

    if (dataPrice) {
        row.data[subtotal] = parseFloat((row.data[num] * dataPrice * row.data[discount] / 100).toFixed(2));
    } else {
        row.data[subtotal] = parseFloat((row.data[num] * row.data[price] * row.data[discount] / 100).toFixed(2));
    }

    for (let element of subDataArray) {
        if (element.data[subtotal]) {
            oSubtotal += parseFloat(element.data[subtotal]); // 加總小計
        }
    }

    if (formVue.dataset.data.tax == "稅內含") {
        parentDataset.data[osubtotal] = parseFloat((oSubtotal / (1 + parentDataset.data['taxrate'])).toFixed(2)); // 原幣小計
    } else {
        parentDataset.data[osubtotal] = parseFloat((oSubtotal).toFixed(2)); // 原幣小計
    }

    return parentDataset.data[osubtotal];
}


function getTodayDate() {
    let fullDate = new Date();
    let yyyy = fullDate.getFullYear();
    let MM = (fullDate.getMonth() + 1) >= 10 ? (fullDate.getMonth() + 1) : ("0" + (fullDate.getMonth() + 1));
    let dd = fullDate.getDate() < 10 ? ("0"+fullDate.getDate()) : fullDate.getDate();
    let today = yyyy + "-" + MM + "-" + dd;
    return today;
}
//清空table
function clearTableRow( that,formId ){
	let currentDataset = that.dataset;
	let currentLen = that.dataset.subData[formId].length;
	let index = 0;
	for( let deleteIndex = 1; deleteIndex<=currentLen;deleteIndex++ ){
		index =  that.dataset.subData[formId].length;
		if( index == 1 ){
			for( let key in currentDataset.subData[formId][0].schema.fields ){
				that.dataset.subData[formId][0].data[key] = null;
			}
		}else{
			formVue.deleteRow(currentDataset,formId,0);
		}
	}
}

//清除多餘列
function clearEmptyRow(that,subArr,formId,fcode,deleteBtn=null){
	let index = subArr.length - 1;
	while (index >= 0) {
		if( deleteBtn ){
		   that.dataset.subData[formId][index].override.preventDelete  = true;
		}

		if( !subArr[index].data[fcode] ){
            formVue.deleteRow(that.dataset,formId,index);
		}
		index--;
	}
}

//小視窗清除多餘列
function clearEmptyRowBylittlePage(subData,littlePageId,littlePageIdBodyFormID,source_code,fcode){
//    for (let element of subData) {
//        let comData = element.subData[littlePageId];
////        clearEmptyRow(element.data[source_code], element.data[source_code].subData[littlePageIdBodyFormID], littlePageIdBodyFormID, fcode);
//    }
}

//篩選資料
function searchbtn(that,serachData,url,formId,clear=null,readonlyBody=[],readonlyHead=[]){
	sendAPIRequest(getURL(url),"post",serachData).then(async result => {
		let getBackStatus = false;
		let rowIdex = 0;
		let subDataArray = that.dataset.subData[formId];
//		if (clear) {
//			clearTableRow(that, formId);
//		} else {
//			rowIdex = subDataArray.length;
//			that.addEmptyRow(that.dataset, formId);
//		}
        if(subDataArray.length == 0){
            that.addEmptyRow(that.dataset, formId);
        }else{
            clearTableRow(that, formId);
        }
		let resLen = result.length;
		if (resLen != 0) {
			for (let index in result) {
				await timeout(20);
				for (let key in result[index]) {
					console.log(result[index]);
					subDataArray[rowIdex]['data'][key] = result[index][key];
					 subDataArray[rowIdex]['data']['remarks'] = result[index]["body_remarks"];
					 subDataArray[rowIdex]['data']['remarks'] = result[index]["body_remarks"];
				}

				if (index != resLen - 1) {
					that.addEmptyRow(that.dataset, formId);
				}

				getBackStatus = true;
				//表身欄位唯獨
				if( readonlyBody.length != 0 ){
				   for(let val of readonlyBody ){
					   that.$set(that.dataset.subData[formId][rowIdex].override.fields, val ,{ field_readonly : true });
				   }
				}
				rowIdex++;
			}
		}else{
			alert("無需取回項目");
		}
		//表頭唯獨
		if( readonlyHead.length != 0 ){
		   for(let val of readonlyHead ){
			   that.$set(that.dataset.override.fields, val, { field_readonly: true });
		   }
		}

		return getBackStatus;
	});
}

function searchbtn2(that,serachData,url,formId,clear=null,readonlyBody=[],readonlyHead=[]){
	sendAPIRequest(getURL(url),"post",serachData).then(async result => {
		let getBackStatus = false;
		let rowIdex = 0;
		let subDataArray = that.dataset.subData[formId];
//		if (clear) {
//			clearTableRow(that, formId);
//		} else {
//			rowIdex = subDataArray.length;
//			that.addEmptyRow(that.dataset, formId);
//		}
        if(subDataArray.length == 0){
            that.addEmptyRow(that.dataset, formId);
        }else{
            clearTableRow(that, formId);
        }
		let resLen = result.length;
		if (resLen != 0) {
			for (let index in result) {
				await timeout(20);
				for (let key in result[index]) {
					subDataArray[rowIdex]['data'][key] = result[index][key];
				}
				if (index != resLen - 1) {
					that.addEmptyRow(that.dataset, formId);
				}

				getBackStatus = true;
				//表身欄位唯獨
				if( readonlyBody.length != 0 ){
				   for(let val of readonlyBody ){
					   that.$set(that.dataset.subData[formId][rowIdex].override.fields, val ,{ field_readonly : true });
				   }
				}
				rowIdex++;
			}
		}else{
			alert("無需取回項目");
		}
		//表頭唯獨
		if( readonlyHead.length != 0 ){
		   for(let val of readonlyHead ){
			   that.$set(that.dataset.override.fields, val, { field_readonly: true });
		   }
		}

		return getBackStatus;
	});
}
function searchbtn3(that,serachData,url,formId,clear=null,readonlyBody=[],readonlyHead=[]){
	sendAPIRequest(getURL(url),"post",serachData).then(async result => {
		let getBackStatus = false;
		let rowIdex = 0;
		let subDataArray = that.dataset.subData[formId];
//		if (clear) {
//			clearTableRow(that, formId);
//		} else {
//			rowIdex = subDataArray.length;
//			that.addEmptyRow(that.dataset, formId);
//		}
        if(subDataArray.length == 0){
            that.addEmptyRow(that.dataset, formId);
        }else{
            clearTableRow(that, formId);
        }
		let resLen = result.length;
		if (resLen != 0) {
			for (let index in result) {
				await timeout(20);
				for (let key in result[index]) {
					subDataArray[rowIdex]['data'][key] = result[index][key];
                    if(key == "product_code"){
                        subDataArray[rowIdex]['data']['cont_code']  = result[index][key];

                    }
                    if(key == "product_name"){
                        subDataArray[rowIdex]['data']['cont_name']  = result[index][key];
                    }
				}
				if (index != resLen - 1) {
					that.addEmptyRow(that.dataset, formId);
				}

				getBackStatus = true;
				//表身欄位唯獨
				if( readonlyBody.length != 0 ){
				   for(let val of readonlyBody ){
					   that.$set(that.dataset.subData[formId][rowIdex].override.fields, val ,{ field_readonly : true });
				   }
				}
				rowIdex++;
			}
		}else{
			alert("無需取回項目");
		}
		//表頭唯獨
		if( readonlyHead.length != 0 ){
		   for(let val of readonlyHead ){
			   that.$set(that.dataset.override.fields, val, { field_readonly: true });
		   }
		}

		return getBackStatus;
	});
}