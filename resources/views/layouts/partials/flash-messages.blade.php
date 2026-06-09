@php
    $appFlashMessages = array_filter([
        'success' => session('success'),
        'error' => session('error'),
        'status' => session('status'),
    ]);
@endphp
@if(!empty($appFlashMessages))
<script type="application/json" id="app-flash-messages">@json($appFlashMessages)</script>
@endif
