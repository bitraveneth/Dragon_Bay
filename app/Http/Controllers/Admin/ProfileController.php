<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\Permission as PermissionHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function edit(Request $request)
    {
        $user = $request->user();
        $user->loadMissing('employee');

        return view('admin.profile.edit', compact('user'));
    }

    public function update(Request $request)
    {
        $user = $request->user();
        $user->loadMissing('employee');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'current_password' => ['required_with:password', 'current_password'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'password' => ['nullable', 'string', 'min:8', 'max:191', 'confirmed'],
            'avatar' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('avatar') && $user->employee && ! PermissionHelper::can($user, 'control.employees')) {
            return back()
                ->withInput($request->except('avatar'))
                ->withErrors([
                    'avatar' => 'You do not have permission to update employee profile photos.',
                ]);
        }

        $user->name = $data['name'];
        $user->email = $data['email'];

        if (! empty($data['password'])) {
            $user->password = $data['password'];
        }

        $user->save();

        // Optional avatar update, reusing employee photo storage if linked.
        if ($request->hasFile('avatar') && $user->employee) {
            $employee = $user->employee;

            if ($employee->photo_path) {
                Storage::disk('public')->delete($employee->photo_path);
            }

            $employee->photo_path = $request->file('avatar')->store('employees/photos', 'public');
            $employee->save();
        }

        return redirect()
            ->route('admin.profile.edit')
            ->with('status', 'Profile updated successfully.');
    }
}
