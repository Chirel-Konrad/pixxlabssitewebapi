<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller {

    public function index() { return response()->json(User::all()); }

    public function store(Request $request) { return response()->json(User::create($request->all())); }

    public function show($id) { return response()->json(User::findOrFail($id)); }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,'.$id,
            'password' => 'sometimes|string|min:8|confirmed',
            'phone' => 'sometimes|string|max:20',
            'is_2fa_enable' => 'sometimes|boolean',
        ]);

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->fill($request->except('password'));
        $user->save();

        return response()->json($user->makeHidden(['password']));
    }


    public function destroy($id) {
        User::destroy($id);
        return response()->json(null, 204);
    }
}
