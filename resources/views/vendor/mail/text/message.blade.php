@component('mail::layout')
    {{-- Header --}}
    @slot('header')
        @component('mail::header', ['url' => env('APP_FRONTEND_URL')])
            {{ env('BUSINESS_NAME') }}
        @endcomponent
    @endslot

    {{-- Body --}}
    {{ $slot }}

    {{-- Subcopy --}}
    @isset($subcopy)
        @slot('subcopy')
            @component('mail::subcopy')
                {{ $subcopy }}
            @endcomponent
        @endslot
    @endisset

    {{-- Footer --}}
    @slot('footer')
        @component('mail::footer')
            © {{ date('Y') }} {{ env('BUSINESS_NAME') }}. @lang('All rights reserved.')
        @endcomponent
    @endslot
@endcomponent
