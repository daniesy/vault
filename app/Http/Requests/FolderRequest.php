<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FolderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        $folder_id = $this->route('folder')?->id; // For update scenario

        return [
            'name' => ['required', 'max:255', Rule::unique('folders')->ignore($folder_id)->where(fn ($query) => $query->where('user_id', $this->user()->id))],
            'parent_id' => 'exists:folders,id',
        ];
    }
}
