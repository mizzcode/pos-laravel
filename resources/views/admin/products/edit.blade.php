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
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('products.update', $product->id) }}" enctype="multipart/form-data">
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
                            <small class="text-muted">Format yang didukung: JPG, PNG, GIF. Maksimal 2MB per file. Bisa pilih
                                multiple file.</small>
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

                            @if ($product->images && count($product->images) > 0)
                                <div class="mt-3">
                                    <label class="form-label">Foto Saat Ini:</label>
                                    <div class="row">
                                        @foreach ($product->images as $img)
                                            <div class="col-auto mb-2">
                                                <div class="card" style="width: 120px;">
                                                    @php
                                                        // Cek apakah gambar lokal atau URL eksternal
                                                        if (str_starts_with($img->file_path, 'http')) {
                                                            $imageUrl = $img->file_path;
                                                        } else {
                                                            $localPath = public_path('storage/' . $img->file_path);
                                                            $imageUrl = file_exists($localPath)
                                                                ? asset('storage/' . $img->file_path)
                                                                : 'https://placehold.co/120x80/95A5A6/FFFFFF?text=Error';
                                                        }
                                                    @endphp
                                                    <img src="{{ $imageUrl }}" class="card-img-top"
                                                        style="height: 80px; object-fit: cover;" alt="Product Image"
                                                        onerror="this.src='https://placehold.co/120x80/95A5A6/FFFFFF?text=Error'">
                                                    <div class="card-body p-2">
                                                        <small class="text-muted d-block mb-1">
                                                            @if ($img->is_default)
                                                                <i class="bx bx-star text-warning"></i> Utama
                                                            @else
                                                                #{{ $img->urutan }}
                                                            @endif
                                                        </small>
                                                        <div class="d-grid">
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
                            @else
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
            submitBtn.innerHTML = '<i class="bx bx-loader-alt bx-spin"></i> Memproses...';

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
                alert('Sedang mengupload ' + imageInput.files.length + ' gambar. Mohon tunggu...');
            } else {
                console.log('No images selected');
            }

            // Reset button if form submission fails (this won't execute if form submits successfully)
            setTimeout(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }, 5000);
        });
    </script>

@endsection
