@component('mail::message')

Hola {{$user->name}}
Gracias por crear una cuenta. Verifícla en el siguiente enlace:

@component('mail::button', ['url' => route('verify', $user->verification_token)])
Confirmar
@endcomponent

Gracias,<br>
{{ config('app.name') }}
@endcomponent
