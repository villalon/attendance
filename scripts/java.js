$(document).ready(function(){
if (typeof courseid != 'undefined') {start();}

$("#select_all").change(function(e){
	if ($(this).is(":checked")){
		$(".generaltable tr:visible input[type='checkbox']").not(this).prop("checked",true);	
	}
	else{
		$(".generaltable tr:visible input[type='checkbox']").not(this).removeAttr("checked");
	}
});

$(".generaltable tr").not(".generaltable thead tr").click(function(e){
	if(!$(e.target).is(".generaltable input[type='checkbox']")){
		$(this).find("input[type='checkbox']").click();
	}
	else{
		totalBoxes = $(".generaltable input[type='checkbox']").not("#select_all").length;
		checkedBoxes = $(".generaltable input[type='checkbox']:checked").not("#select_all").length;
		if (totalBoxes==checkedBoxes){
			$("#select_all").prop("checked",true);
		}
		else{
			$("#select_all").removeAttr("checked");
		}
	}
});

$.tablesorter.addParser({ 
    id: 'unix_dates', 
    is: function(s) {return false;}, 
    format: function(s) {
    	s = s.split('value="');
    	if (s.length>1){s = s[0].split('"'); }
    	return s[0]; 
    }, 
    type: 'text' 
}); 

n = $(".generaltable th").length-1;
if (n==1){$(".generaltable").tablesorter({headers:{0:{sorter:'unix_dates'},1:{sorter: false},},sortList:[[0,0]]});}
if (n==2){$(".generaltable").tablesorter({headers:{0:{sorter:'unix_dates'},2:{sorter: false},},sortList:[[0,0]]});}
if (n==3){$(".generaltable").tablesorter({headers:{0:{sorter:'unix_dates'},3:{sorter: false}},sortList:[[0,0]]});}
if (n==4){$(".generaltable").tablesorter({headers:{0:{sorter:'unix_dates'},4:{sorter: false}},sortList:[[0,0]]});}
if (n==5){$(".generaltable").tablesorter({headers:{0:{sorter:'unix_dates'},5:{sorter: false}},sortList:[[0,0]]});}


$("#filter input").bind('keyup',function(){
	str = $("#filter input").val().toLowerCase();
	rows = $(".generaltable").attr("sort_by").split(" ");
	$(".generaltable tr").not(".generaltable thead tr").each(function() {
	rowString = '';
	for (i=0;i<=rows.length;i++){
		rowString += $(this).find("td:eq("+rows[i]+")").html()+" ";	
	}
	n = rowString.toLowerCase().search(str);
	if (n==-1){$(this).hide();}
	else {$(this).show();}	
});	
});

});

function getAttended(){
if ($("#sessionStatus span").html()==". . . "){$("#sessionStatus span").html("");}
else {$("#sessionStatus span").html($("#sessionStatus span").html()+". ");}
$.post('ajax/getAttended.php',{courseid:courseid},function(data){
data = jQuery.parseJSON(data);
numberAttended = data.studentIds.length;
$("#percentage span").html(data.percentage);
$("#numberAttended span").html(numberAttended);
if (data.remainingTime<=0){$("input[value='close_session']").parent().find("input[type='submit']").click();}
$("#remainingTime").html(data.remainingTime);
for (i=0;i<=numberAttended;i++){
	$(".generaltable input[value='"+data.studentIds[i]+"']").parent().parent().addClass("attended");
	$(".generaltable input[value='"+data.studentIds[i]+"']").parent().html(data.string);
	}
});
}

var interval;

function start(){
interval = setInterval(function(){getAttended()},1000);
}