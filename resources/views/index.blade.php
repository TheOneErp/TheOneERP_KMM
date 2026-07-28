@extends('layouts.default')
@section('title', $languages["index"])
@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

<style>

  </style>
<div class="container">


<div id="calendar" style="z-index:0"></div><br>
<div class="overlay" id="overlay"></div>
<div class="form-container" id="formContainer">
    <h2>新増事件</h2>
    <form id="eventForm">
        <label for="eventName">標題:&nbsp&nbsp&nbsp&nbsp</label>
        <input type="text" id="eventName" name="eventName" required>
        <br>
		<br>
        <label for="eventDate_start">日期起:</label>
        <input type="datetime-local" id="eventDate_start" name="eventDate" required><br><br>
		<label for="eventDate_end">日期迄:</label>
        <input type="datetime-local" id="eventDate_end" name="eventDate" required >
        <br>
		<br>
		<label for="event_desc">內容:&nbsp&nbsp&nbsp&nbsp</label>
        <input type="text" id="event_desc" name="event_desc" required>
        <br>
		<br>
		<label>顏色:&nbsp&nbsp&nbsp&nbsp</label><input type="color" id="event_color" style="width: 30%;"  >
		<br><br>
        <button class="button" type="submit">新増</button>&nbsp&nbsp<button type="button" id="cancel" class="cancel">取消</button>
    </form>
</div>
<div id="calendar" style="z-index:0"></div><br>
<div class="overlay2" id="overlay2"></div>
<div class="form-container2" id="formContainer2">
    <h2>修改事件</h2>
    <form id="eventForm2">
        <label for="eventName">標題:&nbsp&nbsp&nbsp&nbsp</label>
        <input type="text" id="eventName_edit" name="eventName_edit" required>
        <br>
		<br>
        <label for="eventDate_start">日期起:</label>
        <input type="datetime-local" id="eventDate_start_edit" name="eventDate_start_edit" required><br><br>
		<label for="eventDate_end">日期迄:</label>
        <input type="datetime-local" id="eventDate_end_edit" name="eventDate_end_edit" required >
        <br>
		<br>
		<label for="event_desc">內容:&nbsp&nbsp&nbsp&nbsp</label>
        <input type="text" id="event_desc_edit" name="event_desc_edit" required><input type="hidden" id="event_id" name="event_id" required>
        <br>
		<br>

		<label>顏色:&nbsp&nbsp&nbsp&nbsp</label><input type="color" id="event_color_edit" style="width: 30%;"  >
		<br><br>
        <button class="button" type="submit">修改</button>&nbsp&nbsp<button type="button" id="cancel2" class="cancel2">取消</button>&nbsp&nbsp<button type="button" id="event_delete" class="event_delete">刪除</button>
    </form>
</div>
<div class="eight wide column"><div class="ts input"><div class="inline field">

<table>
  <tr>
    <td><label for="title">標題:&nbsp&nbsp</label></td>
    <td><input type="text" id="title" style="width: 100%;"/></td>
  </tr>
  <tr>
    <td><label for="start">時間起:&nbsp&nbsp</label></td>
    <td><input type="datetime-local" id="start" style="width: 100%;" lang="en-us" required/></td>
  </tr>
  <tr>
    <td><label for="end">時間迄:&nbsp&nbsp</label></td>
    <td><input type="datetime-local" id="end" style="width: 100%;" lang="en-us" required/></td>
  </tr>
  <tr>
    <td><label for="desc">描述:&nbsp&nbsp</label></td>
    <td><input type="text" id="desc" style="width: 100%;"/></td>
  </tr>
  <tr>
    <td><label for="color">顏色:&nbsp&nbsp</label></td>
    <td><div style="display: flex; align-items: center;">
    <input type="color" id="color" value="#000000" style="width: 70%; margin-right: 10px;">
    <div id="colorDisplay" style="width: 50px; height: 50px; border: 1px solid #000;background-color: #000000;"></div>
</div></td>
  </tr>
  <tr>
	<br>
    <td colspan="2"><button class="button" onclick="addevent()">送出</button></td>
  </tr>
</table>
</div>


</div>
<script src="{{ asset('js/vue.js') }}"></script>
  <script src="{{ asset('js/echarts.min.js') }}"></script>
  <script src="{{ asset('js/index.min.js') }}"></script>
  <script src="{{ asset('js/popper.min.js') }}"></script>
  <script src="{{ asset('js/bootstrap.min.js') }}"></script>
  <script src="{{ asset('js/chart.js@2.8.0') }}"></script>
<script>
document.getElementById('color').addEventListener('input', function() {
    var color = this.value;
    document.getElementById('colorDisplay').style.backgroundColor = color;
});
$('#overlay').click(function() {
	$('#overlay').fadeOut(500);
    $('#formContainer').fadeOut(500);
});


$('#overlay2').click(function() {
	$('#overlay2').fadeOut(500);
    $('#formContainer2').fadeOut(500);
});
function addevent() {
    title= document.getElementById("title").value
		start= document.getElementById("start").value
		end= document.getElementById("end").value
		desc= document.getElementById("desc").value
		color= document.getElementById("color").value

		start= new Date(start);
		start = moment(start);
		start = start.format("YYYY/MM/DD HH:mm:ss ")
		end= new Date(end);
		end= moment(end);
		end = end.format("YYYY/MM/DD HH:mm:ss ")
		var date1 = moment(start, "YYYY/MM/DD HH:mm:ss");
		var date2 = moment(end, "YYYY/MM/DD HH:mm:ss");
  if(title == "" || start == "Invalid date" || end == "Invalid date" || desc == ""){
    alert("請輸入所有欄位");
  }else if((date1.isAfter(date2))||(date1.isSame(date2))){
		alert("結束時間必須大於開始時間");

  }
  else{
		$.ajaxSetup({
			headers:{
				'X-CSRF-TOKEN': window.csrfToken
			}
		});
		    $.ajax({
                    url:getURL(`action`),
                    type:"POST",
                    data:{
                        title: title,
                        start: start,
                        end: end,
                        desc: desc,
						color:color,
                        type: 'add'
                    },
                    success:function(data)
                    {

                        alert("登記成功");
						location.reload();
                    }
                })
  }



}

  $(document).ready(function () {



$.ajaxSetup({
	headers:{
		'X-CSRF-TOKEN': window.csrfToken
	}
});

var calendar = $('#calendar').fullCalendar({
	themeSystem: 'bootstrap3',
	editable:true,
	  header:{
		left:'prev,next today',
		center:'title',
		right:'month,agendaWeek,agendaDay,listWeek,listMonth'
	},
	locale: 'zh-tw',
	eventTimeFormat: {
                hour: 'numeric',
                minute: '2-digit',
                meridiem: false // Don't display AM/PM
            },
	events:'getcal',
	//eventColor: '#009FCC',
	//eventTextColor : '#ffffff',
	nextDayThreshold: '06:00:00',

	displayEventEnd:true,
	displayEventTime: false,
	selectable:true,
	selectHelper: true,
	editable:true,
	eventResize: function(event, delta)
	{
		//console.log(event.color);
		var start = $.fullCalendar.formatDate(event.start, 'Y-MM-DD HH:mm:ss');
		var end = $.fullCalendar.formatDate(event.end, 'Y-MM-DD HH:mm:ss');
		var title = event.title;
		var id = event.id;
		var color = event.color;
		var desc = event.desc;
		$.ajax({
			url:getURL(`action`),
			type:"POST",
			data:{
				title: title,
				start: start,
				end: end,
				id: id,
				desc: desc,
				color: color,
				type: 'update'
			},
			success:function(response)
			{
				calendar.fullCalendar('refetchEvents');
				alert("登記已更新!");
			}
		})
	},
	eventDrop: function(event, delta)
	{
		var start = $.fullCalendar.formatDate(event.start, 'Y-MM-DD HH:mm:ss');
		var end = $.fullCalendar.formatDate(event.end, 'Y-MM-DD HH:mm:ss');
		var title = event.title;
		var id = event.id;
		var color = event.color;
		var desc = event.desc;
		//console.log(event.color);
		$.ajax({
			url:getURL(`action`),
			type:"POST",
			data:{
				title: title,
				start: start,
				end: end,
				id: id,
				desc: desc,
				color: color,
				type: 'update'
			},
			success:function(response)
			{
				calendar.fullCalendar('refetchEvents');
				alert("登記已更新!");
			}
		})
	},

	eventClick:function(event)
	{
		// Display the overlay and form container
		$('#overlay2').fadeIn(500);
		$('#formContainer2').fadeIn(500);
		$('#eventName_edit').val(event.title) ;
		$('#eventDate_start_edit').val($.fullCalendar.formatDate(event.start, 'Y-MM-DD HH:mm:ss')) ;
		$('#eventDate_end_edit').val($.fullCalendar.formatDate(event.end, 'Y-MM-DD HH:mm:ss')) ;
		$('#event_desc_edit').val(event.desc);
		$('#event_color_edit').val(event.color);
		$('#event_id').val(event.id);



		},

	eventRender : function(event,element) {

		element.find('.fc-title').append('<br><span class="fc-time">' +
                         moment(event.start).format('H:mm') + ' - ' +
                         moment(event.end).format('H:mm') +
                         '</span><br>'+ '<div class="fc-event-desc customized-scrollbar">'+event.desc+'</div>');
		element.find('.fc-list-item-title').append('<br><span class="fc-time">' +
		moment(event.start).format('H:mm') + ' - ' +
		moment(event.end).format('H:mm') +
		'</span><br>'+ '<div class="fc-event-desc customized-scrollbar">'+event.desc+'</div>');
		element[0].title = event.desc;
		$('.fc-next-button').text('>>');
		$('.fc-prev-button').text('<<');

	},    viewRender: function(view, element) {
        // Replace the span in fc-prev-button
        $('.fc-prev-button').html('<<'); // Replace with your desired content

        // Replace the span in fc-next-button
        $('.fc-next-button').html('>>'); // Replace with your desired content
    },
	views: {

		    month: { buttonText: '月' },
			agendaWeek: { buttonText: '週' },
			agendaDay: { buttonText: '日' },
            listWeek: { buttonText: '週列表' },
			listMonth: { buttonText: '月列表' },
        },
		dayClick: function(date, jsEvent, view) {
            // Check if the clicked day is empty (has no events)
			if (view.name === 'month' && date.hasTime() === false) {
                    // Display the overlay and form container
                    $('#overlay').fadeIn(500);
                    $('#formContainer').fadeIn(500);

                    // Set the selected date in the form
                    $('#eventDate_start').val(date.format('Y-MM-DD HH:mm:ss'));
					var endDate = date.clone().add(1, 'hours');
					$('#eventDate_end').val(endDate.format('Y-MM-DD HH:mm:ss'));
                }
        }

});

		$('#eventForm').submit(function (event) {
            event.preventDefault();

            // Get values from the form
            var eventName = $('#eventName').val();
            var eventDate_start = $('#eventDate_start').val();
			var eventDate_end = $('#eventDate_end').val();
			var event_desc = $('#event_desc').val();
			var color = $('#event_color').val();
			eventDate_start= new Date(eventDate_start);
			eventDate_start = moment(eventDate_start);
			eventDate_start = eventDate_start.format("YYYY/MM/DD HH:mm:ss ");
			eventDate_end= new Date(eventDate_end);
			eventDate_end= moment(eventDate_end);
			eventDate_end = eventDate_end.format("YYYY/MM/DD HH:mm:ss ");
			var date1 = moment(eventDate_start, "YYYY/MM/DD HH:mm:ss");
			var date2 = moment(eventDate_end, "YYYY/MM/DD HH:mm:ss");
            // Create a new event object
			if((date1.isAfter(date2))||(date1.isSame(date2))){
				alert("結束時間必須大於開始時間");
				return false;
			}else{
		$.ajaxSetup({
			headers:{
				'X-CSRF-TOKEN': window.csrfToken
			}
				});
		    $.ajax({
                    url:getURL(`action`),
                    type:"POST",
                    data:{
						title: eventName,
						start: eventDate_start,
						end: eventDate_end,
						desc: event_desc,
						color:color,
                        type: 'add'
                    },
                    success:function(data)
                    {

                        alert("登記成功");
						calendar.fullCalendar('refetchEvents');
                    }
                })

            // Hide the overlay and form container
            $('#overlay').fadeOut(500);
            $('#formContainer').fadeOut(500);
			}

        });
		$('#eventForm2').submit(function (event) {
            event.preventDefault();

            // Get values from the form
            var title = $('#eventName_edit').val();
            var eventDate_start = $('#eventDate_start_edit').val();
			var eventDate_end = $('#eventDate_end_edit').val();
			var desc = $('#event_desc_edit').val();
			var color = $('#event_color_edit').val();
			var id = $('#event_id').val();
            // Create a new event object
			eventDate_start= new Date(eventDate_start);
			eventDate_start = moment(eventDate_start);
			eventDate_start = eventDate_start.format("YYYY/MM/DD HH:mm:ss ")
			eventDate_end= new Date(eventDate_end);
			eventDate_end= moment(eventDate_end);
			eventDate_end = eventDate_end.format("YYYY/MM/DD HH:mm:ss ")
			var date1 = moment(eventDate_start, "YYYY/MM/DD HH:mm:ss");
			var date2 = moment(eventDate_end, "YYYY/MM/DD HH:mm:ss");
			if((date1.isAfter(date2))||(date1.isSame(date2))){
				alert("結束時間必須大於開始時間");
				return false;
			}else{
			$.ajaxSetup({
			headers:{
				'X-CSRF-TOKEN': window.csrfToken
			}
				});
		    $.ajax({
                    url:getURL(`action`),
                    type:"POST",
                    data:{
						title: title,
						start: eventDate_start,
						end: eventDate_end,
						id: id,
						desc: desc,
						color: color,
						type: 'update'
                    },
                    success:function(data)
                    {

                        alert("修改成功");
						calendar.fullCalendar('refetchEvents');
                    }
                })

            // Hide the overlay and form container
            $('#overlay2').fadeOut(500);
            $('#formContainer2').fadeOut(500);
			}
        });
			$("#cancel").click(function(){
				$('#overlay').fadeOut(500);
				$('#formContainer').fadeOut(500);
		});
		$("#cancel2").click(function(){
					$('#overlay2').fadeOut(500);
                    $('#formContainer2').fadeOut(500);
		});
		$("#event_delete").click(function(){
			if(confirm("確定要刪除嗎?")){
				var id = $('#event_id').val();
				$.ajaxSetup({
			headers:{
				'X-CSRF-TOKEN': window.csrfToken
			}
				});
		    $.ajax({
                    url:getURL(`action`),
                    type:"POST",
                    data:{
						id: id,
						type: 'delete'
                    },
                    success:function(data)
                    {
						calendar.fullCalendar('refetchEvents');
						alert("事件已刪除");
						$('#overlay2').fadeOut(500);
						$('#formContainer2').fadeOut(500);
                    }
                })

			}


		});
	function closeForm() {
    // Hide the overlay and form container
    $('#overlay').fadeOut(500);
    $('#formContainer').fadeOut(500);
}
});


  </script>

@endsection
