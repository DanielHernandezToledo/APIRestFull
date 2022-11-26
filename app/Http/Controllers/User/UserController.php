<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\ApiController;
use App\Mail\UserCreated;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class UserController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(User $usuarios)
    {
        $usuarios = User::all();

        return $this->showAll($usuarios);
        // return $usuarios;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $rules = [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed',
        ];
        
        $this->validate($request, $rules);

        $campos = $request->all();
        $campos['password'] = bcrypt($request->password);
        $campos['verified'] = User::USUARIO_NO_VERIFICADO;
        $campos['verification_token'] = User::generarVerificationToken();
        $campos['admin'] = User::USUARIO_REGULAR;

        $usuario = User::create($campos);

        return $this->showOne($usuario);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        return $this->showOne($user);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {

        $rules = [
            'email' => 'email|unique:users,email,'.$user->id,
            'password' => 'min:6|confirmed',
            'admin' => 'in:'.User::USUARIO_ADMINISTRADOR.','.User::USUARIO_REGULAR,
        ];

        $this->validate($request, $rules);

        if ($request->has('name'))
        {
            $user->name = $request->name;
        }

        if ($request->has('email') && $user->email != $request->email)
        {
                $user->verified = User::USUARIO_NO_VERIFICADO;
                $user->verification_token = User::generarVerificationToken();
                $user->email = $request->email;

        }

        if($request->has('password'))
        {
            $user->password = bcrypt($request->password);
        }
        
        if($request->has('admin'))
        {
            if(!$user->esVerificado())
            {
                return $this->errorResponse('Solo los usuarios verificados pueden cambiar su valor a administrador', 409);
            }
            $user->admin = $request->admin;
        }

        if(!$user->isDirty())
        {
            return $this->errorResponse('Debe haber al menos un cambio para guardar', 422);
        }

        $user->save();

        return response()->json(['data' => $user], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        $user->delete();

        return response()->json(['data' => $user], 200);
    }

    public function verify($token)
    {
        $user = User::where('verification_token', $token)->firstOrFail();
        $user->verified = User::USUARIO_VERIFICADO;
        $user->verification_token = null;
        $user->save();

        return $this->showMessage('La cuenta ha sido verificada');
    }

    public function resend(User $user)
    {
        if($user->esVerificado()){
            return $this->errorResponse('Este usuario ya ha sido registrado', 409);
        }

        retry(5, function() use($user){
            Mail::to($user)->send(new UserCreated($user));
        }, 100);

        return $this->showMessage('El correo de verificación se ha reenviado');
    }
}
