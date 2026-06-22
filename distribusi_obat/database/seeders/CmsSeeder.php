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
        // 1. Pembuatan Master Kategori Post CMS
        $catNews = PostCategory::create(['name' => 'Berita', 'created_by' => 1]);
        $catActivity = PostCategory::create(['name' => 'Kegiatan', 'created_by' => 1]);
        $catResearch = PostCategory::create(['name' => 'Riset & Inovasi', 'created_by' => 1]);
        $catCollab = PostCategory::create(['name' => 'Kemitraan & Kolaborasi', 'created_by' => 1]);

        // 2. Pembuatan Post Artikel Lengkap (Published / Status = 1)
        Post::create([
            'user_id' => 1,
            'post_category_id' => $catCollab->id,
            'title' => 'Perkuat Riset Medis, Yayasan Satriabudi Dharma Setia Jalin Kerjasama Strategis dengan FK Maranatha',
            'slug' => Str::slug('Perkuat Riset Medis, Yayasan Satriabudi Dharma Setia Jalin Kerjasama Strategis dengan FK Maranatha'),
            'content' => 'Yayasan Satriabudi Dharma Setia (YSDS) secara resmi menandatangani Nota Kesepahaman (MoU) kolaborasi strategis dengan Fakultas Kedokteran Universitas Kristen Maranatha. Langkah ini diambil sebagai wujud nyata komitmen kedua belah pihak dalam memajukan kualitas dunia medis dan riset kesehatan di Indonesia. Kolaborasi ini akan berfokus pada tiga pilar utama, yaitu pengembangan riset akademis, program pengabdian masyarakat, serta pemanfaatan teknologi bioteknologi terkini.',
            'status' => 1,
            'created_by' => 1
        ]);

        Post::create([
            'user_id' => 1,
            'post_category_id' => $catActivity->id,
            'title' => 'Mendorong Pemerataan Layanan Medis, Tim YSDS Lakukan Kunjungan Kerja ke Kalimantan Barat',
            'slug' => Str::slug('Mendorong Pemerataan Layanan Medis, Tim YSDS Lakukan Kunjungan Kerja ke Kalimantan Barat'),
            'content' => 'Dalam upaya konsisten mendukung pemerataan infrastruktur kesehatan di wilayah penunjang, jajaran pengurus Yayasan Satriabudi Dharma Setia (YSDS) melaksanakan kunjungan kerja langsung ke beberapa titik di Provinsi Kalimantan Barat. Kunjungan ini bertujuan untuk memetakan kebutuhan riil fasilitas kesehatan di daerah terpencil agar mendapatkan akses pelayanan yang setara. Selama di Kalimantan Barat, tim YSDS melakukan dialog interaktif dengan kepala dinas kesehatan setempat, para dokter, dan tenaga medis di garda terdepan.',
            'status' => 1,
            'created_by' => 1
        ]);

        Post::create([
            'user_id' => 1,
            'post_category_id' => $catResearch->id,
            'title' => 'Membawa Standar Riset Dunia ke Tanah Air: Komitmen YSDS dalam Inovasi Bioteknologi',
            'slug' => Str::slug('Membawa Standar Riset Dunia ke Tanah Air: Komitmen YSDS dalam Inovasi Bioteknologi'),
            'content' => 'Kesenjangan teknologi dan metodologi riset sering kali menjadi hambatan utama bagi para ilmuwan domestik. Menjawab tantangan tersebut, Yayasan Satriabudi Dharma Setia (YSDS) menginisiasi program global untuk membawa standar, kurikulum, serta perangkat riset tingkat dunia langsung ke tanah air. Melalui kemitraan dengan berbagai penyedia teknologi global dan dukungan lintas kementerian, YSDS memfasilitasi transfer pengetahuan (transfer of knowledge) yang komprehensif.',
            'status' => 1,
            'created_by' => 1
        ]);

        Post::create([
            'user_id' => 1,
            'post_category_id' => $catResearch->id,
            'title' => 'Mewarnai Masa Depan Genomik Indonesia untuk Pengobatan Terpersonalisasi',
            'slug' => Str::slug('Mewarnai Masa Depan Genomik Indonesia untuk Pengobatan Terpersonalisasi'),
            'content' => 'Teknologi genomik kini memegang kunci utama dalam revolusi dunia kedokteran modern. Yayasan Satriabudi Dharma Setia (YSDS) mengambil peran pionir dalam mewarnai cetak biru masa depan kesehatan bangsa melalui akselerasi proyek pemetaan variasi genetik (genome sequencing) masyarakat Indonesia. Proyek riset genomik yang didukung oleh YSDS ini dirancang untuk memetakan referensi data genetik lokal secara akurat guna mendukung implementasi precision medicine.',
            'status' => 1,
            'created_by' => 1
        ]);


        // 3. Profiles (Tentang Kami, Sejarah, Visi Misi)
        Profile::create([
            'key' => 'about',
            'title' => 'Tentang Kami',
            'content' => 'Yayasan Satriabudi Dharma Setia (YSDS) adalah sebuah organisasi nirlaba di Indonesia yang bergerak secara aktif dalam mentransformasi, memfasilitasi, serta meningkatkan akses layanan Pendidikan dan Kesehatan yang berkualitas bagi seluruh lapisan masyarakat. Didirikan atas dasar nilai-nilai inklusivitas, transparansi, akuntabilitas, serta kolaborasi tanpa diskriminasi, YSDS berkomitmen tinggi untuk mengabdi bagi negeri guna melahirkan kualitas hidup masyarakat dan masa depan generasi muda yang jauh lebih baik.',
            'created_by' => 1
        ]);

        Profile::create([
            'key' => 'history',
            'title' => 'Sejarah Yayasan',
            'content' => 'Yayasan Satriabudi Dharma Setia didirikan resmi pada tahun 2016 oleh Ibu Erlina VF Ratu. Pendirian yayasan ini didorong oleh kepedulian sosial yang mendalam terhadap tantangan besar bangsa Indonesia, terutama di daerah-daerah penunjang dan pelosok, dalam memperoleh fasilitas kesehatan yang mumpuni serta akses pendidikan formal yang layak. Kini, di bawah kepemimpinan dr. Vincentius Simeon Weo Budhyanto selaku Ketua Umum, YSDS terus memperluas jangkauan aksi kemanusiaannya seperti mitigasi pandemi COVID-19 dengan penyediaan layanan laboratorium RT-PCR gratis hingga pengembangan riset genomik nasional.',
            'created_by' => 1
        ]);

        Profile::create([
            'key' => 'vision_mission',
            'title' => 'Visi & Misi',
            'content' => 'VISI: Mewujudkan masa depan masyarakat dan generasi muda Indonesia yang lebih baik, sehat, cerdas, dan sejahtera melalui perluasan akses pendidikan serta pemerataan layanan kesehatan berkualitas yang berbasis riset dan inovasi. MISI: 1) Mendukung penyediaan, pendistribusian, dan penguatan infrastruktur medis serta riset bioteknologi (genomik). 2) Membantu pengelolaan dan pengembangan berbagai unit serta lembaga pendidikan agar inklusif dan adaptif. 3) Menciptakan jaringan kolaborasi strategis yang transparan dan akuntabel.',
            'created_by' => 1
        ]);


        // 4. Contacts (Hotline, WA, Email, Alamat Kantor Pusat & Cabang Administrasi)
        Contact::create([
            'key' => 'phone',
            'title' => 'Hotline Telepon',
            'value' => '(021) 53161317',
            'created_by' => 1
        ]);

        Contact::create([
            'key' => 'whatsapp',
            'title' => 'WhatsApp Resmi',
            'value' => '0811-1921-2323',
            'created_by' => 1
        ]);

        Contact::create([
            'key' => 'email',
            'title' => 'Email Resmi',
            'value' => 'ysds.indonesia@gmail.com', // Atau: sekretariat.ysds@gmail.com / domain ysds.or.id jika sudah di-binding server lokal
            'created_by' => 1
        ]);

        Contact::create([
            'key' => 'address_main',
            'title' => 'Alamat Kantor Utama',
            'value' => 'Ruko C-17, Pasar Modern Intermoda - BSD, Jl. Raya Cisauk Lapan, Sampora, Kec. Cisauk, Kabupaten Tangerang, Banten 15345',
            'created_by' => 1
        ]);

        Contact::create([
            'key' => 'address_branch',
            'title' => 'Alamat Kantor Administrasi',
            'value' => 'Jl. Kutilang C24 No. 7, Perumahan Sarua Permai, Benda Baru, Kec. Pamulang, Kota Tangerang Selatan, Banten 15414',
            'created_by' => 1
        ]);
    }
}