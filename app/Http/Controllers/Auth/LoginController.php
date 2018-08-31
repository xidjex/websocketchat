<?php

namespace App\Http\Controllers\Auth;

use Auth;
use Redirect;
use Gate;
use App\User;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Foundation\Auth\RegistersUsers;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers, RegistersUsers {
        RegistersUsers::guard insteadof AuthenticatesUsers;
        AuthenticatesUsers::redirectPath insteadof RegistersUsers;
    }

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(LoginRequest $request)
    {
        $user = User::getIsExists($request->name, $request->email);

        if ($user) {
            if ($user->state == User::STATUS_BANNED) {
                return back()->withErrors(['banned' => ['Вы забанены администратором!']]);
            }

            if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
                return redirect('/');
            } else {
                return back()->withErrors(['password' => ['Неверный пароль.']]);
            }

        } else {

            if (User::isNameExists($request->name)) {
                return back()->withErrors(['name' => ['Такое имя уже занято.']]);
            }
             elseif (User::isEmailExists($request->email)) {
                 return back()->withErrors(['email' => ['Такой Email уже занят.']]);
             } else {
                $this->register($request);
            }
        }

        return redirect($this->redirectTo);
    }

    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
    }
}
