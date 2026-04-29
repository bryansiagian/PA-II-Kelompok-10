<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PostCategory;
use App\Models\Post;
use App\Models\Profile;
use App\Models\Contact;
use Illuminate\Support\Str;

class CmsSeeder extends Seeder {
    public function run(): void {
        // 1. Buat Kategori (Sebagai pengganti 'type')
        $catNews = PostCategory::create(['name' => 'Berita', 'created_by' => 1]);
        $catActivity = PostCategory::create(['name' => 'Kegiatan', 'created_by' => 1]);

        // 2. Buat Post Contoh (Dihubungkan ke ID Kategori)
        Post::create([
            'user_id' => 1,
            'post_category_id' => $catNews->id,
            'title' => 'Pembukaan Cabang Gudang Baru',
            'slug' => Str::slug('Pembukaan Cabang Gudang Baru'),
            'content' => 'Isi berita tentang pembukaan cabang...',
            'status' => 1, // Published
            'created_by' => 1
        ]);

        Post::create([
            'user_id' => 1,
            'post_category_id' => $catActivity->id,
            'title' => 'Bakti Sosial Kesehatan 2024',
            'slug' => Str::slug('Bakti Sosial Kesehatan 2024'),
            'content' => 'Isi laporan kegiatan bakti sosial...',
            'status' => 1, // Published
            'created_by' => 1
        ]);

        // 3. Profiles & Contacts (Tetap sama sesuai DBML)
        Profile::create(['key' => 'about', 'title' => 'Tentang Kami', 'content' => 'Isi about...', 'created_by' => 1]);
        Contact::create(['key' => 'phone', 'title' => 'Telepon', 'value' => '021-12345', 'created_by' => 1]);
    }
}