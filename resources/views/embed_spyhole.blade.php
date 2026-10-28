<script type="text/javascript">
    window.spyholeConfig = {
        storeUrl: '{!! route('spyhole.store-entry') !!}',
        samplingRate: {{ config('laravel-spyhole.min_sampling_rate') }},
        xsrf: '{!! csrf_token() !!}',
        idValue: '{{ $idValue ?? 'spyhole-id' }}',
        type: '{{ $type ?? '' }}',
    };
    window.spyholeDom = {
        domSent: false,
        currentPage: {
            recording: null,
        }
    };
    window.spyholeEvents = [];
</script>
<input type="hidden" id="{{ $idValue ?? 'spyhole-id' }}" value="">
<script type="text/javascript" defer src="{!! asset('/vendor/laravel-spyhole/rrweb.min.js') !!}"></script>
<script type="text/javascript" defer src="{!! asset('/vendor/laravel-spyhole/recording-handler.js') !!}"></script>
