<?php

use App\Models\Setting;
use Illuminate\Support\Facades\Storage;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new class extends Component {
    use WithFileUploads;

    public $settings = [];
    public $form = [];
    public $imagePreviews = [];
    public $heroProfessions = [];
    public $newProfession = '';

    // Default settings keys
    public $settingKeys = [
        'site_title' => ['type' => 'text', 'group' => 'general', 'label' => 'Site Title', 'description' => 'Title untuk halaman website'],
        'site_name' => ['type' => 'text', 'group' => 'general', 'label' => 'Site Name', 'description' => 'Nama yang ditampilkan di navbar dan hero section'],
        'hero_professions' => ['type' => 'json', 'group' => 'hero', 'label' => 'Hero Professions', 'description' => 'Daftar profesi untuk typewriter (JSON array, contoh: ["Frontend Developer", "Flutter Developer"])'],
        'hero_image' => ['type' => 'image', 'group' => 'hero', 'label' => 'Hero Image', 'description' => 'Foto profil untuk hero section'],
        'meta_description' => ['type' => 'text', 'group' => 'seo', 'label' => 'Meta Description', 'description' => 'Deskripsi untuk SEO'],
        'logo' => ['type' => 'image', 'group' => 'branding', 'label' => 'Logo', 'description' => 'Logo untuk navbar'],
        'favicon' => ['type' => 'image', 'group' => 'branding', 'label' => 'Favicon', 'description' => 'Favicon untuk browser tab'],
        'github_url' => ['type' => 'text', 'group' => 'social', 'label' => 'GitHub URL', 'description' => 'Link ke GitHub profile'],
        'linkedin_url' => ['type' => 'text', 'group' => 'social', 'label' => 'LinkedIn URL', 'description' => 'Link ke LinkedIn profile'],
        'instagram_url' => ['type' => 'text', 'group' => 'social', 'label' => 'Instagram URL', 'description' => 'Link ke Instagram profile'],
        'email' => ['type' => 'text', 'group' => 'contact', 'label' => 'Email', 'description' => 'Email kontak'],
        'phone' => ['type' => 'text', 'group' => 'contact', 'label' => 'Phone', 'description' => 'Nomor telepon'],
        'about_description' => ['type' => 'text', 'group' => 'about', 'label' => 'About Description', 'description' => 'Deskripsi untuk section "Tentang Saya"'],
        'footer_description' => ['type' => 'textarea', 'group' => 'general', 'label' => 'Footer Description', 'description' => 'Deskripsi yang ditampilkan di footer'],
        'resume_pdf' => ['type' => 'file', 'group' => 'general', 'label' => 'Resume PDF', 'description' => 'File PDF untuk download resume'],
    ];

    public function mount()
    {
        $this->loadSettings();
    }

    public function loadSettings()
    {
        $dbSettings = Setting::all()->keyBy('key');
        
        foreach ($this->settingKeys as $key => $config) {
            $setting = $dbSettings->get($key);
            
            // Initialize file fields to null, others to empty string or value
            if ($config['type'] === 'file' || $config['type'] === 'image') {
                $this->form[$key] = null; // File fields should be null initially
            } else {
                $this->form[$key] = $setting ? $setting->value : '';
            }
            
            // Set default values if empty (only for non-file fields)
            if (empty($this->form[$key]) && $config['type'] !== 'file' && $config['type'] !== 'image') {
                if ($key === 'site_name') {
                    $this->form[$key] = 'Ilham Hatta Manggala';
                } elseif ($key === 'hero_professions') {
                    $this->form[$key] = '["Frontend Developer", "Flutter Developer"]';
                } elseif ($key === 'about_description') {
                    $this->form[$key] = 'Saya adalah seorang yang berfokus pada pengembangan website serta aplikasi mobile. Saya memiliki ketertarikan besar terhadap teknologi web dan mobile, khususnya dalam pengembangan menggunakan Flask, Laravel, React, Flutter, Bootstrap, dan Tailwind CSS.';
                } elseif ($key === 'footer_description') {
                    $this->form[$key] = 'Web & Flutter Developer yang berfokus pada solusi digital modern, antarmuka yang bersih, dan performa yang optimal.';
                }
            }
            
            // Load hero professions as array
            if ($key === 'hero_professions') {
                if ($setting && $setting->value) {
                    $decoded = json_decode($setting->value, true);
                    $this->heroProfessions = is_array($decoded) ? $decoded : [];
                } else {
                    $this->heroProfessions = ['Frontend Developer', 'Flutter Developer'];
                }
            }
            
            // Load image previews
            if ($config['type'] === 'image' && $setting && $setting->value) {
                $this->imagePreviews[$key] = asset('storage/' . $setting->value);
            }
            
            // Load file previews (for PDF, etc)
            if ($config['type'] === 'file' && $setting && $setting->value) {
                $this->imagePreviews[$key] = asset('storage/' . $setting->value);
            }
            
            // Initialize file fields to null if not set
            if ($config['type'] === 'file' && !isset($this->form[$key])) {
                $this->form[$key] = null;
            }
        }
    }

    public function updatedForm($value, $key)
    {
        // Handle image upload preview
        if (isset($this->settingKeys[$key]) && $this->settingKeys[$key]['type'] === 'image') {
            if ($value instanceof \Illuminate\Http\UploadedFile) {
                $this->validate([
                    "form.{$key}" => 'image|max:2048',
                ]);
                $this->imagePreviews[$key] = $value->temporaryUrl();
            }
        }
        
        // Handle file upload preview (PDF, etc)
        if (isset($this->settingKeys[$key]) && $this->settingKeys[$key]['type'] === 'file') {
            if ($value instanceof \Illuminate\Http\UploadedFile) {
                $this->validate([
                    "form.{$key}" => 'mimes:pdf|max:5120', // Max 5MB for PDF
                ]);
                // For PDF, we can't use temporaryUrl() as PDF is not previewable
                // Just store the filename for display
                $this->imagePreviews[$key] = $value->getClientOriginalName();
            }
        }
    }

    public function addProfession()
    {
        $profession = trim($this->newProfession);
        if (empty($profession)) {
            return;
        }
        
        if (!in_array($profession, $this->heroProfessions)) {
            $this->heroProfessions[] = $profession;
            $this->newProfession = '';
        }
    }

    public function removeProfession($index)
    {
        unset($this->heroProfessions[$index]);
        $this->heroProfessions = array_values($this->heroProfessions); // Re-index array
    }

    public function save()
    {
        // Validate file uploads first
        $this->validate([
            'form.resume_pdf' => 'nullable|mimes:pdf|max:5120',
        ]);
        
        // Convert hero professions array to JSON before saving
        $this->form['hero_professions'] = json_encode($this->heroProfessions);
        
        foreach ($this->form as $key => $value) {
            if (!isset($this->settingKeys[$key])) {
                continue;
            }

            $config = $this->settingKeys[$key];
            $settingData = [
                'key' => $key,
                'type' => $config['type'],
                'group' => $config['group'],
                'description' => $config['description'] ?? null,
            ];

            // Handle image upload
            if ($config['type'] === 'image' && $value instanceof \Illuminate\Http\UploadedFile) {
                // Delete old image if exists
                $oldSetting = Setting::where('key', $key)->first();
                if ($oldSetting && $oldSetting->value) {
                    Storage::disk('public')->delete($oldSetting->value);
                }
                
                $path = $value->store('settings', 'public');
                $settingData['value'] = $path;
            } elseif ($config['type'] === 'file' && $value instanceof \Illuminate\Http\UploadedFile) {
                // Handle file upload (PDF, etc)
                // Delete old file if exists
                $oldSetting = Setting::where('key', $key)->first();
                if ($oldSetting && $oldSetting->value) {
                    Storage::disk('public')->delete($oldSetting->value);
                }
                
                // Store the file
                $path = $value->store('settings', 'public');
                
                // Verify file was stored
                if (!Storage::disk('public')->exists($path)) {
                    session()->flash('error', "Failed to save file for {$key}");
                    continue;
                }
                
                $settingData['value'] = $path;
            } elseif ($config['type'] === 'image' && empty($value)) {
                // Skip if image field is empty (don't update existing)
                continue;
            } elseif ($config['type'] === 'file' && empty($value)) {
                // Skip if file field is empty (don't update existing)
                continue;
            } elseif ($config['type'] === 'json' && !empty($value)) {
                // Validate JSON format
                $decoded = json_decode($value, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    session()->flash('error', "Invalid JSON format for {$key}: " . json_last_error_msg());
                    return;
                }
                $settingData['value'] = $value;
            } else {
                $settingData['value'] = $value;
            }

            // Update or create setting
            Setting::updateOrCreate(
                ['key' => $key],
                $settingData
            );
        }

        $this->loadSettings();
        session()->flash('message', 'Settings berhasil disimpan!');
    }
}; ?>

<section class="w-full">
    <div class="mb-6">
        <h1 class="text-2xl font-bold">Frontend Settings</h1>
        <p class="text-sm text-zinc-600 dark:text-zinc-400">Manage logo, favicon, and other frontend configurations</p>
    </div>

    @if (session()->has('message'))
        <flux:callout variant="success" icon="check-circle" heading="Success" class="mb-4">
            {{ session('message') }}
        </flux:callout>
    @endif

    @if (session()->has('error'))
        <flux:callout variant="danger" icon="exclamation-circle" heading="Error" class="mb-4">
            {{ session('error') }}
        </flux:callout>
    @endif

    <form wire:submit="save" class="space-y-8">
        <!-- General Settings -->
        <div class="rounded-lg border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 p-6">
            <h2 class="text-lg font-semibold mb-4">General Settings</h2>
            <div class="space-y-4">
                <flux:input 
                    wire:model="form.site_title" 
                    label="Site Title" 
                    placeholder="Ilham Hatta Manggala"
                    hint="Title untuk halaman website"
                />
                <flux:input 
                    wire:model="form.site_name" 
                    label="Site Name" 
                    placeholder="Ilham Hatta Manggala"
                    hint="Nama yang ditampilkan di navbar"
                />
                <div>
                    <flux:label>Resume PDF</flux:label>
                    @if(isset($imagePreviews['resume_pdf']))
                        <div class="mt-2 mb-2">
                            @php
                                $resumePreview = $imagePreviews['resume_pdf'];
                                $isUrl = str_contains($resumePreview, 'storage/') || str_contains($resumePreview, 'http');
                            @endphp
                            @if($isUrl)
                                <a href="{{ $resumePreview }}" target="_blank" class="text-blue-600 dark:text-blue-400 hover:underline">
                                    📄 Lihat Resume PDF saat ini
                                </a>
                            @else
                                <p class="text-sm text-zinc-600 dark:text-zinc-400">
                                    File yang akan diupload: <strong>{{ $resumePreview }}</strong>
                                </p>
                            @endif
                        </div>
                    @endif
                    <flux:input 
                        type="file" 
                        wire:model="form.resume_pdf" 
                        accept="application/pdf"
                        hint="File PDF untuk download resume (Max 5MB)"
                    />
                </div>
                <flux:textarea 
                    wire:model="form.footer_description" 
                    label="Footer Description"
                    placeholder="Web & Flutter Developer yang berfokus pada solusi digital modern..."
                    hint="Deskripsi yang ditampilkan di footer"
                    rows="3"
                />
                <flux:textarea 
                    wire:model="form.meta_description" 
                    label="Meta Description" 
                    placeholder="Portfolio website..."
                    hint="Deskripsi untuk SEO"
                    rows="3"
                />
            </div>
        </div>

        <!-- Hero Settings -->
        <div class="rounded-lg border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 p-6">
            <h2 class="text-lg font-semibold mb-4">Hero Section</h2>
            <div class="space-y-4">
                <div>
                    <flux:label>Hero Image</flux:label>
                    @if(isset($imagePreviews['hero_image']))
                        <div class="mt-2 mb-2">
                            <img src="{{ $imagePreviews['hero_image'] }}" alt="Hero Image Preview" class="h-48 w-auto rounded">
                        </div>
                    @endif
                    <flux:input 
                        type="file" 
                        wire:model="form.hero_image" 
                        accept="image/*"
                        hint="Foto profil untuk hero section (PNG, JPG - Max 2MB)"
                    />
                </div>
                <div>
                    <flux:label>Hero Professions</flux:label>
                    <div class="flex gap-2 mb-2">
                        <flux:input 
                            wire:model="newProfession" 
                            placeholder="Masukkan profesi (contoh: Frontend Developer)"
                            wire:keydown.enter.prevent="addProfession"
                            class="flex-1"
                        />
                        <flux:button 
                            type="button" 
                            variant="primary" 
                            wire:click="addProfession"
                            wire:loading.attr="disabled"
                        >
                            Add
                        </flux:button>
                    </div>
                    
                    @if(count($heroProfessions) > 0)
                        <div class="flex flex-wrap gap-2 mt-2">
                            @foreach($heroProfessions as $index => $profession)
                                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 text-sm">
                                    {{ $profession }}
                                    <button 
                                        type="button"
                                        wire:click="removeProfession({{ $index }})"
                                        class="ml-1 text-blue-600 dark:text-blue-300 hover:text-blue-800 dark:hover:text-blue-100"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </span>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-2">
                            Belum ada profesi. Tambahkan profesi di atas.
                        </p>
                    @endif
                </div>
            </div>
        </div>

        <!-- About Settings -->
        <div class="rounded-lg border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 p-6">
            <h2 class="text-lg font-semibold mb-4">About Section</h2>
            <div class="space-y-4">
                <div>
                    <flux:label>About Description</flux:label>
                    <flux:textarea 
                        wire:model="form.about_description" 
                        label="Deskripsi Tentang Saya"
                        placeholder="Masukkan deskripsi tentang diri Anda..."
                        rows="4"
                        hint="Deskripsi yang akan ditampilkan di section 'Tentang Saya'"
                    />
                </div>
            </div>
        </div>

        <!-- Branding Settings -->
        <div class="rounded-lg border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 p-6">
            <h2 class="text-lg font-semibold mb-4">Branding</h2>
            <div class="space-y-4">
                <div>
                    <flux:label>Logo</flux:label>
                    @if(isset($imagePreviews['logo']))
                        <div class="mt-2 mb-2">
                            <img src="{{ $imagePreviews['logo'] }}" alt="Logo Preview" class="h-16 w-auto rounded">
                        </div>
                    @endif
                    <flux:input 
                        type="file" 
                        wire:model="form.logo" 
                        accept="image/*"
                        hint="Logo untuk navbar (PNG, JPG, SVG - Max 2MB)"
                    />
                </div>

                <div>
                    <flux:label>Favicon</flux:label>
                    @if(isset($imagePreviews['favicon']))
                        <div class="mt-2 mb-2">
                            <img src="{{ $imagePreviews['favicon'] }}" alt="Favicon Preview" class="h-16 w-16 rounded">
                        </div>
                    @endif
                    <flux:input 
                        type="file" 
                        wire:model="form.favicon" 
                        accept="image/*"
                        hint="Favicon untuk browser tab (PNG, ICO, SVG - Max 2MB)"
                    />
                </div>
            </div>
        </div>

        <!-- Social Media Settings -->
        <div class="rounded-lg border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 p-6">
            <h2 class="text-lg font-semibold mb-4">Social Media</h2>
            <div class="space-y-4">
                <flux:input 
                    wire:model="form.github_url" 
                    label="GitHub URL" 
                    type="url"
                    placeholder="https://github.com/username"
                />
                <flux:input 
                    wire:model="form.linkedin_url" 
                    label="LinkedIn URL" 
                    type="url"
                    placeholder="https://linkedin.com/in/username"
                />
                <flux:input 
                    wire:model="form.instagram_url" 
                    label="Instagram URL" 
                    type="url"
                    placeholder="https://instagram.com/username"
                />
            </div>
        </div>

        <!-- Contact Settings -->
        <div class="rounded-lg border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 p-6">
            <h2 class="text-lg font-semibold mb-4">Contact Information</h2>
            <div class="space-y-4">
                <flux:input 
                    wire:model="form.email" 
                    label="Email" 
                    type="email"
                    placeholder="your@email.com"
                />
                <flux:input 
                    wire:model="form.phone" 
                    label="Phone" 
                    type="tel"
                    placeholder="+62 123 456 7890"
                />
            </div>
        </div>

        <div class="flex justify-end">
            <flux:button variant="primary" type="submit">
                Save Settings
            </flux:button>
        </div>
    </form>
</section>

