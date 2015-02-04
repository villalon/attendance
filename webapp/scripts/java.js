$(function(){
if (typeof courseid != 'undefined') {start();}
$("#studentLogin").submit(function(e){e.preventDefault();studentLogin();});
$("#teacherLogin").submit(function(e){e.preventDefault();teacherLogin();});
$("#openSessions .openSession a").click(function(e){e.preventDefault();markAttendance(this);});


$("#attended").click(function(e){
	e.preventDefault();$("#list_title div").hide();
	$("#lists a").removeClass("active");
	$(this).addClass("active");
	$("#title_attended").show();
	$("#list p").hide();
	$(".attended").show();
});

$("#all").click(function(e){
	e.preventDefault();
	$("#lists a").removeClass("active");
	$(this).addClass("active");
	$("#list_title div").hide();
	$("#title_all").show();
	$("#list p").show();
});

$("#absent").click(function(e){
	e.preventDefault();
	$("#lists a").removeClass("active");
	$(this).addClass("active");
	$("#list_title div").hide();
	$("#title_absent").show();
	$("#list p").hide();
	$("#list p").not(".attended").show();
});

arrange();
$(window).resize(arrange());
});

function arrange(){
	ww = $(window).width();
}

function studentLogin(){
show_error('');
user = $(".login input:eq(0)").val();
pass = $(".login input:eq(1)").val();
if (user=="" || pass==""){show_error(allfields);}
else{
$("#loading").fadeIn();
$.post("../ajax/actions.php",{action:'studentLogin',user:user,pass:pass},function(data){
	if (data==""){document.location.href="markAttendance.php";}
	else {show_error(data);$("#loading").fadeOut();}
});
}
}

function markAttendance(a){
show_error('');
div = a.parentNode;
courseid = $(div).attr("id");
$("#loading").fadeIn();
$.post("../ajax/actions.php",{action:'markAttendance',courseid:courseid},function(data){
	if (data==""){a.remove();$(div).append('<span>OK <img src="../scripts/images/ok.png">');}
	else {show_error(data);}
	$("#loading").fadeOut();
});
}

function teacherLogin(){
show_error('');
user = $(".login input:eq(0)").val();
pass = $(".login input:eq(1)").val();
if (user=="" || pass==""){show_error(allfields);}
else{
$("#loading").fadeIn();
$.post("../ajax/actions.php",{action:'teacherLogin',user:user,pass:pass},function(data){
	if (data==""){document.location.href="checkAttendance.php";}
	else {show_error(data);$("#loading").fadeOut();}
});
}
}

function show_error(text){
$("#error").html(text);	
}

var interval;

function start(){
interval = setInterval(function(){getAttended()},1000);
}

function getAttended(){
if ($("#sessionStatus span").html()==". . . "){$("#sessionStatus span").html("");}
else {$("#sessionStatus span").html($("#sessionStatus span").html()+". ");}
$.post('../../ajax/getAttended.php',{courseid:courseid},function(data){
data = jQuery.parseJSON(data);
numberAttended = data.studentIds.length;
$("#percentage").html(data.percentage);
$("#numberAttended").html(numberAttended);
if (data.remainingTime<=0){$("input[value='close_session']").parent().find("input[type='submit']").click();}
$("#remainingTime").html(data.remainingTime);
for (i=0;i<=numberAttended;i++){
	$("#"+data.studentIds[i]).addClass("attended");
	}
$(".active").click();
});
}