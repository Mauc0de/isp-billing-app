<?php

namespace App\Http\Controllers;

use App\Enums\RouterStatus;
use App\Enums\RouterSuspendMethod;
use App\Mikrotik\RouterClientFactory;
use App\Models\Router;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RouterController extends Controller
{
    public function index(): View
    {
        return view('routers.index', [
            'routers' => Router::query()->whereNull('archived_at')->latest()->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'location' => ['nullable', 'string', 'max:100'],
            'vpn_ip' => ['required', 'string', 'max:100'],
            'api_port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'username' => ['required', 'string', 'max:100'],
            'password' => ['required', 'string', 'max:255'],
            'suspend_method' => ['required', 'in:ppp_secret,address_list'],
            'address_list_name' => ['nullable', 'string', 'max:100'],
            'use_ssl' => ['sometimes', 'boolean'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $validated['use_ssl'] = $request->boolean('use_ssl');
        $validated['api_port'] = $validated['api_port'] ?? 8728;
        $validated['is_active'] = true;

        Router::query()->create($validated);

        return back()->with('success', 'Router berhasil ditambahkan.');
    }

    public function update(Request $request, Router $router): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'location' => ['nullable', 'string', 'max:100'],
            'vpn_ip' => ['required', 'string', 'max:100'],
            'api_port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'username' => ['required', 'string', 'max:100'],
            'password' => ['nullable', 'string', 'max:255'],
            'suspend_method' => ['required', 'in:ppp_secret,address_list'],
            'address_list_name' => ['nullable', 'string', 'max:100'],
            'use_ssl' => ['sometimes', 'boolean'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        $validated['use_ssl'] = $request->boolean('use_ssl');
        $router->update($validated);

        return back()->with('success', 'Router berhasil diperbarui.');
    }

    public function test(Router $router, RouterClientFactory $factory): RedirectResponse
    {
        try {
            $health = $factory->make($router)->health();

            $router->update([
                'status' => $health->reachable ? RouterStatus::Online : RouterStatus::Offline,
                'last_seen_at' => $health->reachable ? now() : $router->last_seen_at,
                'last_error' => $health->error,
            ]);

            $message = $health->reachable
                ? "Router {$router->name} online — {$health->identity}, versi {$health->version}."
                : "Router {$router->name} offline: {$health->error}";
        } catch (\Throwable $e) {
            $router->update(['status' => RouterStatus::Offline, 'last_error' => $e->getMessage()]);
            $message = "Gagal menghubungi {$router->name}: {$e->getMessage()}";
        }

        return back()->with('success', $message);
    }

    public function toggle(Router $router): RedirectResponse
    {
        $router->update(['is_active' => ! $router->is_active]);

        return back()->with('success', 'Status router '.$router->name.' diubah.');
    }

    public function destroy(Router $router): RedirectResponse
    {
        $router->update(['archived_at' => now(), 'is_active' => false]);

        return back()->with('success', 'Router diarsipkan.');
    }
}
