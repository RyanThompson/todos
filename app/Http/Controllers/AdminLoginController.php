<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminLoginController extends Controller
{
    public function show()
    {
        return view('admin.login');
    }

    public function store(Request $request)
    {
        $state = $this->state();
        $email = $state['ADMIN_EMAIL'] ?? '';
        $password = $state['ADMIN_PASSWORD'] ?? '';

        if ($email === '' || $password === '' || ! hash_equals($email, (string) $request->input('email')) || ! Hash::check((string) $request->input('password'), $password)) {
            return back()->withErrors(['email' => 'Those credentials do not match.'])->onlyInput('email');
        }

        $request->session()->put('env_admin', true);

        return redirect('/admin');
    }

    /**
     * @return array<string, string>
     */
    protected function state(): array
    {
        $path = base_path('.env.state');
        if (! is_file($path)) {
            return [];
        }

        $values = [];
        foreach (file($path, FILE_IGNORE_NEW_LINES) ?: [] as $line) {
            if ($line === '' || str_starts_with(trim($line), '#') || ! str_contains($line, '=')) {
                continue;
            }
            [$key, $value] = explode('=', $line, 2);
            $values[trim($key)] = trim($value);
        }

        return $values;
    }
}
