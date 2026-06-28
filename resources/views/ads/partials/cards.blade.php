@foreach($ads as $ad)
    @include('components.ad-card', ['ad' => $ad])
@endforeach
