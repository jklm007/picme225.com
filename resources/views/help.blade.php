@extends('user.layout.app')

@section('content')

	<div class="container" style="margin-top: 80px; ">
		<div class="col-sm-1"></div>
		<div class="col-sm-8">
			<?php echo Setting::get('help'); ?>
		</div>
		<div class="col-sm-1">
			
		</div>
	</div> 
@endsection