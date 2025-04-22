<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Upload;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;



class UploadController extends Controller
{
/**
 * @OA\Post(
 *     path="/api/upload",
 *     summary="Upload a file",
 *     description="Uploads a file to the server and stores it in S3.",
 *     tags={"Uploads"},
 *     security={{"sanctum":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(
 *                 required={"file"},
 *                 @OA\Property(
 *                     property="file",
 *                     type="string",
 *                     format="binary",
 *                     description="The file to upload"
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="File uploaded successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="File uploaded successfully")
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation error",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Validation Error"),
 *             @OA\Property(
 *                 property="errors",
 *                 type="object",
 *                 example={"file": {"The file field is required."}}
 *             )
 *         )
 *     )
 * )
 */
    public function upload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:jpg,png,pdf,docx|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $path = $file->storeAs('uploads', uniqid() . '_' . $originalName, 's3');

        Storage::disk('s3')->setVisibility($path, 'public');

        Upload::create([
            'filename' => $originalName,
            's3_path' => $path,
        ]);

        return response()->json(['message' => 'File uploaded successfully'], 201);
    }


/**
 * @OA\Get(
 *     path="/api/files",
 *     summary="Get all uploaded files",
 *     description="Returns a list of files from the server. Requires Bearer Token.",
 *     tags={"Uploads"},
 *     security={{"sanctum":{}}},  
 *     @OA\Response(
 *         response=200,
 *         description="List of uploaded files",
 *         @OA\JsonContent(
 *             type="array",
 *             @OA\Items(
 *                 @OA\Property(property="filename", type="string", example="sample.pdf"),
 *                 @OA\Property(property="url", type="string", format="url", example="https://bucket.s3.amazonaws.com/uploads/sample.pdf")
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Unauthorized",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Unauthenticated.")
 *         )
 *     )
 * )
 */
    public function index()
    {
        
        $files = Upload::all()->map(function ($file) {
            return [
                'filename' => $file->filename,
                'url' => Storage::disk('s3')->url($file->s3_path),
            ];
        });

        return response()->json($files);
    }
/**
 * @OA\Delete(
 *     path="/api/files/{filename}",
 *     summary="Delete a file",
 *     description="Deletes a file from the server and S3 storage.",
 *     tags={"Uploads"},
 *     security={{"sanctum":{}}},
 *     @OA\Parameter(
 *         name="filename",
 *         in="path",
 *         required=true,
 *         description="The name of the file to delete",
 *         @OA\Schema(type="string", example="document.pdf")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="File deleted successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="File deleted successfully")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="File not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="File not found")
 *         )
 *     )
 * )
 */
    public function destroy($filename)
    {
        $file = Upload::where('filename', $filename)->first();

        if (!$file) {
            return response()->json(['message' => 'File not found'], 404);
        }

        Storage::disk('s3')->delete($file->s3_path);
        $file->delete();

        return response()->json(['message' => 'File deleted successfully']);
    }
}


