<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class Helper extends Model
{
    use HasFactory;

    static function CustomPaginate($perPage, $page, $data, $searchTerm = null)
    {

        $collection = $data->filter(function ($item) use ($searchTerm) {
            // Check if the 'name' key in the item contains the search term
            return stripos($item['user_name'], $searchTerm) !== false;
        });

        // Paginate the collection
        $paginatedData = $collection->forPage($page, $perPage);

        // Create a LengthAwarePaginator instance
        $paginator = new LengthAwarePaginator(
            $paginatedData,
            $collection->count(), // Total items
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        // Reset the keys of the paginated data to start from 0
        return $paginator->setCollection($paginatedData->values());
    }

    static function FindMyPosition($user_id, $data)
    {
        $meritPosition = 0;
        $previousScore = null;

        foreach ($data as $item) {
            if ($item->obtained_marks != $previousScore) {
                $meritPosition++;
            }
            $item['position'] = $meritPosition;
            $previousScore = $item->obtained_marks;
        }
        // user_id

        // Create a new collection with items that have the specified ID
        $newCollection = collect($data)->filter(function ($item) use ($user_id) {
            return $item['user_id'] == $user_id;
        });

        // If you only want the first matching item
        return $newCollection->first() ? $newCollection->first()->position : null;
    }

    static function SetPosition($data)
    {
        $meritPosition = 0;
        $previousScore = null;

        foreach ($data as $item) {
            if ($item->obtained_marks != $previousScore) {
                $meritPosition++;
            }
            $item['position'] = $meritPosition;
            $previousScore = $item->obtained_marks;
        }

        return $data;
    }
}
