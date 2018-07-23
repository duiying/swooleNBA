var wsUrl = "ws://193.112.38.71:8812";

    var websocket = new WebSocket(wsUrl);

    //实例对象的onopen属性
    websocket.onopen = function(evt) {
      // websocket.send("hello-sinwa");
      console.log("conected-swoole-success");
    }

    // 实例化 onmessage
    websocket.onmessage = function(evt) {
      push11(evt.data);
      console.log("ws-server-return-data22:" + evt.data);
    }

    //onclose
    websocket.onclose = function(evt) {
      console.log("close");
    }
    //onerror

    websocket.onerror = function(evt, e) {
      console.log("error:" + evt.data);
    }

    function push11(data) {
      data = JSON.parse(data);
      var html='<div class="comment">';
      html+='<span>'+data.user+' </span>';
      html+='<span>'+data.content+'</span>';
      html+='</div>';
      $('#comment-first').remove();
      $('#discuss-box').val('');
      $('#comments').append(html);
    }
