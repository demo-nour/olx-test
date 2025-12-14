<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Ad;
use App\Models\Category;
use App\Models\CategoryField;
use App\Models\AdField;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdController extends Controller
{
    public function createAd(Request $request)
    {
        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'price'       => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id'
        ]);

        $category = Category::find($request->category_id);
        $fields = $category->fields()->with('options')->get();

        $priceField = $category->fields()->where('attribute', 'price')->first();

        if (!$priceField) {
            $priceField = CategoryField::where('attribute', 'price')->whereNull('category_id')->first();
        }

        if ($priceField) {
            if ($priceField->min_value !== null && $data['price'] < $priceField->min_value) {
                return response()->json([
                    'error' => "{$priceField->attribute} must be >= {$priceField->min_value}"
                ], 422);
            }

            if ($priceField->max_value !== null && $data['price'] > $priceField->max_value) {
                return response()->json([
                    'error' => "{$priceField->attribute} must be <= {$priceField->max_value}"
                ], 422);
            }
        }


        foreach ($fields as $field) {
            if ($field->is_required && !$request->has($field->attribute)) {
                return response()->json([
                    'error'     => "{$field->attribute} is required."
                ], 422);
            }

            if ($request->has($field->attribute)) {

                $value = $request->input($field->attribute);

                if ($field->value_type === 'enum') {
                    $allowed = $field->options->pluck('value')->toArray();
                    if (!in_array($value, $allowed)) {
                        return response()->json([
                            'error' => "{$field->attribute} must be one of: " . implode(', ', $allowed)
                        ], 422);
                    }
                }

                if (in_array($field->value_type, ['float', 'integer'])) {
                    if (!is_numeric($value)) {
                        return response()->json([
                            'error' => "{$field->attribute} must be a numeric value."
                        ], 422);
                    }

                    if ($field->min_value !== null && $value < $field->min_value) {
                        return response()->json([
                            'error' => "{$field->attribute} must be >= {$field->min_value}"
                        ], 422);
                    }

                    if ($field->max_value !== null && $value > $field->max_value) {
                        return response()->json([
                            'error' => "{$field->attribute} must be <= {$field->max_value}"
                        ], 422);
                    }
                }

                if ($field->value_type === 'string') {

                    if (!is_string($value)) {
                        return response()->json([
                            'error' => "{$field->attribute} must be a string."
                        ], 422);
                    }

                    if ($field->min_length !== null && strlen($value) < $field->min_length) {
                        return response()->json([
                            'error' => "{$field->attribute} must be at least {$field->min_length} characters."
                        ], 422);
                    }

                    if ($field->max_length !== null && strlen($value) > $field->max_length) {
                        return response()->json([
                            'error' => "{$field->attribute} must not exceed {$field->max_length} characters."
                        ], 422);
                    }
                }

                if ($field->value_type === 'enum_multiple') {

                    if (!is_array($value)) {
                        return response()->json([
                            'error' => "{$field->attribute} must be an array of values."
                        ], 422);
                    }

                    $allowed = $field->options->pluck('value')->toArray();

                    foreach ($value as $v) {
                        if (!in_array($v, $allowed)) {
                            return response()->json([
                                'error' => "{$field->attribute} contains invalid value: {$v}"
                            ], 422);
                        }
                    }

                    $request[$field->attribute] = implode(',', $value);
                }
            }
        }


        $ad = Ad::create([
            'title'       => $data['title'],
            'description' => $data['description'],
            'price'       => $data['price'],
            'category_id' => $category->id,
            'user_id'     => Auth::id(),
        ]);

        foreach ($fields as $field) {
            if ($request->has($field->attribute)) {
                AdField::create([
                    'ad_id'              => $ad->id,
                    'category_field_id'  => $field->id,
                    'value'              => $request->input($field->attribute)
                ]);
            }
        }

        return response()->json([
            'message' => 'Ad created successfully',
            'ad_id'   => $ad->id
        ]);
    }

    public function myAds()
    {
        $ads = Ad::where('user_id', Auth::id())->with('fields.categoryField')->paginate(3);

        return response()->json($ads);
    }

    public function singleAd($id)
    {
        $ad = Ad::with(['fields.categoryField', 'category'])->find($id);

        if (!$ad) {
            return response()->json(['message' => 'Ad not found'], 404);
        }

        return response()->json($ad);
    }
}
