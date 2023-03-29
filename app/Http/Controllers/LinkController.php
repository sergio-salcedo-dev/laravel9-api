<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Exceptions\LinkExistsException;
use App\Http\Requests\CreateLinkRequest;
use App\Http\Requests\UpdateLinkRequest;
use App\Http\Resources\Links\ErrorMessageResource;
use App\Http\Resources\Links\LinkBasicResource;
use App\Http\Resources\Links\LinkResource;
use App\Http\Resources\MessageResource;
use App\Models\Link;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use PDOException;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class LinkController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $request->validate(['page' => 'numeric|integer|gt:0']);

        $links = QueryBuilder::for(Link::class)
            ->allowedFilters(['full_link', 'short_link'])
            ->allowedSorts('full_link', 'short_link', 'views', 'id')
            ->where('user_id', Auth::user()->id)
            ->paginate($request->get('perPage', 5));

        return response()->json($links);
    }

    public function show(Link $link): LinkResource
    {
        return new LinkResource($link);
    }

    public function store(CreateLinkRequest $request): JsonResource|Response
    {
        $link = $request->validated('link');
        $fullLink = Str::endsWith($link, '/') ? Str::replaceLast('/', '', $link) : $link;
        $fullLinkWithoutProtocol = preg_replace('/^http(s?):\/\/(www.?)/', '', $fullLink);

        if (!$fullLinkWithoutProtocol) {
            return (new ErrorMessageResource([
                'key' => 'link',
                'message' => 'Domain is required.',
            ]))->response()->setStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $link = Link::where('full_link', $fullLink)->first();

            if ($link) {
                throw new LinkExistsException('Link already exists');
            }

            $link = Link::create([
                'full_link' => $fullLink,
                'short_link' => $fullLinkWithoutProtocol,
                'user_id' => Auth::user()->id,
            ]);

            return new LinkResource($link);
        } catch (Throwable $e) {
            $exceptionMessage = 'Something went wrong.';
            $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;

            if ($e instanceof PDOException || $e instanceof LinkExistsException) {
                $exceptionMessage = $e->getMessage();
                $statusCode = Response::HTTP_UNPROCESSABLE_ENTITY;
            }

            return (new ErrorMessageResource([
                'key' => 'link',
                'message' => $exceptionMessage,
            ]))->response()->setStatusCode($statusCode);
        }
    }

    public function update(UpdateLinkRequest $request, Link $link): LinkResource|JsonResponse
    {
        $theLink = $request->validated('link');
        $fullLink = Str::endsWith($theLink, '/') ? Str::replaceLast('/', '', $theLink) : $theLink;
        $fullLinkWithoutProtocol = preg_replace('/^http(s?):\/\/(www.?)/', '', $fullLink);

        if (!$fullLinkWithoutProtocol) {
            return (new ErrorMessageResource([
                'key' => 'link',
                'message' => 'Domain is required.',
            ]))->response()->setStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $link->update([
            'full_link' => $fullLink,
            'short_link' => $fullLinkWithoutProtocol,
        ]);
        $link->refresh();

        return new LinkResource($link);
    }

    public function destroy(Link $link): MessageResource
    {
        $link->delete();

        return new MessageResource('Link deleted successfully');
    }

    public function destroyAll(): MessageResource
    {
        Auth::user()->links()->delete();

        return new MessageResource('All links deleted successfully');
    }

    /** Remove the specified resource from storage. */
    public function search(string $shortLink): JsonResource
    {
        $links = Link::where('short_link', 'LIKE', "%$shortLink%")->whereUserId(Auth::user()->id)->get();

        return LinkBasicResource::collection($links);
    }
}
