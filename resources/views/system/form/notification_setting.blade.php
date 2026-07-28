@extends('layouts.default')
@section('title', '新增通知')
@section('content')
<style>
	:disabled {
	  background-color: #eee !important;
	}
</style>
<div class="ts stackable grid">
 <!-- Notice Messages -->
@if(isset($errors) && ((is_object($errors) && count($errors->all()) > 0 ) || count($errors) > 0))
<div class="ts inverted icon negative message">
    <i class="remove circle icon"></i>
    <div class="header">
        @if(is_object($errors))
            @foreach($errors->all() as $key=>$message) {{ $message }} <br />
            @endforeach
            
        @else {{ $errors }} @endif
    </div>
</div>
@endif

@if(Session::has('success'))
<div class="ts inverted icon positive message">
    <i class="check circle icon"></i>
    <div class="header">
        {{Session::get('success')}}
    </div>
</div>
@endif
    <div class="sixteen wide column">
        
        <h2 class="txt_center">新增通知</h2>
        <form id="newMessageForm" action="{{ route('notification_setting_save', ['type' => $type,'id' => $id]) }}" name="newMessageForm" method="POST" onsubmit="return selectDisable()">
            @csrf
            <table class="ts table">
                <tbody>
                    <tr>
                        <td><span style="color:red;">*</span>表單</td>
                        <td class="ts input">
                              <select class="ts basic dropdown" name="page_id" {{$type=="update" ?"":""}} {{ $type=="update"?"disabled":""}}>
                               			@foreach($pageArr as $key=>$value)
                               			
											@if(old('page_id',is_null($data) ? '' : $data->page_id) == $value['page_id'] ) 
											<option value="{{$value['page_id']}}" selected>{{$value['page_text']}}
											@else
											<option value="{{$value['page_id']}}">{{$value['page_text']}}
											@endif 
                              				</option>
                               			
                               		@endforeach
								</select>
                        </td>
                    </tr>
                    <tr>
                        <td><span style="color:red;">*</span>表單狀態</td>
                        <td class="ts input">
                          <select class="ts basic dropdown" name="msg_status" {{$type=="update" ?"":""}} {{ $type=="update"?"disabled":""}}>
								<option value="insert" @if(old('msg_status',is_null($data) ? '' : $data->notification_setting_trigger_type) == "insert" ) selected @endif >新增</option>
								<option value="update" @if(old('msg_status',is_null($data) ? '' : $data->notification_setting_trigger_type) == "update" ) selected @endif>修改</option>
								<option value="delete" @if(old('msg_status',is_null($data) ? '' : $data->notification_setting_trigger_type) == "delete" ) selected @endif>刪除</option>
							</select>
                    </tr>
                    <tr>
                        <td><span style="color:red;">*</span>內容</td>
                        <td class="ts resizable input"><textarea name="msg_content" rows="4" cols="50">{{ old('msg_content',is_null($data) ? '' : $data->notification_setting_content) }}</textarea></td>
                    </tr>
                    <tr>
                        <td>開啟EMAIL</td>
                        <td class=""><input type="checkbox" name="msg_email" value="1" @if(old('msg_email',is_null($data) ? '' : $data->notification_setting_mail) == "1")
                            checked
                            @endif
                            ></td>
                    </tr>
                    <tr>
                        <td>開啟簡訊</td>
                        <td class=""><input type="checkbox" name="msg_sms" value="1" @if(old('msg_sms',is_null($data) ? '' : $data->notification_setting_phone) == "1")
                            checked
                            @endif
                            ></td>
                    </tr>
                </tbody>
            </table>
            <div style="overflow-y: auto;" class="content" >
              <div class="ts  grid">
              	<div class="four wide column">
              		<select name="guSelect" class="ts basic small dropdown ">
              			<option value="none" selected disabled hidden>選擇 用戶型態</option> 
						<option value="group">群組</option>
						<option value="user">個人</option>
					</select>
              	</div>
              	<div class="four wide column">
              		<button type="button" class="ts  button" id="cancelled">取消</button>
              	</div>              	
              </div>               
                <table id="ugTable" class="ts compact celled definition table">
                    <thead>
                        <tr>
                            <td>
								<div class="ts toggle checkbox">
                                        <input type="checkbox" id="checkAllBtn" >
                                        <label for="checkAllBtn"></label>
                                    </div>
                                
                            </td>
                            <th>帳號</th>
                            <th>姓名</th>
                            <th>用戶型態</th>
                        </tr>
                    </thead>
                    <tbody id="userTbody">
                       <tr v-for="(element , index) in ugArr" :class="'radioTr '+ element.target_type">						   
							<td class="collapsing">
								<div class="ts toggle checkbox">
									<input type="checkbox" :id="element.target_type+'_'+element.target_id" name="msg_object[]" :value="element.target_type+'_'+element.target_id"  v-model="element.check">
									<label :for="element.target_type+'_'+element.target_id"></label>
								</div>
							</td>
							<td>@{{element.target_username}}</td>
							<td>@{{element.target_name}}</td>
							<td>@{{element.target_type}}</td>
                       </tr>
                    </tbody>
                    
                </table>
            </div>
            <div class="col-md-12 btn_center">
                <button class="ts button" type="submit" name="sendBtn">送出</button>
            </div>
        </form>
    </div>
</div>
@endsection
@section('script')
@parent
<script>
    ts('#first.tabbed.menu .item').tab();
	ts('.ts.dropdown:not(.basic)').dropdown();
	let loadingDiv = new Vue({
		el: "#userTbody",
		data: {
			ugArr : @json($ugArr),
			oldArr : @json(old('msg_object')), 	//update inser null update [aaaaa]
			status : "{{$type}}"
		},
		beforeMount(){
			if(this.oldArr){
				for (let v of this.ugArr) {
				   v.check = 0;
				   for (let o of this.oldArr) {
					   if( o == `${v.target_type}_${v.target_id}`){
						   v.check = 1;
					   }
				   }
				}
			   
			}
		},
		methods: {

		}
	})
	function checkAll(){
		let allItem = $("input[name='msg_object[]']");
		let num = 0;
		for(var val of allItem) { 
			if($(val).closest("tr").is(":visible") && $(val).is(':checked') ){
				num++;					
			}				
		}
		if (num == $("#ugTable tr:visible" ).length - 1) {
			$('#checkAllBtn').prop('checked', true);
		}else{
			$('#checkAllBtn').prop('checked', false);
		}
		
	}
	
	function selectDisable(){
		$("select[name='msg_status']").removeAttr("disabled");
		$("select[name='page_id']").removeAttr("disabled");
		return true;
	}
	(function($) {
		$('.radioTr').click(function() {
			let chosenRadio = $(this).find('input[type="checkbox"]');
			if( chosenRadio.is(':checked') ){
				chosenRadio.prop('checked', false);
			}else{
				chosenRadio.prop('checked', true);
			}			
		 });
		
		$('#checkAllBtn').click(function() {
			let allItem = $("input[name='msg_object[]']");
			
			if( $(this).is(':checked') ){
				
				for(var val of allItem) { 
					if($(val).closest("tr").is(":visible") ){
						$(val).prop('checked', true);
					}
				}
			}else{
//				allItem.prop('checked', false);
				for(var val of allItem) { 
					if($(val).closest("tr").is(":visible") ){
						$(val).prop('checked', false);
					}
				}
			}			
		 });
		
		$('select[name=guSelect]').change(function() {
			if($(this).val() == 'user' ){
				$(".user").show();
				$(".group").hide();
			}else{
				$(".user").hide();
				$(".group").show();
			}
			checkAll();
		});
		
		$("#cancelled").click(function() {
			$(".user").show();
			$(".group").show();
			$('select[name=guSelect]').val("none");
			checkAll();
	 	});

	

    })(jQuery);
</script>
@endsection

