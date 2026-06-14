<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EventRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'session_id'   => ['required', 'string', 'max:36'],
            'event_type'   => ['required', 'string', 'in:page_view,niche_view,product_impression,product_click'],
            'niche_id'     => ['nullable', 'uuid', 'exists:niches,id'],
            'product_id'   => ['nullable', 'uuid', 'exists:products,id'],
            'store_id'     => ['nullable', 'uuid', 'exists:stores,id'],
            'utm_source'   => ['nullable', 'string', 'max:100'],
            'utm_medium'   => ['nullable', 'string', 'max:100'],
            'utm_campaign' => ['nullable', 'string', 'max:255'],
            'utm_content'  => ['nullable', 'string', 'max:255'],
            'referrer'     => ['nullable', 'string', 'max:500'],
            'metadata'     => ['nullable', 'array'],
        ];
    }
}
