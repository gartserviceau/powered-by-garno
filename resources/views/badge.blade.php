@once
  <script src="{{ asset('vendor/garno/powered-by-garno.js') }}" defer></script>
@endonce
<powered-by-garno
  theme="{{ $theme }}"
  size="{{ $size }}"
  target="{{ $target }}"
  @if ($href) href="{{ $href }}" @endif
  @if ($rel) rel="{{ $rel }}" @endif
><a href="{{ $href ?: 'https://garno.com.au/' }}" @if ($target === '_blank') target="_blank" rel="noopener" @endif><img src="{{ $image() }}" alt="Powered by Garno" width="140" height="24"></a></powered-by-garno>
