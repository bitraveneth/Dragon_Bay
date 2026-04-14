<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $logs = AuditLog::with('user')
            ->when($request->query('action'), fn ($query, $action) => $query->where('action', 'like', '%' . $action . '%'))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('admin.audit.index', compact('logs'));
    }
}
