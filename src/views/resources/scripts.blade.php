<?php
    $file_name = isset($file_name) ? $file_name : 'app';

    $static_resources = ufa()->loadScripts();
    $main = ufa()->realPath($file_name, 'js');
    $main_ie = ufa()->realPath($file_name . '-ie', 'js');
    $params = ufa()->getParams();
?>

{{-- Load main script --}}
@if(ufa()->compatible_ie)
<!--[if IE]>
<script src="{{$main_ie}}" type="text/javascript"></script>
<![endif]-->
<!--[if ! IE]><!-->
<script src="{{$main}}" type="text/javascript"></script>
<!--<![endif]-->
@else
    <script src="{{$main}}" type="text/javascript"></script>
@endif

<script type="text/javascript">
    $.params ={!!json_encode($params)!!};
</script>

{{-- load external scripts --}}
@foreach($static_resources['external'] as $js_file)
    <script src="{{$js_file}}" type="text/javascript"></script>
@endforeach

{{-- load internal scripts --}}
@foreach($static_resources['internal'] as $js_file)
    <script src="{{$js_file}}" type="text/javascript"></script>
@endforeach