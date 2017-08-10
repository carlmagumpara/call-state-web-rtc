@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-4">
            <div class="panel">
                <ul class="list-group">
                    @foreach($users as $user)
                        @if (Auth::user()->id != $user->id)
                            <li class="list-group-item">
                                <div class="row">
                                    <div class="col-md-9 col-xs-9">
                                        <div class="status" id="user-{{ $user->id }}"></div>
                                        {{ $user->name }}    
                                    </div>
                                    <div class="col-md-3 col-xs-3">
                                    <button class="btn btn-xs btn-block call-button" callee-id="{{ $user->id }}" callee-name="{{ $user->name }}">CALL</button>
                                    </div>
                                </div>
                            </li>
                        @endif
                    @endforeach
                </ul>
            </div>
        </div>
        <div class="col-md-8">
            <div class="panel panel-default">
                <div class="panel-heading">Dashboard</div>
                <div class="panel-body">
                    <h2>Hi! {{ Auth::user()->name }}</h2>

                     <div id="callPage" class="call-page"> 
                        <video id="localVideo" autoplay></video> 
                        <video id="remoteVideo" autoplay></video>
                     </div>

                </div>
            </div>
        </div>
    </div>

  <div class="modal fade" id="calleeModal" data-controls-modal="calleeModal"  data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-sm" role="document">
      <div class="modal-content">
        <div class="modal-body">
              <h4>
                  <span id="user-caller"></span> is calling...
              </h4>
            <div class="center call-buttons">
              <button type="button" class="btn btn-success accept-button btn-sm" data-dismiss="modal">Accept</button>
              <button type="button" class="btn btn-danger reject-button btn-sm" data-dismiss="modal">Reject</button>
            </div>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="callerModal" data-controls-modal="callerModal"  data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-sm" role="document">
      <div class="modal-content">
        <div class="modal-body">
            <h4>
                Calling <span id="user-callee"></span>...
            </h4>
            <div class="center call-buttons">
              <button type="button" class="btn btn-success cancel-button btn-sm" data-dismiss="modal">Cancel</button>
            </div>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="defaultModal" data-controls-modal="defaultModal"  data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-sm" role="document">
      <div class="modal-content">
        <div class="modal-body">
              <h5 id="messageInfo"></h5>
            <div class="center call-buttons">
              <button type="button" class="btn btn-info btn-sm" data-dismiss="modal">
                  Ok
              </button>
            </div>
        </div>
      </div>
    </div>
  </div>

</div>

<audio id="callingSignal" loop>
  <source src="{{ asset('audio/calling.ogg') }}"></source>
  <source src="{{ asset('audio/calling.mp3') }}"></source>
</audio>

<audio id="ringtoneSignal" loop>
  <source src="{{ asset('audio/ringtone.ogg') }}"></source>
  <source src="{{ asset('audio/ringtone.mp3') }}"></source>
</audio>
<script type="text/javascript">

$(document).ready(function(){
    
  window.WebSocket = window.WebSocket || window.MozWebSocket
  // var connection = new WebSocket('ws://localhost:5000')
  var connection = new WebSocket('wss://call-state-web-rtc-wss.herokuapp.com')
  var connected_user = null
  var title = $('title').text()

  if (!window.WebSocket) {
    console.log('Sorry, but your browser doesn\'t support WebSocket.')
  }

  connection.onopen = function () {
    var data = {
      type: 'subscribe',
      user_id: '{{ Auth::user()->id }}'
    }
    connection.send(JSON.stringify(data))
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
        break;
      case 'ringing':
        $('#callingSignal')[0].play()
        $('#callerModal').modal('show')
        $('#user-callee').text(json.callee_name)
        $('.cancel-button').attr('callee-id', json.callee_id)
        break;
      case 'user-is-offline':
        $('#callerModal').modal('hide')
        $('#defaultModal').modal('show')
        $('#messageInfo').text(json.message)
        break;
      case 'accepted':
        $('#callingSignal')[0].pause()
        $('#callerModal').modal('hide')

        if (json.caller_id === '{{ Auth::user()->id }}') {
          yourConn.createOffer(function (offer) { 
            var data = {
              type: 'offer',
              offer: offer,
              callee_id: connected_user
            }
            connection.send(JSON.stringify(data))
            yourConn.setLocalDescription(offer); 
          }, function (error) { 
             alert("Error when creating an offer"); 
          });
        }

        if (json.callee_id === '{{ Auth::user()->id }}') {
          connected_user = json.caller_id
        }

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
      default:
        console.log('[Frontend]: Opss... Something\'s wrong here.')
    }
  }

  navigator.webkitGetUserMedia({ video: true, audio: true }, function (myStream) { 
     stream = myStream; 
     $('#localVideo').attr('src', window.URL.createObjectURL(stream))
     var configuration = { 
        "iceServers": [{ "url": "stun:203.183.172.196:3478" }]
     }; 
     yourConn = new webkitRTCPeerConnection(configuration); 
     yourConn.addStream(stream); 
     yourConn.onaddstream = function (e) { 
        $('#remoteVideo').attr('src', window.URL.createObjectURL(e.stream)).attr('autoplay') 
     };
     yourConn.onicecandidate = function (event) { 
        if (event.candidate) { 
            var data = {
              type: 'candidate',
              wconnected_user: connected_user,
              candidate: event.candidate
            }
            connection.send(JSON.stringify(data))
        } 
     };  
  }, function (error) { 
     alert(error);
  }); 

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

})

</script>
@endsection
