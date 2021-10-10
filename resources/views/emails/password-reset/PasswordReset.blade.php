@component('mail::message')

<h1>Password Reset</h1>
<br>

<div>
<p>
Hello, here's the link to reset your password:

<a targe="_blank" href="{{$link}}">Link</a>

Good luck!
</p>
</div>

@endcomponent