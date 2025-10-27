<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class ApiController extends Controller
{
    public function scholarships(Request $request, $id = null)
    {
        $userId = Session::get('user_id', 0);
        
        if (!$userId && !Session::get('is_admin')) {
            return response()->json([], 401);
        }

        if ($id) {
            // Individual scholarship
            if ($userId > 0) {
                $scholarship = DB::table('scholarships as s')
                    ->leftJoin('bookmarks as b', function($join) use ($userId) {
                        $join->on('b.scholarship_id', '=', 's.id')
                             ->where('b.user_id', $userId);
                    })
                    ->select('s.*', DB::raw('(b.id IS NOT NULL) as bookmarked'))
                    ->where('s.id', $id)
                    ->first();
            } else {
                $scholarship = DB::table('scholarships')->where('id', $id)->first();
            }
            
            if ($scholarship) {
                $scholarship = (array) $scholarship;
                if (isset($scholarship['bookmarked'])) {
                    $scholarship['bookmarked'] = (int) $scholarship['bookmarked'];
                }
                return response()->json($scholarship);
            }
            
            return response()->json(['error' => 'Scholarship not found'], 404);
        }

        // List scholarships
        $query = $request->get('q', '');
        
        $scholarships = DB::table('scholarships as s');
        
        if ($userId > 0) {
            $scholarships->leftJoin('bookmarks as b', function($join) use ($userId) {
                $join->on('b.scholarship_id', '=', 's.id')
                     ->where('b.user_id', $userId);
            })
            ->select('s.id', 's.title', 's.sponsor', 's.category', 's.image_path', DB::raw('(b.id IS NOT NULL) as bookmarked'));
        } else {
            $scholarships->select('s.id', 's.title', 's.sponsor', 's.category', 's.image_path');
        }
        
        if ($query) {
            $scholarships->where(function($q) use ($query) {
                $q->where('s.title', 'like', "%{$query}%")
                  ->orWhere('s.sponsor', 'like', "%{$query}%")
                  ->orWhere('s.category', 'like', "%{$query}%");
            });
        }
        
        $items = $scholarships->get()->map(function($item) {
            $item = (array) $item;
            if (isset($item['bookmarked'])) {
                $item['bookmarked'] = (int) $item['bookmarked'];
            }
            return $item;
        });
        
        return response()->json($items);
    }

    public function bookmarks(Request $request)
    {
        $userId = Session::get('user_id');
        if (!$userId) {
            return response()->json([], 401);
        }

        $items = DB::table('bookmarks as b')
            ->join('scholarships as s', 's.id', '=', 'b.scholarship_id')
            ->select('s.id', 's.title', 's.sponsor', 's.image_path', 'b.created_at')
            ->where('b.user_id', $userId)
            ->orderBy('b.created_at', 'desc')
            ->get()
            ->map(function ($row) { return (array) $row; });

        return response()->json($items);
    }

    public function toggleBookmark(Request $request)
    {
        $userId = Session::get('user_id');
        
        if (!$userId) {
            return response()->json([], 401);
        }

        $data = $request->json()->all();
        $scholarshipId = (int) ($data['scholarship_id'] ?? 0);
        $action = $data['action'] ?? '';

        if ($scholarshipId <= 0) {
            return response()->json(['error' => 'Invalid scholarship ID'], 400);
        }

        if ($action === 'toggle') {
            $exists = DB::table('bookmarks')
                ->where('user_id', $userId)
                ->where('scholarship_id', $scholarshipId)
                ->exists();

            if ($exists) {
                DB::table('bookmarks')
                    ->where('user_id', $userId)
                    ->where('scholarship_id', $scholarshipId)
                    ->delete();
                return response()->json(['success' => true, 'bookmarked' => false]);
            } else {
                DB::table('bookmarks')->insert([
                    'user_id' => $userId,
                    'scholarship_id' => $scholarshipId,
                    'created_at' => now()
                ]);
                return response()->json(['success' => true, 'bookmarked' => true]);
            }
        }

        return response()->json(['error' => 'Invalid action'], 400);
    }

    public function applications(Request $request)
    {
        $userId = Session::get('user_id');
        
        if ($request->isMethod('get')) {
            $scholarshipId = (int) $request->get('scholarship_id', 0);
            
            if ($scholarshipId > 0) {
                $applications = DB::table('bookmarks as b')
                    ->leftJoin('users as u', 'b.user_id', '=', 'u.id')
                    ->select('b.*', 'u.username', 'u.email', 'u.contact', 'b.created_at as applied_at')
                    ->where('b.scholarship_id', $scholarshipId)
                    ->orderBy('b.created_at', 'desc')
                    ->get()
                    ->map(function($item) {
                        $item = (array) $item;
                        $item['status'] = 'bookmarked';
                        return $item;
                    });
                    
                return response()->json($applications);
            }
            
            return response()->json([]);
        }

        // Admin status update
        if ($request->input('action') === 'update_status') {
            if (!Session::get('is_admin')) {
                return response()->json(['success' => false, 'error' => 'Admin access required']);
            }
            $applicationId = (int) $request->input('application_id', 0);
            $status = $request->input('status', '');
            if (!in_array($status, ['pending','approved','rejected'], true)) {
                return response()->json(['success' => false, 'error' => 'Invalid status']);
            }
            DB::table('applications')->where('id', $applicationId)->update(['status' => $status]);
            return response()->json(['success' => true, 'message' => 'Application status updated successfully']);
        }

        if (!$userId) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $scholarshipId = (int) $request->input('scholarship_id', 0);

        if ($scholarshipId <= 0) {
            return response()->json(['success' => false, 'error' => 'Invalid scholarship ID']);
        }

        $exists = DB::table('applications')
            ->where('user_id', $userId)
            ->where('scholarship_id', $scholarshipId)
            ->exists();

        if ($exists) {
            return response()->json(['success' => false, 'error' => 'You have already applied for this scholarship']);
        }

        DB::table('applications')->insert([
            'user_id' => $userId,
            'scholarship_id' => $scholarshipId,
            'applied_at' => now()
        ]);

        return response()->json(['success' => true, 'message' => 'Application submitted successfully!']);
    }

    public function notifications(Request $request)
    {
        $userId = Session::get('user_id');
        if (!$userId) { return response()->json([], 401); }

        $rows = DB::table('applications as a')
            ->join('scholarships as s', 's.id', '=', 'a.scholarship_id')
            ->select(
                's.title as scholarship_title',
                's.sponsor as scholarship_sponsor',
                'a.applied_at',
                'a.status'
            )
            ->where('a.user_id', $userId)
            ->orderBy('a.applied_at', 'desc')
            ->get()
            ->map(function ($r) { return (array) $r; });

        return response()->json($rows);
    }

    public function editScholarship(Request $request)
    {
        // Admin only
        if (!Session::get('is_admin')) {
            return response()->json(['error' => 'Admin access required'], 403);
        }

        if ($request->isMethod('get')) {
            $id = (int) $request->query('id', 0);
            if ($id <= 0) { return response()->json(['error' => 'Invalid scholarship ID']); }
            $row = DB::table('scholarships')->where('id', $id)->first();
            if (!$row) { return response()->json(['error' => 'Scholarship not found'], 404); }
            return response()->json((array) $row);
        }

        // POST: update or delete
        $action = $request->input('action', '');
        if ($action === 'delete') {
            $id = (int) $request->input('id', 0);
            if ($id <= 0) { return response()->json(['success' => false, 'error' => 'Invalid scholarship ID']); }
            DB::table('scholarships')->where('id', $id)->delete();
            return response()->json(['success' => true]);
        }

        $id = (int) $request->input('id', 0);
        $data = $request->validate([
            'title' => 'required|string',
            'description' => 'required|string',
            'sponsor' => 'nullable|string',
            'category' => 'required|string|in:Company,School,Organization,Government,Foundation,Non-Profit,Individual,Other',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'email' => 'nullable|email',
            'phone' => 'nullable|string'
        ]);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $ext = strtolower($file->getClientOriginalExtension());
            if (!in_array($ext, ['jpg','jpeg','png'])) {
                return response()->json(['success' => false, 'errors' => ['Only JPG and PNG images are allowed.']]);
            }
            $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $safe = preg_replace('/[^A-Za-z0-9-_]/', '_', $filename);
            $name = $safe . '_' . time() . '.' . $ext;
            $file->move(public_path('uploads'), $name);
            $data['image_path'] = 'uploads/' . $name;
        }

        DB::table('scholarships')->where('id', $id)->update($data);
        return response()->json(['success' => true, 'message' => 'Scholarship updated successfully']);
    }

    public function userProfile(Request $request)
    {
        $userId = Session::get('user_id');
        
        if (!$userId) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        if ($request->isMethod('post')) {
            // Handle profile updates
            $request->validate([
                'username' => 'required|string|min:3',
                'email' => 'required|email',
                'contact' => 'required|string',
                'password' => 'nullable|min:6',
                'profilePic' => 'nullable|image|max:5120' // 5MB
            ]);

            $data = [
                'username' => $request->input('username'),
                'email' => $request->input('email'),  
                'contact' => $request->input('contact'),
                'updated_at' => now()
            ];

            if ($request->filled('password')) {
                $data['password'] = bcrypt($request->input('password'));
            }

            if ($request->hasFile('profilePic')) {
                $file = $request->file('profilePic');
                $filename = uniqid() . '_' . time() . '.' . $file->getClientOriginalExtension();
                $path = $file->move(public_path('uploads'), $filename);
                $data['profile_picture'] = 'uploads/' . $filename;
            }

            DB::table('users')->where('id', $userId)->update($data);

            Session::put('username', $data['username']);
            Session::put('email', $data['email']);

            return response()->json(['success' => true]);
        }

        $user = DB::table('users')
            ->select('username', 'email', 'contact', 'profile_picture')
            ->where('id', $userId)
            ->first();

        return response()->json([
            'username' => $user->username ?? '',
            'email' => $user->email ?? '',
            'contact' => $user->contact ?? '',
            'profile_picture' => $user->profile_picture ?? ''
        ]);
    }
}