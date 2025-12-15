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
        $category = $request->get('category', '');
        $sort = $request->get('sort', '');
        
        $scholarships = DB::table('scholarships as s');
        
        // Handle sorting by applications count (bookmarks count)
        $needsAppCount = ($sort === 'apps_high');
        
        if ($needsAppCount) {
            $scholarships->leftJoin('bookmarks as app_count', 'app_count.scholarship_id', '=', 's.id');
        }
        
        if ($userId > 0) {
            $scholarships->leftJoin('bookmarks as b', function($join) use ($userId) {
                $join->on('b.scholarship_id', '=', 's.id')
                     ->where('b.user_id', $userId);
            });
            
            if ($needsAppCount) {
                $scholarships->select('s.id', 's.title', 's.sponsor', 's.category', 's.image_path', 's.start_date', 's.end_date', 
                    DB::raw('(b.id IS NOT NULL) as bookmarked'),
                    DB::raw('COUNT(DISTINCT app_count.id) as app_count'));
            } else {
                $scholarships->select('s.id', 's.title', 's.sponsor', 's.category', 's.image_path', 's.start_date', 's.end_date', 
                    DB::raw('(b.id IS NOT NULL) as bookmarked'));
            }
        } else {
            if ($needsAppCount) {
                $scholarships->select('s.id', 's.title', 's.sponsor', 's.category', 's.image_path', 's.start_date', 's.end_date',
                    DB::raw('COUNT(DISTINCT app_count.id) as app_count'));
            } else {
                $scholarships->select('s.id', 's.title', 's.sponsor', 's.category', 's.image_path', 's.start_date', 's.end_date');
            }
        }
        
        // Apply search filter
        if ($query) {
            $scholarships->where(function($q) use ($query) {
                $q->where('s.title', 'like', "%{$query}%")
                  ->orWhere('s.sponsor', 'like', "%{$query}%")
                  ->orWhere('s.category', 'like', "%{$query}%");
            });
        }
        
        // Apply category filter
        if ($category) {
            $scholarships->where('s.category', $category);
        }
        
        // Group by if sorting by applications
        if ($needsAppCount) {
            $scholarships->groupBy('s.id', 's.title', 's.sponsor', 's.category', 's.image_path', 's.start_date', 's.end_date');
            if ($userId > 0) {
                $scholarships->addSelect(DB::raw('MAX(CASE WHEN b.user_id = ' . (int)$userId . ' THEN 1 ELSE 0 END) as bookmarked'));
            }
        }
        
        // Apply sorting
        switch ($sort) {
            case 'title_asc':
                $scholarships->orderBy('s.title', 'asc');
                break;
            case 'title_desc':
                $scholarships->orderBy('s.title', 'desc');
                break;
            case 'start_new':
                $scholarships->orderBy('s.start_date', 'desc');
                break;
            case 'end_new':
                $scholarships->orderBy('s.end_date', 'desc');
                break;
            case 'apps_high':
                $scholarships->orderByRaw('COUNT(DISTINCT app_count.id) DESC');
                break;
            default:
                $scholarships->orderBy('s.created_at', 'desc');
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
        
        if ($id <= 0) {
            return response()->json(['success' => false, 'error' => 'Invalid scholarship ID']);
        }
        
        // Check if scholarship exists
        $exists = DB::table('scholarships')->where('id', $id)->exists();
        if (!$exists) {
            return response()->json(['success' => false, 'error' => 'Scholarship not found']);
        }
        
        try {
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
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false, 
                'error' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }

        // Handle image upload
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $ext = strtolower($file->getClientOriginalExtension());
            if (!in_array($ext, ['jpg','jpeg','png'])) {
                return response()->json(['success' => false, 'error' => 'Only JPG and PNG images are allowed.']);
            }
            $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $safe = preg_replace('/[^A-Za-z0-9-_]/', '_', $filename);
            $name = $safe . '_' . time() . '.' . $ext;
            $file->move(public_path('uploads'), $name);
            $data['image_path'] = 'uploads/' . $name;
        }

        // Don't include updated_at if column doesn't exist
        // Remove updated_at from data array if it was added
        unset($data['updated_at']);
        
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
                'first_name' => 'nullable|string|min:2',
                'last_name' => 'nullable|string|min:2',
                'email' => 'required|email',
                'contact' => 'required|string',
                'password' => 'nullable|min:6',
                'profilePic' => 'nullable|image|max:5120' // 5MB
            ]);

            $data = [
                'email' => $request->input('email'),  
                'contact' => $request->input('contact'),
            ];
            
            // Only update name fields if provided
            if ($request->filled('first_name') && $request->filled('last_name')) {
                $firstName = $request->input('first_name');
                $lastName = $request->input('last_name');
                $fullName = $firstName . ' ' . $lastName;
                
                $data['first_name'] = $firstName;
                $data['last_name'] = $lastName;
                $data['name'] = $fullName;
            }
            
            // Don't include updated_at as legacy table may not have it

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

            if (isset($data['first_name'])) {
                Session::put('first_name', $data['first_name']);
                Session::put('last_name', $data['last_name']);
            }
            Session::put('email', $data['email']);

            return response()->json(['success' => true]);
        }

        $user = DB::table('users')
            ->select('first_name', 'last_name', 'username', 'email', 'contact', 'profile_picture')
            ->where('id', $userId)
            ->first();

        return response()->json([
            'first_name' => $user->first_name ?? '',
            'last_name' => $user->last_name ?? '',
            'username' => $user->username ?? '',
            'email' => $user->email ?? '',
            'contact' => $user->contact ?? '',
            'profile_picture' => $user->profile_picture ?? ''
        ]);
    }
}