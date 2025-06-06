<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\book_user;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Log;

class BookController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth', except: ['index', 'show']),
        ];
    }

    /**
     * @OA\Get(
     *     path="/book",
     *     tags={"Book"},
     *     summary="listAllItem",
     *     description="list all Item",
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *             default="1"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success Message",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="current_page",
     *                 type="integer",
     *                 format="int32",
     *                 description="Current page number"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/BookModel"),
     *                 description="List of item"
     *             ),
     *             @OA\Property(
     *                 property="first_page_url",
     *                 type="string",
     *                 format="uri",
     *                 description="First page URL"
     *             ),
     *             @OA\Property(
     *                 property="from",
     *                 type="integer",
     *                 format="int32",
     *                 description="First item number in the current page"
     *             ),
     *             @OA\Property(
     *                 property="last_page",
     *                 type="integer",
     *                 format="int32",
     *                 description="Last page number"
     *             ),
     *             @OA\Property(
     *                 property="links",
     *                 type="array",
     *                 @OA\Items(
     *                     oneOf={
     *                         @OA\Schema(ref="#/components/schemas/Previous"),
     *                         @OA\Schema(ref="#/components/schemas/Links"),
     *                         @OA\Schema(ref="#/components/schemas/Next")
     *                     }
     *                 ),
     *                 description="Links"
     *             ),
     *             @OA\Property(
     *                 property="last_page_url",
     *                 type="string",
     *                 format="uri",
     *                 description="Last page URL"
     *             ),
     *             @OA\Property(
     *                 property="next_page_url",
     *                 type="string",
     *                 format="uri",
     *                 description="Next page URL"
     *             ),
     *             @OA\Property(
     *                 property="path",
     *                 type="string",
     *                 description="Path"
     *             ),
     *             @OA\Property(
     *                 property="per_page",
     *                 type="integer",
     *                 format="int32",
     *                 description="Items per page"
     *             )
     *         ),
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="an ""unexpected"" error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorModel"),
     *     )
     * )
     * Display the specified resource.
     */
    public function index()
    {
        return $this->success(Book::latest()->whereVerified(1)->paginate(20));
    }

    /**
     * @OA\Post(
     *     path="/book",
     *     tags={"Book"},
     *     summary="MakeOneItem",
     *     description="make one Item",
     *     @OA\RequestBody(
     *         description="tasks input",
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="title",
     *                 type="string",
     *                 description="title",
     *                 example="Item name"
     *             ),
     *             @OA\Property(
     *                 property="author",
     *                 type="string",
     *                 description="author",
     *                 default="null",
     *                 example="writer Item",
     *             ),
     *             @OA\Property(
     *                 property="price",
     *                 type="integer",
     *                 description="price",
     *                 default="null",
     *                 example=0,
     *             ),
     *             @OA\Property(
     *                 property="sold_price",
     *                 type="integer",
     *                 description="sold price",
     *                 default="null",
     *                 example=0,
     *             ),
     *             @OA\Property(
     *                 property="count",
     *                 type="integer",
     *                 description="count",
     *                 default="null",
     *                 example=0,
     *             ),
     *             @OA\Property(
     *                 property="type",
     *                 type="string",
     *                 description="type book",
     *                 default="null",
     *                 example=0,
     *             ),
     *             @OA\Property(
     *                 property="release_date",
     *                 type="string",
     *                 description="release date",
     *                 default="null",
     *                 example="1403-2-24",
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success Message",
     *         @OA\JsonContent(ref="#/components/schemas/BookModel"),
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="an 'unexpected' error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorModel"),
     *     ),security={{"api_key": {}}}
     * )
     * Make a book
     */
    public function store(Request $request)
    {
        $request->validate([
            'title'       => 'required',
            'author'      => 'required',
            'price'       => 'required|numeric',
            'sold_price'  => 'required|numeric',
            'type'        => 'required|in:rent,sell,gift,ebook',
            'count'       => 'required|numeric',
            'release_date'=> 'required',
        ]);

        try {
            $book = Book::create($request->all());
            book_user::create([
                'user_id'     => $request->user_id,
                'book_id'     => $book->id,
                'price'       => $book->price,
                'sold_price'  => $request->sold_price,
                'count'       => $request->count,
                'type'        => $request->type,
                'release_date'=> $request->release_date,
            ]);
            return $this->success($book);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return $this->error(__('messages.book.notCreated')); 
        }
    }

    /**
     * @OA\Get(
     *     path="/book/{id}",
     *     tags={"Book"},
     *     summary="getOneItem",
     *     description="get One Item",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success Message",
     *         @OA\JsonContent(ref="#/components/schemas/BookModel"),
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="an ""unexpected"" error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorModel"),
     *     )
     * )
     * Display the specified resource.
     */
    public function show(Int $id)
    {
        try {
            $book = Book::findOrFail($id);
            if ($book->user_id !== auth()->id() && $book->verified == 0) {
                return $this->error(__('messages.Forbidden'), status:403);
            }
            return $this->success($book);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return $this->error(__('messages.book.notFound'));
        }
    }

    /**
     * @OA\Put(
     *     path="/book/{id}",
     *     tags={"Book"},
     *     summary="EditOneItem",
     *     description="edit one Item",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         description="tasks input",
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="title",
     *                 type="string",
     *                 description="title",
     *                 example="Item name"
     *             ),
     *             @OA\Property(
     *                 property="author",
     *                 type="string",
     *                 description="author",
     *                 default="null",
     *                 example="writer Item",
     *             ),
     *             @OA\Property(
     *                 property="price",
     *                 type="integer",
     *                 description="price",
     *                 default="null",
     *                 example=0,
     *             ),
     *             @OA\Property(
     *                 property="sold_price",
     *                 type="integer",
     *                 description="sold price",
     *                 default="null",
     *                 example=0,
     *             ),
     *             @OA\Property(
     *                 property="count",
     *                 type="integer",
     *                 description="count",
     *                 default="null",
     *                 example=0,
     *             ),
     *             @OA\Property(
     *                 property="type",
     *                 type="string",
     *                 description="type book",
     *                 default="null",
     *                 example="sell",
     *             ),
     *             @OA\Property(
     *                 property="release_date",
     *                 type="string",
     *                 description="release date",
     *                 default="null",
     *                 example="1403-2-24",
     *             ),
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 description="status book",
     *                 default="null",
     *                 example="accept",
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success Message",
     *         @OA\JsonContent(ref="#/components/schemas/BookModel"),
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="an 'unexpected' error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorModel"),
     *     ),security={{"api_key": {}}}
     * )
     * Update the specified resource in storage.
     */
    public function update(Request $request, Int $id)
    {
        $request->validate([
            'title'       => 'required',
            'author'      => 'required',
            'price'       => 'required|numeric',
            'sold_price'  => 'numeric',
            'count'       => 'numeric',
            'release_date'=> '',
        ]);

        try {
            $book = Book::findOrFail($id);
            if ($book->user_id !== auth()->id() || $book->verified == 1) {
                return $this->error(__('messages.Forbidden'), status:403);
            }
            $book->update($request->all());
            $manage = book_user::whereBookId($book->id);
            $manage->update([
                'price'       => $book->price,
                'sold_price'  => $request->sold_price,
                'count'       => $request->count,
                'release_date'=> $request->release_date,
            ]);
            return response()->json($book);
        } catch (Exception $e) {
            Log::error($e->getMessage());
<<<<<<< Updated upstream
            return $this->error(__('messages.book.notUpdated'));
=======
            return response()->json(['error' => 'Book not updated'], 400);
>>>>>>> Stashed changes
        }
    }

    /**
     * @OA\Delete(
     *     path="/book/{id}",
     *     tags={"Book"},
     *     summary="DeleteOneItem",
     *     description="Delete one Item",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success Message",
     *         @OA\JsonContent(ref="#/components/schemas/SuccessModel"),
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="an 'unexpected' error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorModel"),
     *     ),security={{"api_key": {}}}
     * )
     * Remove the specified resource from storage.
     */
    public function destroy(Int $id)
    {
        try {
            $book = Book::findOrFail($id);
            if ($book->user_id !== auth()->id() || $book->verified == 1) {
                return $this->error(__('messages.Forbidden'), status:403);
            }

            $book->delete();
            $id = $book->id;
            return response()->json("book $id deleted");
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return $this->error(__('messages.book.notDelete'));
        }
    }
}
