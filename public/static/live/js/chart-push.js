$(function(){
	$('#discuss-box').keydown(function(event) {
    //回车事件
	  if (event.keyCode == 13) { 
		  var text  = $(this).val();
      var url   = "http://193.112.38.71:8811?s=index/chart/index";
      var data  = {'content':text,'game_id':1};
      $.post(url, data, function(result) {
      	$(this).val("");
      }, 'json');
	  }
	});
});
