
<style>

    .pagination-c span {
        display: inline-block;
        margin: 0 5px;
    }

    .pagination-c a {
        padding: 5px 10px;
        text-decoration: none;
        border: 1px solid #ccc;
        border-radius: 5px;
        color: #333;
    }

    .pagination-c a.active {
        background-color: #ffd900;
        color: #fff;
        border-color: #e1cc2b;
    }

    .pagination-c a.disabled {
        pointer-events: none;
        color: #999;
    }

</style>

@php
    $queryString = http_build_query(request()->all());
    $queryString = str_replace('page='.$data_links->currentPage(), '', $queryString);

    $currentPage = $data_links->currentPage(); // Current page number
    $lastPage = $data_links->lastPage(); // Total number of pages
    $nextPage = $currentPage < $lastPage ? $currentPage + 1 : $lastPage; // Calculate the next page

    // Determine the range of pages to display
    $startPage = max(1, $currentPage - 5); // Start 5 pages before the current page
    $endPage = min($lastPage, $currentPage + 4); // End 4 pages after the current page

@endphp

<tr class="table-footer">
    <td>
        <a class="{{ $currentPage == 1 ? 'disabled' : '' }}" href="{{ $data_links->url($currentPage - 1) }}">
            <i class="fa fa-arrow-left"></i> &nbsp; Previous
        </a>
    </td>
    <td colspan="{{isset($colspan) ? $colspan : '7'}}" class="text-center">
        <div class="pagination-c">
            @if ($startPage > 1)
                <span>
                    <a href="{{ $data_links->url(1) }}&{{ $queryString }}">1</a>
                </span>
                @if ($startPage > 2)
                    <span>...</span> <!-- Ellipsis if there's a gap -->
                @endif
            @endif

            @for ($i = $startPage; $i <= $endPage; $i++)
                <span>
                    <a href="{{ $data_links->url($i) }}&{{ $queryString }}" class="{{ $currentPage == $i ? 'active' : '' }}">{{ $i }}</a>
                </span>
            @endfor

            @if ($endPage < $lastPage)
                @if ($endPage < $lastPage - 1)
                    <span>...</span> <!-- Ellipsis if there's a gap -->
                @endif
                <span>
                    <a href="{{ $data_links->url($lastPage) }}&{{ $queryString }}">{{ $lastPage }}</a>
                </span>
            @endif
        </div>
    </td>
    <td class="text-end">
        <a href="{{ $data_links->url($nextPage) }}" class="{{ $currentPage == $lastPage ? 'disabled' : '' }}">
            Next &nbsp; <i class="fa fa-arrow-right"></i>
        </a>
    </td>
</tr>

