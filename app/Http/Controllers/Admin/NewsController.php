<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\Provider;
use App\Models\AppNotification;
use App\Http\Controllers\SendPushNotification;

class NewsController extends Controller
{
    public function index()
    {
        $news = Post::whereIn('type', ['NEWS', 'RSS_NEWS'])->latest()->get();
        return view('admin.news.index', compact('news'));
    }

    public function create()
    {
        return view('admin.news.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'source' => 'required|string|max:50',
            'external_link' => 'nullable|url',
        ]);

        $post = new Post();
        $post->user_id = \Auth::guard('admin')->id() ?? 1;
        $post->type = 'NEWS';
        $post->content = $request->title . "\n\n" . $request->content;
        $post->source = $request->source;
        $post->external_link = $request->external_link;
        $post->status = 'ACTIVE';

        if ($request->hasFile('media_url')) {
            $post->media_url = $request->file('media_url')->store('news');
        }

        $post->save();

        // Envoyer une notification push FCM a tous les utilisateurs
        try {
            (new SendPushNotification)->sendPushToAllUsers("Nouvelle Actualite : " . $request->title);
        } catch (\Exception $e) {
            \Log::error("Erreur Notification News : " . $e->getMessage());
        }

        // Persister le message admin dans le Centre d'Activites de chaque chauffeur actif
        try {
            $excerpt = \Str::limit($request->content, 120);
            Provider::where('status', 'approved')
                ->chunk(100, function ($providers) use ($request, $excerpt, $post) {
                    foreach ($providers as $provider) {
                        AppNotification::send(
                            $provider,
                            'Info Admin : ' . $request->title,
                            $excerpt,
                            'ADMIN_INFO',
                            (string) $post->id,
                            'POST'
                        );
                    }
                });
        } catch (\Exception $e) {
            \Log::error("Erreur AppNotification News providers : " . $e->getMessage());
        }

        return redirect()->route('admin.news.index')->with('flash_success', 'Actualite publiee avec succes et notifiee aux utilisateurs.');
    }

    public function edit($id)
    {
        $news = Post::findOrFail($id);
        return view('admin.news.edit', compact('news'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'content' => 'required|string',
            'source' => 'required|string|max:50',
        ]);

        $post = Post::findOrFail($id);
        $post->content = $request->content;
        $post->source = $request->source;
        $post->external_link = $request->external_link;

        if ($request->hasFile('media_url')) {
            $post->media_url = $request->file('media_url')->store('news');
        }

        $post->save();

        return redirect()->route('admin.news.index')->with('flash_success', 'Actualité mise à jour avec succès');
    }

    public function destroy($id)
    {
        $post = Post::findOrFail($id);
        $post->delete();

        return redirect()->route('admin.news.index')->with('flash_success', 'Actualité supprimée avec succès');
    }
}
