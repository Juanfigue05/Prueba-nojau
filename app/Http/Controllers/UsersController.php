<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\UserRequest;

class UsersController extends Controller
{
    public function index()
    {
        $users = User::all();
        return view('users', compact('users'));
    }

    public function create()
    {
        return view('create_user');
    }

    public function store(UserRequest $request)
    {
        User::create($request->all());
        return redirect()->route('users.index')->with('success','Usuario creado Exitosamente.');
    }

    public function edit(User $user)
    {
        return view('edit_user', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $user->update($request->all());
        return redirect()->route('users.index')->with('success','Información de usuario editada con éxito.');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('users.index')->with('danger','Usuario eliminado correctamente.');
    }

    // ==================================================
    // MASS USER CREATION METHODS
    // ==================================================

    public function massCreate()
    {
        return response()->json(['error' => 'Mass creation view not implemented'], 501);
    }

    public function massStore(Request $request)
    {
        return response()->json(['error' => 'Mass store method not implemented'], 501);
    }

    public function massPreview(Request $request)
    {
        return response()->json(['error' => 'Mass preview method not implemented'], 501);
    }

    public function csvTemplate()
    {
        return response()->json(['error' => 'CSV template method not implemented'], 501);
    }

    // ==================================================
    // MASS USER DELETION METHODS
    // ==================================================

    public function massDelete()
    {
        return response()->json(['error' => 'Mass deletion view not implemented'], 501);
    }

    public function massDestroy(Request $request)
    {
        return response()->json(['error' => 'Mass destroy method not implemented'], 501);
    }
}
