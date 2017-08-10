@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-4">
            <div class="panel">
                <ul class="list-group">
                    @foreach($users as $user)
                        @if (Auth::user()->id == $user->id)
                            <li class="list-group-item">
                                <div class="row">
                                    <div class="col-md-9 col-xs-9">
                                        {{ $user->name }}  (You)  
                                    </div>
                                    <div class="col-md-3 col-xs-3">
                                        <span class="label label-default online-badge" id="user-{{ $user->id }}">
                                            Offline
                                        </span>
                                    </div>
                                </div>
                            </li>
                        @else
                            <li class="list-group-item">
                                <div class="row">
                                    <div class="col-md-9 col-xs-9">
                                        {{ $user->name }}    
                                    </div>
                                    <div class="col-md-3 col-xs-3">
                                        <span class="label label-default online-badge" id="user-{{ $user->id }}">
                                            Offline
                                        </span>
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
                    <h2>Profile</h2>
                </div>
            </div>
        </div>
    </div>
</div>


<script type="text/javascript">

    var socket = io('http://localhost:3000');

    function onUserConnected() {
        socket.emit('user connected', '{{ Auth::user()->id }}');
    }    

    onUserConnected();

    socket.on('show online', function(data){
        $('.online-badge')
            .addClass('label-default')
            .removeClass('label-success')
            .text('Offline')
        for (var i = 0; i < data.length; i++) {
            $('#user-'+data[i])
            .removeClass('label-default')
            .addClass('label-success')
            .text('Online')
        }
    });

</script>
@endsection