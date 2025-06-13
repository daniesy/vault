<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\FileRequest;
use App\Http\Requests\ImportFileRequest;
use App\Http\Requests\UpdateFileRequest;
use App\Http\Resources\File as FileResource;
use App\Http\Resources\FileSearchResult;
use App\Http\Resources\FolderSearchResult;
use App\Models\File;
use App\Models\Folder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class FileController extends Controller
{
    private function uploadFile(UploadedFile $file, string $userId): array
    {
        $name = $file->getClientOriginalName();
        $filename = sha1_file( $file->path() ) . "." . $file->getClientOriginalExtension();

        $path = $file->storeAs("public/files/$userId", $filename);
        $size = $file->getSize();
        $type = $file->getMimeType();

        return compact('name', 'path', 'type', 'size');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Folder $folder, FileRequest $request): JsonResponse
    {
        $this->authorize('update', $folder);
        $extra = $this->uploadFile($request->file('file'), $request->user()->id);

        $file = $request->user()->files()->create([
            ...$extra,
            "folder_id" => $folder->id,
        ]);

        return response()->json([
            'data' => [
                'message' => 'Successfully created file!',
                'file' => new FileResource($file)
            ]
        ]);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Folder $folder, File $file, UpdateFileRequest $request): JsonResponse
    {
        $this->authorize('update', $folder);
        $this->authorize('update', $file);

        $file->update($request->validated());

        return response()->json([
            'data' => [
                'message' => 'Successfully updated file!',
                'file' => new FileResource($file)
            ]
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Folder $folder, File $file): JsonResponse
    {
        $this->authorize('delete', $folder);
        $this->authorize('delete', $file);

        $file->delete();

        return response()->json([
            'data' => [
                'message' => 'Successfully deleted file!'
            ]
        ]);
    }

    /**
     * Download a file from a given URL.
     *
     * @param string $url The URL of the file to download.
     *
     * @return UploadedFile The downloaded file.
     */
    private function downloadFile(string $url): UploadedFile
    {
        $file = file_get_contents($url);
        $tmp = tempnam(sys_get_temp_dir(), 'file');
        file_put_contents($tmp, $file);

        return new UploadedFile($tmp, basename($url));
    }

    /**
     * @throws AuthorizationException
     */
    public function import(ImportFileRequest $request): JsonResponse
    {
        $url = $request->validated('url');
        // Download the file at the url
        $file = $this->downloadFile($url);

        $folderId = $request->get('folder');
        if ($folderId) {
            $folder = $request->user()->folders()->findOrFail($folderId);
            $this->authorize('update', $folder);
        } else {
            // If no folder is specified, use the root folder
            $folder = $request->user()->folders()->firstOrCreate(['name' => '/', 'parent_id' => null]);
        }


        // Upload the file to the storage
        $extra = $this->uploadFile($file, $request->user()->id);

        $file = $request->user()->files()->create([
            ...$extra,
            "folder_id" => $folder->id,
        ]);

        return response()->json([
            'data' => [
                'message' => 'Successfully imported the file!',
                'file' => new FileResource($file)
            ]
        ]);
    }


}
