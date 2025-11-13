<?php
namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Channel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileUploadController extends Controller
{
    private $allowedMimes = [
        'image' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'],
        'document' => ['pdf', 'doc', 'docx', 'txt', 'zip', 'rar']
    ];

    private $maxFileSize = 10240; 

    public function upload(Request $request, Channel $channel)
    {
        // Check if user can access this channel
        if (!auth()->user()->servers->contains($channel->server_id)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'file' => 'required|file|max:' . $this->maxFileSize,
            'message' => 'nullable|string|max:1000'
        ]);

        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $mimeType = $file->getMimeType();
        $fileSize = $file->getSize();

        // Determine file type
        $fileType = $this->getFileType($mimeType, $extension);

        // Generate unique filename
        $filename = Str::random(20) . '_' . time() . '.' . $extension;
        $path = $file->storeAs('uploads/' . $channel->id, $filename, 'public');

        // Create message with file
        $message = Message::create([
            'channel_id' => $channel->id,
            'user_id' => auth()->id(),
            'content' => $request->input('message', ''),
            'file_path' => $path,
            'file_name' => $originalName,
            'file_size' => $fileSize,
            'file_type' => $fileType,
            'mime_type' => $mimeType,
        ]);

        $message->load('user');

        return response()->json([
            'success' => true,
            'message' => $message,
            'file_url' => Storage::url($path)
        ]);
    }

    private function getFileType($mimeType, $extension)
    {
        if (strpos($mimeType, 'image/') === 0) {
            return 'image';
        }

        if (in_array($extension, $this->allowedMimes['document'])) {
            return 'document';
        }

        return 'other';
    }

    public function download(Message $message)
    {
        if (!auth()->user()->servers->contains($message->channel->server_id)) {
            abort(403);
        }

        if (!$message->hasFile()) {
            abort(404);
        }

        // ✅ Gunakan storage disk yang konsisten
        if (!Storage::disk('public')->exists($message->file_path)) {
            abort(404);
        }

        return Storage::disk('public')->download($message->file_path, $message->file_name);
    }


    public function preview(Message $message)
    {
        if (!auth()->user()->servers->contains($message->channel->server_id)) {
            abort(403);
        }

        if (!$message->hasFile() || !$message->isImage()) {
            abort(404);
        }

        // ✅ Gunakan storage disk yang benar
        if (!Storage::disk('public')->exists($message->file_path)) {
            abort(404);
        }

        // ✅ Return file response dengan headers yang benar
        return response()->file(
            Storage::disk('public')->path($message->file_path),
            [
                'Content-Type' => $message->mime_type,
                'Content-Disposition' => 'inline; filename="' . $message->file_name . '"'
            ]
        );
    }
}