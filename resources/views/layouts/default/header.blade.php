@section('header')
<div class="ts pointing secondary large icon menu">
    <a
        class="item"
        style="z-index: 2;"
        id="menuButton"
        data-tooltip="{{ $commonTranslations['menu'] }}"
    ><i class="list icon"></i></a>
    <a
        class="item glm header title"
        href={{ route("index") }}
        data-tooltip="{{ $commonTranslations['index'] }}"

    >
	<!-- <img style="padding-top:5px " height="50" border="0" src="http://ul.clouderptw.com:8080/TheOneERP_yl/resources/image/LOGO_210309.jpg" alt="雲量客服"> -->
	{{ env("APP_NAME") }}
</a>
    <div class="right menu">
	<div class="item"><p id="timer"></p></div>
	<a href="line://ti/p/@465pujez" target="_blank" class="item"
	style="z-index: 2;" id="open_line" data-tooltip="雲量客服"><i class="volume control phone icon" ></i></a>
	{{-- <a href="line://ti/p/@465pujez" target="_blank"><img style="padding-top:5px " height="50" border="0" src="https://ul.clouderptw.com:13579/TheOneERP_yl/resources/image/LOGO_210309.jpg" alt="雲量客服"></a> --}}
        <div class="item">{{$commonTranslations["welcome"]}}, {{session("user_name")}}</div>
        <a
            class="item open"
            :class="{unread: unreadNum != 0}"
            style="z-index: 2;"
            id="noticeOpen"
            @click="getNotice"
            data-tooltip="{{ $commonTranslations['notification'] }}"
        ><i class="bell icon outline"></i></a>
        <a
            href="{{ route("users_form",["type" => "update", "id" => session('user_id')]) }}"
            class="item"
            style="z-index: 2;"
            data-tooltip="{{ $commonTranslations['profile'] }}"
        ><i class="user outline icon"></i></a>
        <a
            href="{{ route("system.auth.logout.get") }}"
            class="item"
			id="logout"
            style="z-index: 2;"
            data-tooltip="{{ $commonTranslations['logout'] }}"
        ><i class="log out icon"></i></a>

    </div>
</div>


<div v-cloak id="noticeBar" class="ts grid noticeBar" v-if="seen">
	<!-- <i class="big fitted icon caret up littleTriangle" style="z-index: 2;"></i> -->
	<div class="twelve wide column"><span style="color: white;">通知</span></div>
	<div class="four wide column"><a style="color: white;" href="#" @click="readAll">全部已讀</a></div>
</div>
<div v-cloak class=" glm scrollBar noticeClass" id="noticeDiv" name="noticeDiv"  v-if="seen" style="box-shadow: -2px 6px 5px #8888885c;" >

	<div id="reslut" class="">
		<div v-for="(msg , index) in showmsg" :key="index" class="NinnerDiv" @click="read(msg.id,msg.page_link)" :style="[msg.read == 0 ? {'background-color': '#e7f8fa'} : {'background': '#FFF'}]">

			<p style="font-weight: bolder;"><i class="send outline icon"></i>@{{msg.page}}</p>
			<p style="padding-left: 28px;">@{{msg.content}}</p>
			<p style="color: #959595;text-align: right;">@{{msg.time}}&nbsp;&nbsp;By&nbsp;@{{msg.creator}}</p>
		</div>
	</div>
</div>

@endsection

@section('script')
@parent
<script>
document.getElementById("menuButton").addEventListener('click',(e) => {
    document.getElementById("menu").className = document.getElementById("menu").className.includes("four") ? "zero wide column" : "four wide column"
    document.getElementById("content").className = document.getElementById("content").className.includes("twelve") ? "sixteen wide column" : "twelve wide column"
});
let noticeEl = new Vue({
	el: "#noticeDiv",
	data: {
		showmsg:[],
		img:'',
		messages: [],
		scrolledToBottom: false,
		remainNum:0,
		notiHistory:1,
		moreLoading :true,
		seen : false,
	},
	beforeMount(){
		this.noticeOrNot();
	},
	mounted() {
		window.addEventListener('scroll',this.scrollAction,true);
	},
	methods: {
		scrollAction(e){
			if(e.srcElement.scrollTop+e.srcElement.offsetHeight>e.srcElement.scrollHeight-100 && this.remainNum>0 && this.moreLoading ){
				this.moreLoading = false;
				this.showmessage();
			}
		},
		noticeOrNot : function (){
			data = {};
			url = "{{ route('notification_setting_noticeOrNot') }}";
			sendAPIRequest(url,"post",data).then(result => {
				this.remainNum = result["remainNum"];
				noticeOpen.changeUn(result["notice"]);
				if( this.remainNum > 0){
					this.showmessage();
				}


			});

		},
		showmessage: function () {
			data2 = {
				notiHistory:this.notiHistory,
				remainNum:this.remainNum
			};
			url2 = "{{ route('notification_setting_selectNotice') }}";
			sendAPIRequest(url2,"post",data2).then(result2 => {
				for( const ele of result2 ){
					this.showmsg.push(ele);
					this.notiHistory = ele.id;
					this.remainNum = ele.remainNum;
				}
				this.moreLoading = true;
				var test=document.querySelector('.bell');
// var test123=window.getComputedStyle(test,'::before')
var bell=window.getComputedStyle(test,'::after')
if(bell.content!="none"){
	var bellch=document.getElementsByClassName('bell icon outline');
	if(bellch[0]!=undefined){
		bellch[0].className="bell loading negative icon";
	}
}
			});
		},
		read:function(item,link){
			url = "{{ route('notification_setting_trandRead') }}";
			data = {'id':item},
			sendAPIRequest(url,"post",data).then(result => {
				location.href = "{{ route('system.page.list.show',['page_id'=>'~']) }}".replace("~",link) ;
			});
		}
	}
});
let noticeOpen = new Vue({
	el: "#noticeOpen",
	data: {
		unreadNum:0
	},
	methods: {
		getNotice:function()  {
			noticeBar.seen = !noticeBar.seen;
			noticeEl.seen = !noticeEl.seen;
		},
		changeUn:function(num){
			this.unreadNum = num;
		}
	}
});
let noticeBar = new Vue({
	el: "#noticeBar",
	data: {
		seen : false,
		target : {{session("user_id")}}
	},
	methods: {
		readAll:function(item){
			url = "{{ route('notification_setting_trandRead') }}";
			data = {'target':this.target},
			sendAPIRequest(url,"post",data).then(result => {
				if( result == 1 ){
					noticeEl.showmsg= [];
					noticeEl.noticeOrNot();
			   }
			var bellch=document.getElementsByClassName('bell icon');
			bellch[0].className="bell icon outline";
			});
		}
	}
});

var countDownDate = new Date(new Date().setHours(new Date().getHours() + 2));

$(document.body).ready(function() {
  countDownDate = new Date(new Date().setHours(new Date().getHours() + 2));
  now = new Date().getTime();
  distance = countDownDate - now;
   hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
  minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
  seconds = Math.floor((distance % (1000 * 60)) / 1000);
 document.getElementById("timer").innerHTML = hours + "h " + minutes + "m " + seconds + "s "+"後自動登出";

});
$(document).on('click','body *',function(){
 countDownDate = new Date(new Date().setHours(new Date().getHours() + 2));
  now = new Date().getTime();
  distance = countDownDate - now;
   hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
  minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
  seconds = Math.floor((distance % (1000 * 60)) / 1000);
 document.getElementById("timer").innerHTML = hours + "h " + minutes + "m " + seconds + "s "+"後自動登出";

});

var x = setInterval(function() {

  var now = new Date().getTime();

  var distance = countDownDate - now;

  var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
  var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
  var seconds = Math.floor((distance % (1000 * 60)) / 1000);


  document.getElementById("timer").innerHTML = hours + "h " + minutes + "m " + seconds + "s "+"後自動登出";


  if (distance < 0) {
    clearInterval(x);
    document.getElementById("timer").innerHTML = "閒置過久，系統已被登出";
 document.location.href="{{ route("system.auth.logout.get") }}";
  }
}, 1000);
$(document).on('click','#open_line',function(){
	window.open('https://ul.clouderptw.com:13579/{{env('DB_DATABASE')}}/resources/image/THE ONE ERP 客服平台.jpg');
});

</script>
@endsection
