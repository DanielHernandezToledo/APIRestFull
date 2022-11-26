@component('mail::message')
# Introduction

The body of your message.

@component('mail::button', ['url' => ''])
Button Text
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
Hola {{$user->name}}
Has cambiado tu correo electrónico. Verifica tu nuevo correo:

@component('mail::message')
Hola {{$user->name}}
Has cambiado tu correo electrónico. Verifica tu nuevo correo:

@component('mail::button', ['url' => route('verify', $user->verification_token)])
Confirmar
@endcomponent

Gracias,<br>
{{ config('app.name') }}
@endcomponent
