<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Category;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Log;

class CategoryController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth', except: ['index', 'show']),
        ];
    }

    // FIXME: fix pagination schema
    /**
    * @OA\Get(
    *     path="/categories",
    *     tags={"Category"},
    *     summary="listAllCategory",
    *     description="list all category",
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
    public function index()
    {
        return Category::latest()->paginate(20);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'          => 'required|string',
            'description'   => 'required|string',
        ]);
        $category = category::create($request->all());
        return response()->json($category);
    }

    /**
    * @OA\Get(
    *     path="/categories/{id}",
    *     tags={"Category"},
    *     summary="getOneCategory",
    *     description="get One category",
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
    *         @OA\JsonContent(ref="#/components/schemas/CategoryModel"),
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
            $category = category::findOrFail($id);
            $book = Book::where('category_id', $category->id)->get();
            return $this->success($book);
        } catch(Exception $e) {
            Log::error($e->getMessage());
            return $this->error('category not found');
        }
    }

    /**
    * @OA\Put(
    *     path="/categories/{id}",
    *     tags={"Category"},
    *     summary="EditOneCategory",
    *     description="edit one category",
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
    *                 property="name",
    *                 type="string",
    *                 description="name",
    *                 example="category name"
    *             ),
    *             @OA\Property(
    *                 property="description",
    *                 type="string",
    *                 description="description",
    *                 default="null",
    *                 example="writer description",
    *             ),
    *
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
            'name'          => 'required|string',
            'description'   => 'required|string',
        ]);

        try {
            $category = category::findOrFail($id);
            $category->update($request->all());
            return response()->json($category);
        } catch(Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['error' => 'category not update'], 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Int $id)
    {
        try {
            $category = category::findOrFail($id);
            $category->delete();
            $id = $category->id;
            return response()->json("category $id deleted");
        } catch(Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['error' => 'category not deleted'], 400);
        }
    }
}