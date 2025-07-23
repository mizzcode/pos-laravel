@extends('layouts.admin')
@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-7">
            <div class="card mb-4">
                <h5 class="card-header">Edit Produk</h5>
                <div class="card-body">
                    {{-- Display all validation errors --}}
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <h6>Ada error pada form:</h6>
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- Display success message --}}
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <div class="d-flex align-items-center">
                                <i class="bx bx-check-circle me-2 fs-4"></i>
                                <div>
                                    <strong>Berhasil!</strong> {{ session('success') }}
                                </div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    {{-- Display error message --}}
                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <div class="d-flex align-items-center">
                                <i class="bx bx-error-circle me-2 fs-4"></i>
                                <div>
                                    <strong>Error!</strong> {{ session('error') }}
                                </div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('products.update', $product->id) }}"
                        enctype="multipart/form-data">
                        @csrf @method('PUT')

                        <div class="mb-3">
                            <label for="nama_produk" class="form-label">Nama Produk</label>
                            <input type="text" name="nama_produk" id="nama_produk"
                                class="form-control @error('nama_produk') is-invalid @enderror"
                                value="{{ old('nama_produk', $product->nama_produk) }}" required>
                            @error('nama_produk')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="category_id" class="form-label">Kategori</label>
                            <select name="category_id" id="category_id"
                                class="form-select @error('category_id') is-invalid @enderror" required>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat->id }}"
                                        {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>
                                        {{ $cat->nama_kategori }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="kode_produk" class="form-label">Kode Produk</label>
                            <input type="text" name="kode_produk" id="kode_produk"
                                class="form-control @error('kode_produk') is-invalid @enderror"
                                value="{{ old('kode_produk', $product->kode_produk) }}" required>
                            @error('kode_produk')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="supplier_id" class="form-label">Supplier (Opsional)</label>
                            <select name="supplier_id" id="supplier_id" class="form-select">
                                <option value="">Internal (tanpa supplier)</option>
                                @foreach ($suppliers as $sup)
                                    <option value="{{ $sup->id }}"
                                        {{ old('supplier_id', $product->supplier_id) == $sup->id ? 'selected' : '' }}>
                                        {{ $sup->nama_supplier ?? $sup->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="stok" class="form-label">Stok</label>
                            <input type="number" name="stok" id="stok"
                                class="form-control @error('stok') is-invalid @enderror" min="0"
                                value="{{ old('stok', $product->stok) }}" required>
                            @error('stok')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="harga_beli" class="form-label">Harga Beli</label>
                            <input type="number" name="harga_beli" id="harga_beli"
                                class="form-control @error('harga_beli') is-invalid @enderror" min="0"
                                value="{{ old('harga_beli', $product->harga_beli) }}">
                            @error('harga_beli')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="harga_jual" class="form-label">Harga Jual</label>
                            <input type="number" name="harga_jual" id="harga_jual"
                                class="form-control @error('harga_jual') is-invalid @enderror" min="0"
                                value="{{ old('harga_jual', $product->harga_jual) }}" required>
                            @error('harga_jual')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="deskripsi" class="form-label">Deskripsi</label>
                            <textarea name="deskripsi" id="deskripsi" class="form-control @error('deskripsi') is-invalid @enderror" rows="2"
                                placeholder="Deskripsi produk (opsional)">{{ old('deskripsi', $product->deskripsi) }}</textarea>
                            @error('deskripsi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="images" class="form-label">Tambah Foto Produk</label>
                            <input type="file" name="images[]" id="images"
                                class="form-control @error('images') is-invalid @enderror" multiple accept="image/*">
                            <small class="text-muted">Format yang didukung: JPG, PNG, GIF. Maksimal 2MB per file. Bisa
                                pilih
                                file ulang untuk mengganti preview. Dan tambah file untuk multiple gambar</small>
                            @error('images')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @error('images.*')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror

                            <!-- Preview gambar yang akan diupload -->
                            <div id="imagePreview" class="mt-2" style="display: none;">
                                <label class="form-label">Preview Gambar Baru:</label>
                                <div class="row" id="previewContainer"></div>
                            </div>

                            @php
                                // Filter images yang benar-benar memiliki file yang ada
                                $validImages = $product->images->filter(function($img) {
                                    if (str_starts_with($img->file_path, 'http')) {
                                        // URL eksternal, anggap valid
                                        return true;
                                    } else {
                                        // File lokal, cek apakah benar-benar ada
                                        $localPath = public_path('storage/' . $img->file_path);
                                        return file_exists($localPath);
                                    }
                                });
                            @endphp

                            @if ($validImages && count($validImages) > 0)
                                <div class="mt-3">
                                    <label class="form-label">Foto Saat Ini:</label>
                                    <div class="row">
                                        @foreach ($validImages as $img)
                                            @php
                                                // Karena sudah difilter, semua gambar di sini pasti valid
                                                if (str_starts_with($img->file_path, 'http')) {
                                                    $imageUrl = $img->file_path;
                                                } else {
                                                    $imageUrl = asset('storage/' . $img->file_path);
                                                }
                                            @endphp
                                            
                                            <div class="col-auto mb-2">
                                                <div class="card" style="width: 120px;">
                                                    <img src="{{ $imageUrl }}" class="card-img-top"
                                                        style="height: 80px; object-fit: cover;" alt="Product Image">
                                                    <div class="card-body p-2">
                                                        <small class="text-muted d-block mb-1">
                                                            @if ($img->is_default)
                                                                <i class="bx bx-star text-warning"></i> Utama
                                                            @else
                                                                #{{ $img->urutan }}
                                                            @endif
                                                        </small>
                                                        <div class="d-grid gap-1">
                                                            @if (!$img->is_default)
                                                                <button type="button"
                                                                    class="btn btn-sm btn-outline-warning"
                                                                    onclick="setDefaultImage({{ $img->id }})">
                                                                    <i class="bx bx-star"></i> Jadikan Utama
                                                                </button>
                                                            @endif
                                                            <button type="button" class="btn btn-sm btn-outline-danger"
                                                                onclick="deleteImage({{ $img->id }})">
                                                                <i class="bx bx-trash"></i> Hapus
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @php
                                // Cek apakah ada images dengan file yang tidak ada (untuk ditampilkan sebagai error)
                                $brokenImages = $product->images->filter(function($img) {
                                    if (str_starts_with($img->file_path, 'http')) {
                                        return false; // URL eksternal tidak bisa dicek
                                    } else {
                                        $localPath = public_path('storage/' . $img->file_path);
                                        return !file_exists($localPath);
                                    }
                                });
                            @endphp

                            @if ($brokenImages && count($brokenImages) > 0)
                                <div class="mt-3">
                                    <label class="form-label text-danger">Foto Bermasalah (File Tidak Ditemukan):</label>
                                    <div class="row">
                                        @foreach ($brokenImages as $img)
                                            <div class="col-auto mb-2">
                                                <div class="card border-danger" style="width: 120px;">
                                                    <div class="card-body p-2 text-center">
                                                        <i class="bx bx-error-circle text-danger fs-3"></i>
                                                        <small class="text-danger d-block mb-1">File tidak ditemukan</small>
                                                        <small class="text-muted d-block mb-2">
                                                            @if ($img->is_default)
                                                                <i class="bx bx-star text-warning"></i> Utama
                                                            @else
                                                                #{{ $img->urutan }}
                                                            @endif
                                                        </small>
                                                        <div class="d-grid">
                                                            <button type="button" class="btn btn-sm btn-outline-danger"
                                                                onclick="deleteImage({{ $img->id }})">
                                                                <i class="bx bx-trash"></i> Hapus Record
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="alert alert-warning mt-2">
                                        <i class="bx bx-info-circle"></i> 
                                        <strong>Perhatian:</strong> File gambar di atas tidak ditemukan di server. 
                                        Silakan hapus record atau upload ulang gambar yang sesuai.
                                    </div>
                                </div>
                            @endif

                            @if ((!$product->images || count($product->images) == 0) || (count($validImages) == 0 && count($brokenImages) == 0))
                                <div class="mt-2">
                                    <div class="alert alert-info">
                                        <i class="bx bx-info-circle"></i> Belum ada foto untuk produk ini. Silakan upload
                                        foto untuk menampilkannya di katalog.
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="d-flex justify-content-end">
                            <a href="{{ route('products.index') }}" class="btn btn-outline-secondary me-2">
                                <i class="bx bx-arrow-back"></i> Batal
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bx bx-save"></i> Update
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto-hide success/error alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert.alert-success, .alert.alert-danger');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }, 5000); // 5 seconds
            });
        });

        // Function to delete image
        function deleteImage(imageId) {
            if (confirm('Hapus gambar ini?')) {
                // Create a temporary form
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/product-images/${imageId}`;

                // Add CSRF token
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = csrfToken;
                form.appendChild(csrfInput);

                // Add method override for DELETE
                const methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                methodInput.value = 'DELETE';
                form.appendChild(methodInput);

                // Submit form
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Function to set default image
        function setDefaultImage(imageId) {
            if (confirm('Jadikan gambar ini sebagai foto utama?')) {
                // Create a temporary form
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/product-images/${imageId}/set-default`;

                // Add CSRF token
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = csrfToken;
                form.appendChild(csrfInput);

                // Submit form
                document.body.appendChild(form);
                form.submit();
            }
        }

        document.getElementById('images').addEventListener('change', function(e) {
            const files = e.target.files;
            const previewContainer = document.getElementById('previewContainer');
            const imagePreview = document.getElementById('imagePreview');

            // Clear previous previews
            previewContainer.innerHTML = '';

            if (files.length > 0) {
                imagePreview.style.display = 'block';

                Array.from(files).forEach((file, index) => {
                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();

                        reader.onload = function(e) {
                            const colDiv = document.createElement('div');
                            colDiv.className = 'col-auto mb-2';

                            colDiv.innerHTML = `
                        <div class="card" style="width: 120px;">
                            <img src="${e.target.result}" class="card-img-top" style="height: 80px; object-fit: cover;" alt="Preview">
                            <div class="card-body p-2">
                                <small class="text-muted">${file.name}</small>
                            </div>
                        </div>
                    `;

                            previewContainer.appendChild(colDiv);
                        };

                        reader.readAsDataURL(file);
                    }
                });
            } else {
                imagePreview.style.display = 'none';
            }
        });

        // Debug form submission
        document.querySelector('form').addEventListener('submit', function(e) {
            console.log('Form is being submitted...');

            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="bx bx-loader-alt bx-spin"></i> Menyimpan...';

            // Add loading overlay to form
            const formCard = document.querySelector('.card-body');
            const loadingOverlay = document.createElement('div');
            loadingOverlay.className =
                'position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center';
            loadingOverlay.style.cssText =
                'background: rgba(255,255,255,0.8); z-index: 1000; border-radius: 0.375rem;';
            loadingOverlay.innerHTML = `
                <div class="text-center">
                    <div class="spinner-border text-primary mb-2" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <div class="small text-muted">Memproses perubahan...</div>
                </div>
            `;
            formCard.style.position = 'relative';
            formCard.appendChild(loadingOverlay);

            const formData = new FormData(this);
            console.log('Form data:');
            for (let [key, value] of formData.entries()) {
                if (key === 'images[]') {
                    console.log(key + ':', value.name, value.size);
                } else {
                    console.log(key + ':', value);
                }
            }

            // Check if images are selected
            const imageInput = document.getElementById('images');
            if (imageInput.files.length > 0) {
                console.log('Images selected:', imageInput.files.length);
                // Update loading message for image uploads
                loadingOverlay.querySelector('.small').textContent =
                    `Mengupload ${imageInput.files.length} gambar...`;
            } else {
                console.log('No images selected');
            }

            // Reset button if form submission fails (this won't execute if form submits successfully)
            setTimeout(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
                if (loadingOverlay.parentNode) {
                    loadingOverlay.remove();
                }
            }, 10000); // 10 seconds timeout
        });
    </script>

@endsection
