<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReceiverRequest;
use App\Models\Receiver;
use Illuminate\Http\Request;

class ReceiverController extends ApiController
{
    public function index()
    {
        $Receivers = Receiver::all();
        return $this->successResponse($Receivers, 'Receivers retrieved successfully.',200);
    }

    // POST /Receivers - Create a new Receiver
    public function store(ReceiverRequest $request)
    {
        // Validate request data
        $validatedData = $request->validated();

        // Handle logo file upload if it exists
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');

            // Generate a unique file name for the uploaded logo
            $fileName = time() . '_' . preg_replace('/\s+/', '_', $file->getClientOriginalName());
            $filePath = $file->storeAs('logos', $fileName, 'public'); // Store file in 'storage/app/public/logos'

            // Add the file path to the validated data
            $validatedData['logo'] = $filePath;
        }

        // Create a new Receiver with the validated data
        $Receiver = Receiver::create($validatedData);

        // Return a success response with the created Receiver
        return $this->successResponse($Receiver, 'Receiver created successfully.', 201);
    }

    // GET /Receivers/{id} - Show a single Receiver
    public function show($id)
    {
        $Receiver = Receiver::find($id);
        if (!$Receiver) {
            return $this->errorResponse('Receiver not found.', 404);
        }
        return $this->successResponse($Receiver, 'Receiver retrieved successfully.',200);
    }

    // PUT /Receivers/{id} - Update a Receiver
    public function update(ReceiverRequest $request, $id)
    {
        $Receiver = Receiver::find($id);
        if (!$Receiver) {
            return $this->errorResponse('Receiver not found.', 404);
        }

        $Receiver->update($request->validated());
        return $this->successResponse($Receiver, 'Receiver updated successfully.',200);
    }

    // DELETE /Receivers/{id} - Delete a Receiver
    public function destroy($id)
    {
        $Receiver = Receiver::find($id);
        if (!$Receiver) {
            return $this->errorResponse('Receiver not found.', 404);
        }

        $Receiver->delete();
        return $this->successResponse([], 'Receiver deleted successfully.',200);
    }
}
