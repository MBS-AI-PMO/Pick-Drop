<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

trait ManagesVerificationDocuments
{
    protected function storeVerificationDocument(UploadedFile $file, string $dir, string $prefix): string
    {
        $name = $prefix . '_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

        return $file->storeAs($dir, $name, 'public');
    }

    protected function deleteVerificationDocument(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    /**
     * Replace uploaded document fields on a verification model.
     *
     * @param  array<string, string>  $fieldPrefixes  field => filename prefix
     * @return array<string, string> updated field => path
     */
    protected function replaceVerificationDocuments(
        Model $verification,
        array $files,
        string $dir,
        array $fieldPrefixes
    ): array {
        $updates = [];

        foreach ($fieldPrefixes as $field => $prefix) {
            $file = $files[$field] ?? null;
            if (!$file instanceof UploadedFile) {
                continue;
            }

            $this->deleteVerificationDocument($verification->{$field} ?? null);
            $updates[$field] = $this->storeVerificationDocument($file, $dir, $prefix);
        }

        return $updates;
    }

    protected function normalizeCnicNumber(string $cnic): string
    {
        $digits = preg_replace('/\D+/', '', $cnic) ?? '';

        if (strlen($digits) === 13) {
            return substr($digits, 0, 5) . '-' . substr($digits, 5, 7) . '-' . substr($digits, 12, 1);
        }

        return $cnic;
    }
}
