<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AuthController extends Controller
{
    // =========================
    // Register user (public)
    // =========================
    public function register(Request $req)
    {
        $req->validate([
            'name' => 'required|string|min:6|max:20',
            'user_name' => 'required|string|max:255|unique:users,user_name',
            'email' => 'nullable|email|max:255|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'role' => 'required|string|in:admin,teacher,student',
            'active' => 'required|string|in:yes,no',
            'password' => [
                'required',
                'string',
                'min:6',
                'max:15',
                // 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z\d]).+$/',
                'confirmed',
            ],
        ], [
            'name.min' => 'Full Name must be at least 6 characters.',
            'name.max' => 'Full Name may not be greater than 20 characters.',
            'user_name.unique' => 'This username is not available. Try another one.',
            'email.unique' => 'This email is already registered.',
            'password.min' => 'Password must be at least 6 characters.',
            'password.max' => 'Password may not be greater than 15 characters.',
        ]);

        if ($req->role === 'admin' && User::where('role', 'admin')->exists()) {
            return back()->withErrors(['role' => 'Only one admin account is allowed.'])->withInput();
        }

        User::create([
            'name' => $req->name,
            'user_name' => $req->user_name,
            'email' => $req->email,
            'phone' => $req->phone,
            'role' => $req->role,
            'active' => $req->active,
            'password' => Hash::make($req->password),
        ]);

        return redirect('/login')->with('success', 'Registration successful!');
    }

    // =========================
    // Login user (public)
    // =========================
    public function login(Request $req)
    {
        $req->validate(['user_name' => 'required', 'password' => 'required']);

        $user = User::where('user_name', $req->user_name)->first();

        if (!$user) {
            return back()->with('error', 'User not found.');
        }

        if ($user->active === 'no') {
            return back()->with('error', 'Your account is blocked.');
        }

        if (Auth::attempt(['user_name' => $req->user_name, 'password' => $req->password])) {
            $req->session()->regenerate();

            ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => 'login',
                'description' => 'User logged in.',
                'ip_address' => $req->ip(),
            ]);

            return redirect('/dashboard');
        }

        return back()->with('error', 'Incorrect credentials.');
    }

    // =========================
    // Logout
    // =========================
    public function logout(Request $req)
    {
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'logout',
            'description' => 'User logged out.',
            'ip_address' => $req->ip(),
        ]);

        Auth::logout();
        $req->session()->invalidate();
        $req->session()->regenerateToken();

        return redirect('/login');
    }

    // =========================
    // Dashboard
    // =========================
    public function dashboard()
    {
        $users = User::latest()->paginate(10);
        $stats = $this->getDashboardStats();

        return view('dashboard', compact('users', 'stats'));
    }

    // =========================
    // Show create user form (Admin only)
    // =========================
    public function create()
    {
        $classes = SchoolClass::where('active', 'yes')->orderBy('name')->get();
        $subjects = Subject::with('schoolClass')->where('active', 'yes')->orderBy('name')->get();
        return view('create_user', compact('classes', 'subjects'));
    }

    // =========================
    // Store new user (Admin only)
    // =========================
    public function store(Request $req)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'user_name' => 'required|string|max:255|unique:users,user_name',
            'email' => 'nullable|email|max:255|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'role' => 'required|string|in:admin,teacher,student',
            'active' => 'required|string|in:yes,no',
            'password' => 'required|string|min:6|confirmed',
        ];

        if ($req->role === 'student') {
            $rules['admission_number'] = 'nullable|string|max:50|unique:users,admission_number';
            $rules['father_name'] = 'nullable|string|max:100';
            $rules['class_id'] = 'nullable|exists:classes,id';
            $rules['profile_picture'] = 'nullable|image|mimes:jpg,jpeg,png|max:2048';
        }

        if ($req->role === 'teacher') {
            $rules['job_history'] = 'nullable|string|max:2000';
            $rules['education'] = 'nullable|string|max:1000';
        }

        $req->validate($rules);

        $data = [
            'name' => $req->name,
            'user_name' => $req->user_name,
            'email' => $req->email,
            'phone' => $req->phone,
            'role' => $req->role,
            'active' => $req->active,
            'password' => Hash::make($req->password),
        ];

        if ($req->role === 'student') {
            $data['admission_number'] = $req->admission_number;
            $data['father_name'] = $req->father_name;
            $data['class_id'] = $req->class_id;

            if ($req->hasFile('profile_picture')) {
                $data['profile_picture'] = $req->file('profile_picture')->store('profile_pictures', 'public');
            }
        }

        if ($req->role === 'teacher') {
            $data['job_history'] = $req->job_history;
            $data['education'] = $req->education;
        }

        $user = User::create($data);

        if ($req->role === 'student') {
            $user->enrolledSubjects()->sync($req->input('subjects', []));
        }

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'user_created',
            'description' => 'Admin created user: ' . $req->name . ' (' . $req->role . ')',
            'ip_address' => $req->ip(),
        ]);

        return redirect('/dashboard')->with('success', 'User created successfully.');
    }

    // =========================
    // Edit user (Admin only)
    // =========================
    public function edit($id)
    {
        $user = User::with('enrolledSubjects')->findOrFail($id);
        $classes = SchoolClass::where('active', 'yes')->orderBy('name')->get();
        $subjects = Subject::with('schoolClass')->where('active', 'yes')->orderBy('name')->get();
        return view('edit', compact('user', 'classes', 'subjects'));
    }

    // =========================
    // Update user (Admin only)
    // =========================
    public function update(Request $req, $id)
    {
        $user = User::findOrFail($id);

        $rules = [
            'name' => 'required',
            'user_name' => 'required|unique:users,user_name,' . $id,
            'email' => 'nullable|email|max:255|unique:users,email,' . $id,
            'phone' => 'nullable|string|max:20',
            'role' => 'required|in:admin,teacher,student',
            'active' => 'required|in:yes,no',
        ];

        if ($req->role === 'student') {
            $rules['admission_number'] = 'nullable|string|max:50|unique:users,admission_number,' . $id;
            $rules['father_name'] = 'nullable|string|max:100';
            $rules['class_id'] = 'nullable|exists:classes,id';
            $rules['profile_picture'] = 'nullable|image|mimes:jpg,jpeg,png|max:2048';
        }

        if ($req->role === 'teacher') {
            $rules['job_history'] = 'nullable|string|max:2000';
            $rules['education'] = 'nullable|string|max:1000';
        }

        $req->validate($rules);

        $data = [
            'name' => $req->name,
            'user_name' => $req->user_name,
            'email' => $req->email,
            'phone' => $req->phone,
            'role' => $req->role,
            'active' => $req->active,
        ];

        if ($req->role === 'student') {
            $data['admission_number'] = $req->admission_number;
            $data['father_name'] = $req->father_name;
            $data['class_id'] = $req->class_id;

            if ($req->hasFile('profile_picture')) {
                if ($user->profile_picture) {
                    Storage::disk('public')->delete($user->profile_picture);
                }
                $data['profile_picture'] = $req->file('profile_picture')->store('profile_pictures', 'public');
            }
        }

        if ($req->role === 'teacher') {
            $data['job_history'] = $req->job_history;
            $data['education'] = $req->education;
        }

        $user->update($data);

        if ($req->role === 'student') {
            $user->enrolledSubjects()->sync($req->input('subjects', []));
        }

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'user_updated',
            'description' => 'Admin updated user: ' . $req->name,
            'ip_address' => $req->ip(),
        ]);

        return redirect('/dashboard')->with('success', 'User updated.');
    }

    // =========================
    // Delete user (Admin only)
    // =========================
    public function destroy($id)
    {
        if ($id == Auth::id()) {
            return back()->with('error', 'You cannot delete yourself.');
        }

        User::findOrFail($id)->delete();

        return back()->with('success', 'User deleted.');
    }

    public function toggleStatus(int $id): RedirectResponse
    {
        $user = User::findOrFail($id);
        if ($user->id === Auth::id()) {
            return back()->with('error', 'You cannot block yourself.');
        }
        $user->active = $user->active === 'yes' ? 'no' : 'yes';
        $user->save();

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'user_status_toggled',
            'description' => 'Admin toggled status for user: ' . $user->name . ' to ' . $user->active,
        ]);

        return back()->with('success', 'User status updated successfully.');
    }

    // =========================
    // Search (live search compatible)
    // =========================
    public function search(Request $req)
    {
        $term = $req->query('query', '');

        $users = User::where('role', 'student')
            ->where(function ($query) use ($term) {
                $query->where('name', 'like', "%$term%")
                    ->orWhere('user_name', 'like', "%$term%");
            })
            ->get();

        $stats = $this->getDashboardStats();

        return view('dashboard', compact('users', 'term', 'stats'));
    }

    public function toggleActive($id)
    {
        $user = User::findOrFail($id);

        if ($user->id === Auth::id()) {
            return back()->with('error', 'You cannot block your own account.');
        }

        $user->active = $user->active === 'yes' ? 'no' : 'yes';
        $user->save();

        $message = $user->active === 'yes'
            ? 'User account unblocked successfully.'
            : 'User account blocked successfully.';

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'user_block_toggle',
            'description' => 'Admin ' . ($user->active === 'yes' ? 'unblocked' : 'blocked') . ' user: ' . $user->name,
        ]);

        return back()->with('success', $message);
    }

    public function profile(): View
    {
        $user = Auth::user();
        return view('profile', compact('user'));
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = Auth::user();
        
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
        ];

        if ($user->role === 'student') {
            $rules['admission_number'] = 'nullable|string|max:50|unique:users,admission_number,' . $user->id;
            $rules['father_name'] = 'nullable|string|max:100';
        }

        if ($request->filled('password')) {
            $rules['password'] = 'required|string|min:6|confirmed';
        }

        $request->validate($rules);

        $user->name = $request->name;
        $user->email = $request->email;
        
        if ($user->role === 'student') {
            $user->admission_number = $request->admission_number;
            $user->father_name = $request->father_name;
        }

        if ($user->role === 'teacher') {
            $user->job_history = $request->job_history;
            $user->education = $request->education;
        }

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        if ($request->hasFile('profile_picture')) {
            if ($user->profile_picture) {
                Storage::disk('public')->delete($user->profile_picture);
            }
            $user->profile_picture = $request->file('profile_picture')->store('profile_pictures', 'public');
        }

        $user->save();

        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'profile_updated',
            'description' => 'User updated their own profile.',
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Profile updated successfully.');
    }

    private function getDashboardStats(): array
    {
        return [
            'total_users' => User::count(),
            'total_students' => User::where('role', 'student')->count(),
            'total_teachers' => User::where('role', 'teacher')->count(),
            'total_admins' => User::where('role', 'admin')->count(),
            'total_active' => User::where('active', 'yes')->count(),
            'total_blocked' => User::where('active', 'no')->count(),
            'total_classes' => SchoolClass::count(),
            'total_subjects' => Subject::count(),
            'assigned_subjects' => DB::table('teacher_subjects')->count(),
            'active_teachers' => User::where('role', 'teacher')->where('active', 'yes')->count(),
        ];
    }
}