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
@include('script')
@endsection
