<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Profile;
use App\Models\Contact;
use App\Models\OrganizationStructure;
use App\Models\Gallery;
use App\Models\GalleryFile;
use App\Models\GeneralFile;
use App\Models\AuditLog;
use App\Models\Product; // Tambahkan ini jika ingin menampilkan produk di landing page
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class CmsController extends Controller {

    // --- Akses Publik (Landing Page) ---
    public function getLandingPageData() {
        try {
            return response()->json([
                'profiles' => Profile::where('active', 1)->get()->keyBy('key'),
                'contacts' => Contact::where('active', 1)->get()->keyBy('key'),

                'news' => Post::with('category')
                    ->whereHas('category', function($q) {
                        $q->where('name', 'LIKE', '%Berita%');
                    })
                    ->where('status', 1)
                    ->where('active', 1)
                    ->latest()
                    ->take(3)
                    ->get(),

                'activities' => Post::with('category')
                    ->whereHas('category', function($q) {
                        $q->where('name', 'LIKE', '%Kegiatan%');
                    })
                    ->where('status', 1)
                    ->where('active', 1)
                    ->latest()
                    ->take(3)
                    ->get(),

                'organization' => OrganizationStructure::where('active', 1)
                    ->orderBy('order', 'asc')
                    ->get(),

                'gallery' => Gallery::with('files')
                    ->where('active', 1)
                    ->latest()
                    ->take(6)
                    ->get(),

                // Opsional: Jika ingin mengambil produk terbaru untuk landing page
                'latest_products' => Product::with('category')
                    ->where('active', 1)
                    ->latest()
                    ->take(4)
                    ->get(),
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    // --- Manajemen Kategori Postingan (INI YANG KURANG) ---
    public function indexPostCategories() {
        try {
            // withCount untuk menghitung jumlah berita/kegiatan di tiap kategori
            return PostCategory::withCount(['posts' => function($q) {
                $q->where('active', 1);
            }])->where('active', 1)->latest()->get();
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function storePostCategory(Request $request) {
        $request->validate(['name' => 'required|string|unique:post_categories,name']);

        return DB::transaction(function() use ($request) {
            $category = PostCategory::create([
                'name' => $request->name,
                'active' => 1
            ]);

            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => "CMS: Menambah kategori postingan baru - {$category->name}"
            ]);

            return response()->json(['message' => 'Kategori berhasil ditambahkan'], 201);
        });
    }

    public function updatePostCategory(Request $request, $id) {
        $category = PostCategory::findOrFail($id);
        $request->validate(['name' => "required|string|unique:post_categories,name,{$id}"]);

        return DB::transaction(function() use ($request, $category) {
            $oldName = $category->name;
            $category->update(['name' => $request->name]);

            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => "CMS: Mengubah kategori postingan {$oldName} menjadi {$request->name}"
            ]);

            return response()->json(['message' => 'Kategori berhasil diperbarui']);
        });
    }

    public function deletePostCategory($id) {
        $category = PostCategory::findOrFail($id);

        // Proteksi: Jangan hapus jika masih ada berita di kategori ini
        if ($category->posts()->where('active', 1)->count() > 0) {
            return response()->json(['message' => 'Gagal! Kategori ini masih digunakan oleh beberapa postingan aktif.'], 422);
        }

        return DB::transaction(function() use ($category) {
            $name = $category->name;
            $category->update(['active' => 0]);

            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => "CMS: Menghapus kategori postingan - {$name}"
            ]);

            return response()->json(['message' => 'Kategori berhasil dihapus']);
        });
    }

    // --- Manajemen Berita & Kegiatan (Admin) ---
    public function indexPosts() {
        return Post::with(['category', 'author'])->where('active', 1)->latest()->get();
    }

    public function showPost($id) {
        return Post::with('category')->findOrFail($id);
    }

    public function storePost(Request $request) {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required',
            'post_category_id' => 'required|exists:post_categories,id',
            'image' => 'nullable|image|max:2048',
            'status' => 'required|in:0,1'
        ]);

        return DB::transaction(function() use ($request) {
            $path = $request->hasFile('image') ? $request->file('image')->store('posts', 'public') : null;

            $post = Post::create([
                'user_id' => auth()->id(),
                'post_category_id' => $request->post_category_id,
                'title' => $request->title,
                'slug' => Str::slug($request->title) . '-' . time(),
                'content' => $request->content,
                'image' => $path,
                'status' => $request->status,
                'active' => 1
            ]);

            $category = PostCategory::find($request->post_category_id);

            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => "CMS: Membuat postingan baru [{$category->name}] - {$request->title}"
            ]);

            return response()->json(['message' => 'Konten berhasil disimpan'], 201);
        });
    }

    public function updatePost(Request $request, $id) {
        $post = Post::findOrFail($id);

        $request->validate([
            'title' => 'required',
            'content' => 'required',
            'post_category_id' => 'required|exists:post_categories,id',
            'status' => 'required'
        ]);

        return DB::transaction(function() use ($request, $post) {
            $data = $request->only(['title', 'content', 'post_category_id', 'status']);

            if ($request->hasFile('image')) {
                if($post->image) Storage::disk('public')->delete($post->image);
                $data['image'] = $request->file('image')->store('posts', 'public');
            }

            $post->update($data);

            AuditLog::create(['user_id' => auth()->id(), 'action' => "CMS: Mengupdate postingan - {$post->title}"]);
            return response()->json(['message' => 'Konten diperbarui']);
        });
    }

    public function deletePost($id) {
        $post = Post::findOrFail($id);
        $title = $post->title;
        $post->update(['active' => 0]);

        AuditLog::create(['user_id' => auth()->id(), 'action' => "CMS: Mengarsipkan postingan - {$title}"]);
        return response()->json(['message' => 'Konten berhasil diarsipkan']);
    }

    // --- Manajemen Profil ---
    public function updateProfile(Request $request) {
        $request->validate([
            'key' => 'required',
            'title' => 'required',
            'content' => 'required'
        ]);

        return DB::transaction(function() use ($request) {
            Profile::updateOrCreate(
                ['key' => $request->key],
                ['title' => $request->title, 'content' => $request->content]
            );

            AuditLog::create(['user_id' => auth()->id(), 'action' => "CMS: Memperbarui profil " . strtoupper($request->key)]);
            return response()->json(['message' => 'Profil berhasil diperbarui']);
        });
    }

    // --- Manajemen Struktur Organisasi ---
    public function indexOrg() { return OrganizationStructure::orderBy('order')->get(); }

    public function storeOrg(Request $request) {
        $request->validate([
            'name' => 'required',
            'position' => 'required',
            'photo' => 'nullable|image|max:2048'
        ]);

        return DB::transaction(function() use ($request) {
            $path = $request->hasFile('photo') ? $request->file('photo')->store('org', 'public') : null;

            OrganizationStructure::create([
                'name' => $request->name,
                'position' => $request->position,
                'photo' => $path,
                'order' => $request->order ?? 0
            ]);

            AuditLog::create(['user_id' => auth()->id(), 'action' => "CMS: Menambah pengurus - {$request->name}"]);
            return response()->json(['message' => 'Data pengurus disimpan']);
        });
    }

    public function updateOrg(Request $request, $id) {
        $org = OrganizationStructure::findOrFail($id);
        $request->validate([
            'name' => 'required',
            'position' => 'required',
            'photo' => 'nullable|image|max:2048'
        ]);

        return DB::transaction(function() use ($request, $org) {
            $data = $request->only(['name', 'position', 'order']);
            if ($request->hasFile('photo')) {
                if ($org->photo) Storage::disk('public')->delete($org->photo);
                $data['photo'] = $request->file('photo')->store('org', 'public');
            }
            $org->update($data);
            AuditLog::create(['user_id' => auth()->id(), 'action' => "CMS: Mengupdate data pengurus {$org->name}"]);
            return response()->json(['message' => 'Data diperbarui']);
        });
    }

    public function deleteOrg($id) {
        $org = OrganizationStructure::findOrFail($id);
        if($org->photo) Storage::disk('public')->delete($org->photo);
        $org->delete();
        AuditLog::create(['user_id' => auth()->id(), 'action' => "CMS: Menghapus pengurus {$org->name}"]);
        return response()->json(['message' => 'Data dihapus']);
    }

    // --- Manajemen Galeri (Dukungan Edit Album) ---
    public function indexGalleries() {
        return Gallery::with(['files'])->where('active', 1)->latest()->get();
    }

    public function storeGallery(Request $request) {
        $request->validate(['title' => 'required|string|max:255']);
        return DB::transaction(function() use ($request) {
            $gallery = Gallery::create(['title' => $request->title]);

            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $file) {
                    $path = $file->store('galleries', 'public');
                    GalleryFile::create([
                        'gallery_id' => $gallery->id,
                        'file_path' => 'storage/'.$path,
                        'file_type' => Str::contains($file->getMimeType(), 'video') ? 'video' : 'image'
                    ]);
                }
            }

            AuditLog::create(['user_id' => auth()->id(), 'action' => "CMS: Membuat album galeri {$request->title}"]);
            return response()->json(['message' => 'Galeri dibuat']);
        });
    }

    public function updateGallery(Request $request, $id) {
        $gallery = Gallery::findOrFail($id);
        $request->validate(['title' => 'required|string|max:255']);

        return DB::transaction(function() use ($request, $gallery) {
            $gallery->update(['title' => $request->title]);

            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $file) {
                    $path = $file->store('galleries', 'public');
                    GalleryFile::create([
                        'gallery_id' => $gallery->id,
                        'file_path' => 'storage/'.$path,
                        'file_type' => Str::contains($file->getMimeType(), 'video') ? 'video' : 'image'
                    ]);
                }
            }

            AuditLog::create(['user_id' => auth()->id(), 'action' => "CMS: Mengupdate album {$gallery->title}"]);
            return response()->json(['message' => 'Galeri diperbarui']);
        });
    }

    public function deleteGallery($id) {
        $gallery = Gallery::findOrFail($id);
        $gallery->update(['active' => 0]);
        AuditLog::create(['user_id' => auth()->id(), 'action' => "CMS: Menghapus album {$gallery->title}"]);
        return response()->json(['message' => 'Galeri dihapus']);
    }

    public function deleteGalleryFile($id) {
        $file = GalleryFile::findOrFail($id);
        $storagePath = str_replace('storage/', '', $file->file_path);
        if (Storage::disk('public')->exists($storagePath)) {
            Storage::disk('public')->delete($storagePath);
        }
        $file->delete();
        return response()->json(['message' => 'Media dihapus']);
    }

    // --- Manajemen File & Kontak ---
    public function indexContacts() { return Contact::where('active', 1)->latest()->get(); }

    public function storeContact(Request $request) {
        $request->validate(['key' => 'required|unique:contacts,key', 'title' => 'required', 'value' => 'required']);
        return DB::transaction(function() use ($request) {
            $path = $request->hasFile('image') ? $request->file('image')->store('contacts', 'public') : null;
            Contact::create([
                'key' => strtolower($request->key), 'title' => $request->title,
                'value' => $request->value, 'image' => $path, 'active' => 1
            ]);
            AuditLog::create(['user_id' => auth()->id(), 'action' => "CMS: Menambah kontak {$request->title}"]);
            return response()->json(['message' => 'Kontak ditambah']);
        });
    }

    public function updateContact(Request $request, $id) {
        $contact = Contact::findOrFail($id);
        $data = $request->only(['key', 'title', 'value']);
        if ($request->hasFile('image')) {
            if($contact->image) Storage::disk('public')->delete($contact->image);
            $data['image'] = $request->file('image')->store('contacts', 'public');
        }
        $contact->update($data);
        AuditLog::create(['user_id' => auth()->id(), 'action' => "CMS: Mengupdate kontak {$contact->title}"]);
        return response()->json(['message' => 'Kontak diperbarui']);
    }

    public function deleteContact($id) {
        $contact = Contact::findOrFail($id);
        $contact->update(['active' => 0]);
        AuditLog::create(['user_id' => auth()->id(), 'action' => "CMS: Menghapus kontak {$contact->title}"]);
        return response()->json(['message' => 'Kontak dihapus']);
    }
}
