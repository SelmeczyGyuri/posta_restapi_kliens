<?php

namespace App\Http\Controllers;

use Illuminate\Http\Client\Response;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Http;

class CountiesController extends Controller
{
    private const FIELDS = ['name', 'crest_url'];

    public function index(Request $request)
    {
        $response = Http::api()->get('/counties', $request->only(['search', 'sort_by', 'sort_dir']));

        if ($response->failed()) {
            session()->now('error', 'Hiba történt az adatok lekérdezése során.');

            return view('counties.index', [
                'entities' => [],
                'search' => $request->get('search'),
            ]);
        }

        $isLogged = !empty(session('api_token'));

        return view('counties.index', [
            'entities' => $this->getCounties($response),
            'search' => $request->get('search'),
            'is_logged' => $isLogged,
        ]);
    }

    public function show(Request $request, $id)
    {
        $response = Http::api()->get("/counties/$id", $request->only(['sort_by', 'sort_dir', 'page']));

        if ($response->failed()) {
            return redirect()->route('counties.index')->with('error', 'A megye nem található.');
        }

        return view('counties.show', [
            'entity' => $this->getCounty($response),
            'cities' => $this->getCountyCities($response),
        ]);
    }

    public function create()
    {
        return view('counties.create');
    }

    public function store(Request $request)
    {
        $response = Http::api()->post('/counties', $request->only(self::FIELDS));

        if ($response->failed()) {
            return $this->failureRedirect($response, 'Nem sikerült létrehozni a megyét.');
        }

        return redirect()->route('counties.index')->with('success', 'Megye sikeresen létrehozva!');
    }

    public function edit($id)
    {
        $response = Http::api()->get("/counties/$id");

        if ($response->failed()) {
            return redirect()->route('counties.index')->with('error', 'A megye nem található.');
        }

        return view('counties.edit', ['entity' => $this->getCounty($response)]);
    }

    public function update(Request $request, $id)
    {
        $response = Http::api()->patch("/counties/$id", $request->only(self::FIELDS));

        if ($response->failed()) {
            return $this->failureRedirect($response, 'Nem sikerült módosítani.');
        }

        return redirect()->route('counties.index')->with('success', 'Megye sikeresen frissítve!');
    }

    public function destroy($id)
    {
        $response = Http::api()->delete("/counties/$id");

        if ($response->failed()) {
            return $this->failureRedirect($response, 'Nem sikerült törölni.');
        }

        return redirect()->route('counties.index')->with('success', 'Megye sikeresen törölve!');
    }

    private function getCounties($response)
    {
        $body = json_decode($response->body(), false);
        return $body->counties ?? [];
    }

    private function getCounty($response)
    {
        $body = json_decode($response->body(), false);
        return $body->county ?? null;
    }

    private function getCountyCities($response): LengthAwarePaginator
    {
        $page = json_decode($response->body(), false)->cities;

        return new LengthAwarePaginator(
            $page->data,
            $page->total,
            $page->per_page,
            $page->current_page,
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }

    private function failureRedirect(Response $response, string $message)
    {
        return match ($response->status()) {
            401 => redirect()->route('login'),
            422 => back()->withErrors($response->json('errors'))->withInput(),
            default => back()->withInput()->with('error', $message),
        };
    }
}
