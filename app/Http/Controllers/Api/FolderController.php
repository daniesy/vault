<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\FolderRequest;
use App\Http\Resources\FileSearchResult;
use App\Http\Resources\Folder as FolderResource;
use App\Http\Resources\FolderSearchResult;
use App\Models\File;
use App\Models\Folder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class FolderController extends Controller
{
    private function processFileTypes(?string $fileTypesParam): array
    {
        if (!$fileTypesParam) {
            return [];
        }

        $normalizedTypes = str_replace(".lottie", "application/zip", $fileTypesParam);
        return explode(",", $normalizedTypes);
    }

    public function index(Request $request): FolderResource
    {
        $fileTypes = $this->processFileTypes($request->get('file-types'));
        $folder = $request->user()->folders()->firstOrCreate(['name' => '/', 'parent_id' => null]);
        $folder->load(['files' => fn ($query) => empty($fileTypes) ? $query : $query->whereIn('type', $fileTypes), 'children']);
        return new FolderResource($folder);
    }

    /**
     * Display a listing of the resource.
     */
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

// Store a newly created resource in storage.
    public function store(FolderRequest $request): JsonResponse
    {
        $folder = $request->user()->folders()->create($request->validated());

        return response()->json([
            'data' => [
                'message' => 'Successfully created folder!',
                'folder' => new FolderResource($folder)
            ]
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Folder $folder): FolderResource
    {
        $folder->load(['files', 'children']);

        return new FolderResource($folder);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Folder $folder)
    {
        //
    }

    // Update the specified resource in storage.
    public function update(FolderRequest $request, Folder $folder): JsonResponse
    {
        $folder->update($request->validated());

        return response()->json([
            'data' => [
                'message' => 'Successfully updated folder!',
                'folder' => new FolderResource($folder)
            ]
        ]);
    }

    public function destroy(Folder $folder): JsonResponse
    {
        $folder->delete();

        return response()->json([
            'data' => [
                'message' => 'Successfully deleted folder!'
            ]
        ]);
    }

    public function search(Request $request): JsonResponse
    {
        $query = $request->get('q');
        $fileTypes = $request->get('file-types');

        if ($fileTypes) {
            $fileTypes = explode(",", str_replace(".lottie", "application/zip", $fileTypes));
        }

        $folderResults =  Folder::where('name', 'like', "%$query%")->get();

        $filesSearch = File::where('name', 'like', "%$query%");
        if ($fileTypes) {
            $filesSearch->whereIn('type', $fileTypes);
        }
        $fileResults = $filesSearch->with('folder')->get();


        $parentFolders = $this->loadFolders($folderResults, $fileResults);
        $folderResults = $this->addFolders($folderResults, $parentFolders);
        $fileResults = $this->addFoldersToFiles($fileResults, $parentFolders);

        return response()->json([
            'data' => [
                'folders' => FolderSearchResult::collection($folderResults),
                'files' => FileSearchResult::collection($fileResults),
            ]
        ]);
    }

    protected function loadFolders(Collection $folderResults, Collection $fileResults): Collection
    {
        $ids = $folderResults->map(fn (Folder $folder) => $folder->parent_ids)->flatten()
            ->merge($fileResults->map(fn (File $file) => $file->folder->parent_ids)->flatten())
            ->unique()
            ->values();


        return Folder::whereIn('id', $ids)->select(['id', 'name'])->get();
    }

    protected function addFolders(Collection $folderResults, Collection $parentFolders): Collection
    {
        return $folderResults->each(fn (Folder $folder) => $folder->parents = collect($folder->parent_ids)->map(fn ($id) => $parentFolders->first(fn (Folder $f) => $f->id === $id))->toArray());
    }

    protected function addFoldersToFiles(Collection $fileResults, Collection $parentFolders): Collection
    {
        return $fileResults->each(fn (File $file) => $file->folder->parents = collect($file->folder->parent_ids)->map(fn ($id) => $parentFolders->first(fn (Folder $f) => $f->id === $id))->toArray());
    }
}
