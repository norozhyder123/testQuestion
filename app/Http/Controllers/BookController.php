<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use JWTAuth;
use Validator;
use Storage;
use File;
use Str;
class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $books = Book::all();
        if($request->has('name'))
        {
            $books = Book::where('title', 'like', '%'.$request->name.'%')->get();
        }
        return response()->json(['data' => $books], 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
                        'title' => 'required|string',
                        'description' => 'required',
                        'cover_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
                        'price' => 'required',
                    ]);

        if($validator->fails())
        {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        if($request->has('cover_image'))
        {
           $attachment = $request->file('cover_image');
           $filename = Str::random(10).time().'.'.$attachment->extension();
           Storage::putFileAs('public/image', $attachment, $filename);
           $request->image_path = $filename;
        }
        // dd();
        $book = Book::create([
            'user_id' => JWTAuth::parseToken()->authenticate()->id,
            'title' => $request->title,
            'description' => $request->description,
            'cover_image' => $request->image_path,
            'price' => $request->price,
          ]);

        return response()->json(['data' => $book], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Book  $book
     * @return \Illuminate\Http\Response
     */
    public function show(Book $book)
    {
        return response()->json(['data' => $book], 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Book  $book
     * @return \Illuminate\Http\Response
     */
    public function edit(Book $book)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Book  $book
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Book $book)
    {
        if($book->user_id != JWTAuth::parseToken()->authenticate()->id)
        {
            return response()->json(['error' => 'Unathenticated'], 400);
        }
        $validator = Validator::make($request->all(), [
                        'title' => 'required|string',
                        'description' => 'required',
                        'price' => 'required|number',
                    ]);

        if($validator->fails())
        {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        if($request->has('cover_image'))
        {
            if(file_exists(storage_path().'\app\public\\'.$book->cover_image))
            {
                File::delete(storage_path().'\app\public\\'.$book->cover_image);
                $book->cover_image = '';
            }
            $attachment = $request->file('cover_image');
               $filename = Str::random(10).time().'.'.$attachment->extension();
               Storage::putFileAs('public/image', $attachment, $filename);
               $request->image_path = $filename;
        }
        $book = Book::update([
            'title' => $request->title,
            'description' => $request->description,
            'cover_image' => $request->image_path,
            'price' => $request->price,
          ]);

        return response()->json(['data' => $book], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Book  $book
     * @return \Illuminate\Http\Response
     */
    public function destroy(Book $book)
    {
        if($book->user_id != JWTAuth::parseToken()->authenticate()->id)
        {
            return response()->json(['error' => 'Unathenticated'], 400);
        }
        if(file_exists(storage_path().'\app\public\\'.$book->cover_image))
        {
            File::delete(storage_path().'\app\public\\'.$book->cover_image);
        }
        $book->delete();

        return response()->json(['data' => 'Book Un-published'], 200);
    }
}
