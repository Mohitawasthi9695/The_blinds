<?php

namespace App\Http\Controllers;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    public function successResponse($data, $message = "Success", $status = 200)
    {
        return response()->json([
            'message' => $message,
            'data' => $data,
        ], $status);
    }
    public function paginationsuccessResponse($data, $message = "Success", $status = 200)
    {
        return response()->json([
            'message' => $message,
            'page_number' => $data->currentPage(),
            'page_size' => $data->perPage(),
            'total_record_count' => $data->total(),
            'total_pages' => $data->lastPage(),
            'has_more_pages' => $data->hasMorePages(),
            'next_page_url' => $data->nextPageUrl(),
            'prev_page_url' => $data->previousPageUrl(),
            'records' => $data->items(),
        ], $status);
    }
    
    
    public function errorResponse($message = "Error", $status = 400, $errors = null)
    {

        if ($errors) {
            $response['errors'] = $errors;
        }
        return response()->json([
            'message' => $message,
            'errors' => $errors,
        ], $status);
    }
    protected $user;
    protected $role;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->user = Auth::user();
            $this->role = $this->user ? $this->user->getRoleNames()->first() : null;
            return $next($request);
        });
    }

}
