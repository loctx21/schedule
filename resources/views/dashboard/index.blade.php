@extends('layouts.app')

@section('content')
@if ( session()->has('error') )
    <div class="alert alert-danger alert-dismissable">{{ session()->get('error') }}</div>
@endif
<script>
 
</script>

<div id="dashboard_index">
</div>
@endsection