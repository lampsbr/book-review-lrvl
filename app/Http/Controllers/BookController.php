<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Book;
use Illuminate\Support\Facades\Cache;

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $title = $request->input('title');
        $filter = $request->input('filter', '');
        $books = Book::when($title, fn($q, $title) => $q->title($title));
        $books = match($filter) {
            'plm' => $books->popularLastMonth(),
            'pl6m' => $books->popularLast6Months(),
            'hrlm' => $books->highestRatedLastMonth(),
            'hrl6m' => $books->highestRatedLast6Months(),
            default => $books->latest()->withAvgRating()->withReviewsCount(),
        };

        // $books = $books->get();
        $cacheKey = 'books_' . $filter . '_' . $title;
        $books = Cache::remember($cacheKey, 3600, fn() => $books->get());
        
        //return view('books.index', compact('books'));
        return view('books.index', ['books' => $books]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id)
    {
        $cacheKey = 'book_' . $id;
        $book = cache()->remember($cacheKey, 3600, fn() =>
            Book::with(['reviews' => fn ($query)=> $query->latest()])->withAvgRating()->withReviewsCount()->findOrFail($id)
        );
        return view('books.show', [ 'book' => $book ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
