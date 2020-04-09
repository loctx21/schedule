@extends('layouts.app')

@section('content')
@if ( session()->has('error') )
    <div class="alert alert-danger alert-dismissable">{{ session()->get('error') }}</div>
@endif
<script>
  window.fbAsyncInit = function() {
    FB.init({
      appId            : '131107417047878',
      autoLogAppEvents : true,
      xfbml            : true,
      version          : 'v6.0'
    });
  };
</script>
<script async defer src="https://connect.facebook.net/en_US/sdk.js"></script>
<div id="dashboard_index">
</div>
@endsection