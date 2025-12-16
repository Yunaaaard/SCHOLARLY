<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Session;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        if (!session()->get('is_admin')) {
            return redirect()->route('login');
        }
        return view('admin.dashboard');
    }

    public function showAddScholarship()
    {
        if (!session()->get('is_admin')) {
            return redirect()->route('login');
        }
        return view('admin.add-scholarship');
    }

    public function addScholarship(Request $request)
    {
        if (!session()->get('is_admin')) {
            return redirect()->route('login');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'sponsor' => 'nullable|string|max:255',
            'category' => 'required|in:Company,School,Organization,Government,Foundation,Non-Profit,Individual,Other',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:100',
            'image' => 'nullable|image|mimes:jpeg,png|max:5120',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $safeName = preg_replace('/[^A-Za-z0-9-_]/', '_', pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
            $fileName = $safeName . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads'), $fileName);
            $imagePath = 'uploads/' . $fileName;
        }

        DB::table('scholarships')->insert([
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'sponsor' => $request->input('sponsor', ''),
            'category' => $request->input('category'),
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'phone' => $request->input('phone'),
            'email' => $request->input('email'),
            'image_path' => $imagePath,
            'created_at' => now(),
        ]);

        return redirect()->route('admin.add-scholarship')->with('success', true);
    }

    public function studentManagement(Request $request)
    {
        if (!session()->get('is_admin')) {
            return redirect()->route('login');
        }

        $query = $request->input('q', '');
        $students = DB::table('users')
            ->select('id', 'first_name', 'last_name', 'username', 'email', 'contact');

        if ($query) {
            $students->where(function($q) use ($query) {
                $q->where('username', 'like', "%{$query}%")
                  ->orWhere('first_name', 'like', "%{$query}%")
                  ->orWhere('last_name', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%")
                  ->orWhere('contact', 'like', "%{$query}%");
            });
        }

        $students = $students->get();

        return view('admin.student-management', compact('students', 'query'));
    }

    public function deleteStudent(Request $request)
    {
        if (!session()->get('is_admin')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $id = (int) $request->input('id');
        if ($id <= 0) {
            return response()->json(['error' => 'Invalid student ID'], 400);
        }

        DB::table('users')->where('id', $id)->delete();

        return response()->json(['success' => true]);
    }

    public function getStudentDetails(Request $request)
    {
        if (!session()->get('is_admin')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $id = (int) $request->input('id');
        if ($id <= 0) {
            return response()->json(['error' => 'Invalid student ID'], 400);
        }

        $student = DB::table('users')
            ->where('id', $id)
            ->select('id', 'first_name', 'last_name', 'username', 'email', 'contact')
            ->first();

        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        $scholarships = DB::table('scholarships as s')
            ->join('bookmarks as b', 's.id', '=', 'b.scholarship_id')
            ->where('b.user_id', $id)
            ->select('s.title')
            ->get()
            ->pluck('title')
            ->toArray();

        $studentData = [
            'id' => $student->id,
            'first_name' => $student->first_name,
            'last_name' => $student->last_name,
            'username' => $student->username,
            'email' => $student->email,
            'contact' => $student->contact,
            'scholarships' => $scholarships
        ];

        return response()->json($studentData);
    }
}
