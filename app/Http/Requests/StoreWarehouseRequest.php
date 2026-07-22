<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreWarehouseRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $warehouse = $this->route('warehouse');
        $warehouseId = is_object($warehouse) ? $warehouse->id : $warehouse;

        return [
            'code' => ['required', 'string', 'max:50', 'unique:warehouses,code,'.$warehouseId],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:central,transit,damaged,return'],
            'is_default' => ['boolean'],
            'status' => ['required', 'in:active,inactive'],
            'address' => ['nullable', 'string'],
            'manager_id' => ['nullable', 'exists:users,id'],
        ];
    }
}
