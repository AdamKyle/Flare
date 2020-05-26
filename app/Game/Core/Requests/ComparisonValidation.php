<?php

namespace App\Game\Core\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ComparisonValidation extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'item_to_equip_type' => 'required|in:weapon,body,shield,leggings,sleeves,helmet,gloves,ring,spell-healing,spell-damage,artifact',
            'slot_id'            => 'required',
        ];
    }

    public function messages() {
        return [
            'item_to_equip_type.required' => 'Error. Invalid Input.',
            'item_to_equip_type.in'       => 'Error. Invalid Input.',
            'slot_id.required'            => 'Error. Invalid Input.',
        ];
    }
}