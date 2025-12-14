<?php

namespace App\Services\Olx;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Models\Category;
use App\Models\CategoryField;
use App\Models\CategoryFieldOption;

class OlxCategoryFieldService
{
    private string $baseUrl = "https://www.olx.com.lb/api";


    public function fetchCategoryField($categoryExternalId)
    {
        return Cache::remember('olx_category_fields_' . $categoryExternalId, now()->addHours(24), function () use ($categoryExternalId) {
            return Http::get("{$this->baseUrl}/categoryFields", [
                'categoryExternalIDs' => $categoryExternalId,
                'includeWithoutCategory' => 'true',
                'splitByCategoryIDs' => 'true',
                'flatChoices' => 'true',
                'groupChoicesBySection' => 'true',
                'flat' => 'true',
            ])->json();
        });
    }

    public function storeCategoryFields(int $externalCategoryId)
    {
        $category = Category::where('external_id', $externalCategoryId)->first();

        if (!$category) {
            dump("Category ($externalCategoryId) not found in DB.");
            return;
        }

        $fields = $this->fetchCategoryField($externalCategoryId);


        if (!is_array($fields)) {
            dump("Could not fetch fields for category ($externalCategoryId).");
            return;
        }

        if (!empty($fields['common_category_fields'])) {
            foreach ($fields['common_category_fields']['flatFields'] as $row) {
                $this->saveField($row, null);
            }
        }

        if (!empty($fields[$category['olx_id']])) {
            foreach ($fields[$category['olx_id']]['flatFields'] as $data) {
                $this->saveField($data, $category['id']);
            }
        }

        dump("Fields imported for category ($externalCategoryId).");
    }

    private function saveField(array $data, ?int $categoryId)
    {
        $field = CategoryField::updateOrCreate(
            ['external_id' => $data['id']],
            [
                'category_id' => $categoryId,
                'attribute'   => $data['attribute'] ?? null,
                'name'        => $data['name'] ?? '',
                'value_type'  => $data['valueType'] ?? null,
                'filter_type' => $data['filterType'] ?? null,
                'is_required' => $data['isMandatory'] ?? false,
                'min_value'   => $data['minValue'] ?? null,
                'max_value'   => $data['maxValue'] ?? null,
                'min_length'  => $data['minLength'] ?? null,
                'max_length'  => $data['maxLength'] ?? null,
            ]
        );


        if (!empty($data['choices'])) {
            foreach ($data['choices'] as $choice) {
                CategoryFieldOption::updateOrCreate(
                    [
                        'category_field_id' => $field->id,
                        'external_id'       => $choice['id'] ?? null,
                    ],
                    [
                        'value' => $choice['value'],
                        'label' => $choice['label'] ?? null,
                    ]
                );
            }

            $olxOptionIds = collect($data['choices'])->pluck('id')->filter()->toArray();
            CategoryFieldOption::where('category_field_id', $field->id)->whereNotIn('external_id', $olxOptionIds)->delete();
        }



        return $field;
    }
}
