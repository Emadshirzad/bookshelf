<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Log;

class BlogController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth.admin'),
        ];
    }

    /**
     * @OA\Get(
     *     path="/admin/blog",
     *     tags={"Admin Blog"},
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
     *                 @OA\Items(ref="#/components/schemas/BlogModel"),
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
     *     ),security={{"api_key": {}}}
     * )
     * Display the specified resource.
     */
    public function index()
    {
        return $this->success(Blog::latest()->whereIsVerified(1)->paginate(20));
    }

    /**
     * @OA\Post(
     *     path="/admin/blog",
     *     tags={"Admin Blog"},
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
     *                 property="description",
     *                 type="string",
     *                 description="description",
     *                 default="null",
     *                 example="writer Item",
     *             ),
     *             @OA\Property(
     *                 property="article",
     *                 type="string",
     *                 description="article",
     *                 default="null",
     *                 example=0,
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success Message",
     *         @OA\JsonContent(ref="#/components/schemas/BlogModel"),
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="an 'unexpected' error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorModel"),
     *     ),security={{"api_key": {}}}
     * )
     * Make a blog
     */
    public function store(Request $request)
    {
        $request->validate([
            'title'          => 'required',
            'description'    => 'required',
            'article'        => 'required'
        ]);

        try {
            $blog = Blog::create($request->all());
            return $this->success($blog);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return $this->error(__('messages.blog.notCreated'));
        }
    }

    /**
     * @OA\Post(
     *     path="/admin/blog/{id}/picture",
     *     tags={"Admin Blog"},
     *     summary="MakeOneItem",
     *     description="make one Item",
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
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="picture",
     *                     description="Item",
     *                     type="file",
     *                     format="file"
     *                 )
     *             )
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
     *     ),
     *     security={{"api_key": {}}}
     * )
     * upload image blog
     */
    public function upload(Request $request, int $id)
    {
        $request->validate([
            'picture' => 'required|file|image',
        ]);

        try {
            $blog = Blog::findOrFail($id);
            $image = $request->picture;
            $imageName = time() . '-' . str()->random(32) . '.' . $image->getClientOriginalExtension();
            $image->storeAs('public/blogs', $imageName);
            $blog->picture = $imageName;
            $blog->save();
            return $this->success(['image uploaded successfully']);
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }    return $this->error(__('messages.blog.imageDontUpload'));
    }

    /**
     * @OA\Get(
     *     path="/admin/blog/{id}",
     *     tags={"Admin Blog"},
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
     *         @OA\JsonContent(ref="#/components/schemas/BlogModel"),
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="an ""unexpected"" error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorModel"),
     *     ),security={{"api_key": {}}}
     * )
     * Display the specified resource.
     */
    public function show(Int $id)
    {
        try {
            $blog = Blog::findOrFail($id);
            return response()->json($blog);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return $this->error(__('messages.blog.notFound'));
        }
    }

    /**
     * @OA\Put(
     *     path="/admin/blog/{id}",
     *     tags={"Admin Blog"},
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
     *                 property="description",
     *                 type="string",
     *                 description="description",
     *                 default="null",
     *                 example="writer Item",
     *             ),
     *             @OA\Property(
     *                 property="article",
     *                 type="string",
     *                 description="article",
     *                 default="null",
     *                 example="price Item",
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success Message",
     *         @OA\JsonContent(ref="#/components/schemas/BlogModel"),
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
            'title'        => 'required',
            'description'  => 'required',
            'article'      => 'required',
        ]);

        try {
            $blog = Blog::findOrFail($id);
            $blog->update($request->all());
            return response()->json($blog);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return $this->error(__('messages.blog.notUpdated'));
        }
    }

    /**
     * @OA\Delete(
     *     path="/admin/blog/{id}",
     *     tags={"Admin Blog"},
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
            $blog = Blog::findOrFail($id);
            $blog->delete();
            $id = $blog->id;
            return response()->json("blog $id deleted");
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return $this->error(__('messages.blog.notDeleted'));
        }
    }

    /**
     * @OA\Get(
     *     path="/admin/blog/unConfirmed",
     *     tags={"Admin Blog"},
     *     summary="getUnConfirmedItem",
     *     description="get un confirmed Item",
     *     @OA\Response(
     *         response=200,
     *         description="Success Message",
     *         @OA\JsonContent(ref="#/components/schemas/BlogModel"),
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="an ""unexpected"" error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorModel"),
     *     ),security={{"api_key": {}}}
     * )
     * Display the specified resource.
     */
    public function unConfirmed()
    {
        return $this->success(Blog::whereVerified(0)->get());
    }

    /**
     * @OA\Put(
     *     path="/admin/blog/{id}/verify",
     *     tags={"Admin Blog"},
     *     summary="VerifyOneItem",
     *     description="Verify one Item",
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
    public function verifyBlog(int $id)
    {
        try {
            $blog = Blog::findOrFail($id);
            $blog->verified = true;
            $blog->save();
            return response()->json(["blog $id verified"]);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return $this->error(__('messages.blog.notVerify'));
        }
    }
}
