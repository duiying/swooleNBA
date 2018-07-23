var wsUrl = "ws://193.112.38.71:8813";

    var websocket = new WebSocket(wsUrl);

    //实例对象的onopen属性
    websocket.onopen = function(evt) {
      // websocket.send("hello-sinwa");
      console.log("conected-swoole-success");
    }

    // 实例化 onmessage
    websocket.onmessage = function(evt) {
      push(evt.data);
      console.log("ws-server-return-data11:" + evt.data);
    }

    //onclose
    websocket.onclose = function(evt) {
      console.log("close");
    }
    //onerror

    websocket.onerror = function(evt, e) {
      console.log("error:" + evt.data);
    }

    function push(data) {
        console.log(1111);
      data = JSON.parse(data);
       var html='<div class="frame">';
    html+='<h3 class="frame-header">';
    html+='<i class="icon iconfont icon-shijian"></i>第'+data.type+'节 01：30';
    html+='</h3>';
    html+='<div class="frame-item">';
    html+='<span class="frame-dot"></span>';
    html+='<div class="frame-item-author">';
    if(data.logo){
        html+='<img src="'+data.logo+'" width="20px" height="20px" />';
    }
    html+=data.title;
    html+='</div>';
    html+='<p>'+data.content+'</p>';
    if(data.image){
        html+='<p><img src="'+data.image+'" width="40%" /></p>';
    }
    html+='</div>';
    html+='</div>';
    $('#match-result').prepend(html);
    }
