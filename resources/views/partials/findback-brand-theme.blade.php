@php($brandColor = \App\Support\AdminSettings::brandColor($fbBrand))
<link rel="stylesheet" href="{{ asset('css/shared/configurable-brand.css') }}?v=1">
@if ($brandColor !== '')
    <style>:root { --fb-configured-brand: {{ $brandColor }}; }</style>
@endif
