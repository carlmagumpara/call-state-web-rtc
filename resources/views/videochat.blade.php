@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-10 col-md-offset-1">
            <div class="panel">
              <div class="panel-body">
                 <div id="callPage" class="call-page"> 
                    <video id="localVideo" autoplay muted></video> 
                    <video id="remoteVideo" autoplay></video>
                 </div>
              </div>
            </div>
        </div>
    </div>
@include('script')
@endsection
