<?php

namespace App\Services;


use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;

class CustomPagination
{
    public static function pagination($data,$perPage,$path)
        {
           
            $collection = collect($data);

           
            $page = Paginator::resolveCurrentPage('page');

            
            $currentPageItems = $collection->slice(($page - 1) * $perPage, $perPage)->all();

         
            $paginator = new LengthAwarePaginator($currentPageItems, count($collection), $perPage, $page);
            $paginator->withPath(url($path));

            return $paginator;
        }

    public static function pagination2($data, $perPage, $path)
    {
        // If $data is a collection, don't use collect() again
        if (!is_a($data, 'Illuminate\Support\Collection')) {
            // Create a collection only if it's not a collection already
            $collection = collect($data);
        } else {
            $collection = $data;
        }

        // Get the current page from the query string or default to 1
        $page = Paginator::resolveCurrentPage('page');

        // Slice the collection to get the items to display on the current page
        $currentPageItems = $collection->forPage($page, $perPage);

        // Create a LengthAwarePaginator instance
        $paginator = new LengthAwarePaginator($currentPageItems, $collection->count(), $perPage, $page, [
            'path' => url($path)
        ]);

        return $paginator;
    }

}
