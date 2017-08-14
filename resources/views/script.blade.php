<script type="text/javascript">

  $(document).ready(function(){

    window.WebSocket = window.WebSocket || window.MozWebSocket
    // var connection = new WebSocket('ws://localhost:5000')
    var connection = new WebSocket('wss://call-state-web-rtc-wss.herokuapp.com')
    var connected_user = null
    var title = $('title').text()

    var roomId = ''

   var configuration = { 
      "iceServers": [{ "url": "stun:203.183.172.196:3478" }]
   }; 
   yourConn = new webkitRTCPeerConnection(configuration); 

    if (!window.WebSocket) {
      console.log('Sorry, but your browser doesn\'t support WebSocket.')
    }

    connection.onopen = function () {
      var data = {
        type: 'subscribe',
        user_id: '{{ Auth::user()->id }}'
      }
      connection.send(JSON.stringify(data))
      onConnected()
    }

    connection.onerror = function (error) {
      console.log('Sorry, but there\'s some problem with your connection or the server is down.')
    }

    connection.onmessage = function (message) {
      try {
        var json = JSON.parse(message.data)
      } catch (e) {
        console.log('This doesn\'t look like a valid JSON: ', message.data)
        return;
      }
      switch(json.type) {
        case 'subscribe':
          $('.status')
            .removeClass('online')
          for (var i = 0; i < json.data.length; i++) {
            $('#user-'+json.data[i])
              .addClass('online')
          }
          break;
        case 'calling':
          $('title').text(json.caller_name+' is calling...')
          $('#ringtoneSignal')[0].play()
          $('#calleeModal').modal('show')
          $('#defaultModal').modal('hide')
          $('#user-caller').text(json.caller_name)
          $('.accept-button').attr('caller-id', json.caller_id).attr('caller-name', json.caller_name)
          $('.reject-button').attr('caller-id', json.caller_id).attr('caller-name', json.caller_name)
          roomId = json.roomId
          break;
        case 'ringing':
          $('#callingSignal')[0].play()
          $('#callerModal').modal('show')
          $('#user-callee').text(json.callee_name)
          $('.cancel-button').attr('callee-id', json.callee_id)
          roomId = json.roomId
          break;
        case 'user-is-offline':
          $('#callerModal').modal('hide')
          $('#defaultModal').modal('show')
          $('#messageInfo').text(json.message)
          break;
        case 'accepted':
          $('#callingSignal')[0].pause()
          $('#callerModal').modal('hide')
          window.location = '/videochat/' + roomId
          break;
        case 'rejected':
          $('#callingSignal')[0].pause()
          $('#callerModal').modal('hide')
          $('#defaultModal').modal('show')
          $('#messageInfo').text(json.message)
          break;
        case 'not-answered':
          $('#callingSignal')[0].pause()
          $('#callerModal').modal('hide')
          $('#defaultModal').modal('show')
          $('#messageInfo').text(json.message)
          break;
        case 'missed-call':
          $('title').text(title)
          $('#ringtoneSignal')[0].pause()
          $('#calleeModal').modal('hide')
          $('#defaultModal').modal('show')
          $('#messageInfo').text(json.message)
          break;
        case 'cancelled':
          $('title').text(title)
          $('#ringtoneSignal')[0].pause()
          $('#calleeModal').modal('hide')
          $('#defaultModal').modal('show')
          $('#messageInfo').text(json.message)
          break;
        case 'user-busy':
          $('#callerModal').modal('hide')
          $('#defaultModal').modal('show')
          $('#messageInfo').text(json.message)
          break;
        case 'candidate':
          yourConn.addIceCandidate(new RTCIceCandidate(json.message))
          break;
        case 'offer':
          yourConn.setRemoteDescription(new RTCSessionDescription(json.message));
          yourConn.createAnswer(function (answer) {
            yourConn.setLocalDescription(answer);
            var data = {
              type: 'answer',
              caller_id: connected_user,
              answer: answer
            }
            connection.send(JSON.stringify(data))
          }, function (error) { 
            alert("Error when creating an answer"); 
          }); 
          break;
        case 'answer':
          yourConn.setRemoteDescription(new RTCSessionDescription(json.message));
          break;
        case 'get-room-users':
          setTimeout(function(){
            if (json.message[0] === '{{ Auth::user()->id }}') {
              yourConn.createOffer(function (offer) { 
                var data = {
                  type: 'offer',
                  offer: offer,
                  callee_id: json.message[1]
                }
                connection.send(JSON.stringify(data))
                yourConn.setLocalDescription(offer); 
                connected_user = json.message[1]
              }, function (error) { 
                 alert("Error when creating an offer"); 
              });
            }
            if (json.message[1] === '{{ Auth::user()->id }}') {
              connected_user = json.message[0]
            }
          }, 3000)
          setVideochat()
          break;
        default:
          console.log('[Frontend]: Opss... Something\'s wrong here.')
      }
    }

    $(window).on('beforeunload', function(){
      if($('#callerModal').hasClass('in')){
        $('.cancel-button').click();
      }
      if($('#calleeModal').hasClass('in')){
        $('.reject-button').click();
      }
    });

    $('.call-button').click(function(){
      var callee_id = $(this).attr('callee-id')
      var callee_name = $(this).attr('callee-name')
      var data = {
        type: 'calling',
        caller_id: '{{ Auth::user()->id }}',
        caller_name: '{{ Auth::user()->name }}',
        callee_id: callee_id,
        callee_name: callee_name
      }
      connection.send(JSON.stringify(data))
      connected_user = callee_id
    })

    $('.accept-button').click(function(){
      $('title').text(title)
      $('#ringtoneSignal')[0].pause()
      var caller_id = $(this).attr('caller-id')
      var caller_name = $(this).attr('caller-name')
      var data = {
        type: 'accepted',
        caller_id: caller_id,
        caller_name: caller_name,
        callee_id: '{{ Auth::user()->id }}',
        callee_name: '{{ Auth::user()->name }}'
      }
      connection.send(JSON.stringify(data))
    })

    $('.reject-button').click(function(){
      $('title').text(title)
      $('#ringtoneSignal')[0].pause()
      var caller_id = $(this).attr('caller-id')
      var caller_name = $(this).attr('caller-name')
      var data = {
        type: 'rejected',
        caller_id: caller_id,
        callee_id: '{{ Auth::user()->id }}',
        callee_name: '{{ Auth::user()->name }}'
      }
      connection.send(JSON.stringify(data)) 
    })

    $('.cancel-button').click(function(){
      $('#callingSignal')[0].pause()
      var callee_id = $(this).attr('callee-id')
      var data = {
        type: 'cancelled',
        callee_id: callee_id,
        caller_id: '{{ Auth::user()->id }}',
        caller_name: '{{ Auth::user()->name }}'
      }
      connection.send(JSON.stringify(data)) 
    })

    function onConnected(){
      var roomIdfromSession = '{{ isset($roomId) ? $roomId : null }}'
      if (roomIdfromSession != '') {
        var data = {
          type: 'get-room-users',
          roomId: roomIdfromSession
        }
        connection.send(JSON.stringify(data))
      }
    }

    function setVideochat(){
      navigator.webkitGetUserMedia({ video: true, audio: true }, function (myStream) { 
         stream = myStream; 
         $('#localVideo').attr('src', window.URL.createObjectURL(stream))
         yourConn.addStream(stream); 
         yourConn.onaddstream = function (e) { 
            $('#remoteVideo').attr('src', window.URL.createObjectURL(e.stream)) 
         };
         yourConn.onicecandidate = function (event) { 
            if (event.candidate) { 
              var data = {
                type: 'candidate',
                connected_user: connected_user,
                candidate: event.candidate
              }
              connection.send(JSON.stringify(data))
            } 
         };  
      }, function (error) { 
         alert(error);
      }); 
    }

  })

</script>