#!/bin/bash

echo "🚀 Mempersiapkan Project Laravel untuk ZIP..."
echo "=============================================="

# 1. Build production assets
echo "📦 Building production assets..."
npm run build

# 2. Clear semua cache Laravel
echo "🧹 Clearing Laravel cache..."
php artisan optimize:clear

# 3. Clear session files jika ada
echo "🗑️  Clearing session files..."
rm -rf storage/framework/sessions/*
rm -rf storage/framework/cache/data/*

# 4. Clear log files (opsional)
echo "📝 Clearing log files..."
> storage/logs/laravel.log

# 5. Pastikan permission storage dan bootstrap/cache
echo "🔐 Setting proper permissions..."
chmod -R 755 storage
chmod -R 755 bootstrap/cache

echo ""
echo "✅ Project siap untuk di-zip!"
echo ""
echo "📋 Yang sudah dilakukan:"
echo "   ✓ Build production assets (public/build/)"
echo "   ✓ Clear semua cache Laravel"
echo "   ✓ Clear session dan cache files"
echo "   ✓ Clear log files"
echo "   ✓ Remove node_modules"
echo "   ✓ Set proper permissions"
echo ""
echo "📁 Sekarang Anda bisa zip folder ini dengan aman!"
echo "   Contoh: zip -r project-pos.zip . -x '*.git*' '*.DS_Store*'"
