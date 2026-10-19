<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Client\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Http;

class CitiesController extends Controller
{
    private const FIELDS = ['zip_code', 'city', 'id_county', 'population'];

    public function index(Request $request)
    {
        $response = Http::api()->get('/cities', $request->only(['search', 'county', 'sort_by', 'sort_dir', 'page']));

        if ($response->failed()) {
            session()->now('error', 'Hiba történt az adatok lekérdezése során.');

            return view('cities.index', [
                'entities' => new LengthAwarePaginator([], 0, 20),
                'counties' => [],
                'search' => $request->get('search'),
                'countyFilter' => $request->get('county'),
            ]);
        }

        return view('cities.index', [
            'entities' => $this->getCities($response),
            'counties' => $this->fetchCounties(),
            'search' => $request->get('search'),
            'countyFilter' => $request->get('county'),
        ]);
    }

    public function show($id)
    {
        $response = Http::api()->get("/cities/$id");

        if ($response->failed()) {
            return redirect()->route('cities.index')->with('error', 'A város nem található.');
        }

        return view('cities.show', [
            'entity' => json_decode($response->body(), false)->city,
        ]);
    }

    public function create()
    {
        return view('cities.create', ['counties' => $this->fetchCounties()]);
    }

    public function store(Request $request)
    {
        $response = Http::api()->post('/cities', $request->only(self::FIELDS));

        if ($response->failed()) {
            return $this->failureRedirect($response, 'Nem sikerült létrehozni a várost.');
        }

        return redirect()->route('cities.index')->with('success', 'Város sikeresen létrehozva!');
    }

    public function edit($id)
    {
        $response = Http::api()->get("/cities/$id");

        if ($response->failed()) {
            return redirect()->route('cities.index')->with('error', 'A város nem található.');
        }

        return view('cities.edit', [
            'entity' => json_decode($response->body(), false)->city,
            'counties' => $this->fetchCounties(),
        ]);
    }

    public function update(Request $request, $id)
    {
        $response = Http::api()->patch("/cities/$id", $request->only(self::FIELDS));

        if ($response->failed()) {
            return $this->failureRedirect($response, 'Nem sikerült módosítani.');
        }

        return redirect()->route('cities.index')->with('success', 'Város sikeresen módosítva!');
    }

    public function destroy($id)
    {
        $response = Http::api()->delete("/cities/$id");

        if ($response->failed()) {
            return $this->failureRedirect($response, 'Nem sikerült törölni.');
        }

        return redirect()->route('cities.index')->with('success', 'Város sikeresen törölve!');
    }

    private function getCities(Response $response): LengthAwarePaginator
    {
        $page = json_decode($response->body(), false);

        return new LengthAwarePaginator(
            $page->data,
            $page->total,
            $page->per_page,
            $page->current_page,
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }

    private function fetchCounties(): array
    {
        $response = Http::api()->get('/counties');

        return $response->successful()
            ? (json_decode($response->body(), false)->counties ?? [])
            : [];
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