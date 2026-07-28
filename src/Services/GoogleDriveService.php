<?php

namespace Leazycms\Web\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleDriveService
{
    protected ?string $clientId;
    protected ?string $clientSecret;
    protected ?string $refreshToken;
    protected ?string $folderId;

    public function __construct()
    {
        $this->clientId = get_option('google_drive_client_id');
        $this->clientSecret = get_option('google_drive_client_secret');
        $this->refreshToken = get_option('google_drive_refresh_token');
        $this->folderId = get_option('google_drive_folder_id');
    }

    /**
     * Dapatkan Access Token baru menggunakan Refresh Token
     */
    public function getAccessToken(): ?string
    {
        if (!$this->clientId || !$this->clientSecret || !$this->refreshToken) {
            return null;
        }

        try {
            $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'refresh_token' => $this->refreshToken,
                'grant_type' => 'refresh_token',
            ]);

            if ($response->successful()) {
                return $response->json('access_token');
            }
            
            Log::error('Google Drive getAccessToken gagal: ' . $response->body());
        } catch (\Exception $e) {
            Log::error('Google Drive getAccessToken error: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Upload file ke Google Drive (Resumable Upload untuk file besar direkomendasikan, 
     * tapi Multipart Upload sudah cukup untuk backup zip standar)
     */
    public function upload(string $filePath, string $fileName): bool
    {
        $token = $this->getAccessToken();
        if (!$token || !file_exists($filePath)) {
            return false;
        }

        $metadata = [
            'name' => $fileName,
        ];
        
        if ($this->folderId) {
            $metadata['parents'] = [$this->folderId];
        }

        try {
            // Upload menggunakan multipart (REST API)
            $response = Http::withToken($token)
                ->attach('metadata', json_encode($metadata), 'metadata.json', ['Content-Type' => 'application/json; charset=UTF-8'])
                ->attach('file', file_get_contents($filePath), $fileName)
                ->post('https://www.googleapis.com/upload/drive/v3/files?uploadType=multipart');

            if ($response->successful()) {
                return true;
            }
            
            Log::error('Google Drive upload gagal: ' . $response->body());
        } catch (\Exception $e) {
            Log::error('Google Drive upload error: ' . $e->getMessage());
        }

        return false;
    }

    /**
     * Ambil list file ZIP di folder / root 
     */
    public function list(): array
    {
        $token = $this->getAccessToken();
        if (!$token) {
            return [];
        }

        // Cari file dengan nama berakhiran .zip
        $query = "name contains '.zip' and mimeType != 'application/vnd.google-apps.folder' and trashed = false";
        if ($this->folderId) {
            $query .= " and '" . $this->folderId . "' in parents";
        }

        try {
            $response = Http::withToken($token)->get('https://www.googleapis.com/drive/v3/files', [
                'q' => $query,
                'fields' => 'files(id, name, size, modifiedTime)',
                'spaces' => 'drive',
                'orderBy' => 'modifiedTime desc',
                'pageSize' => 100,
            ]);

            if ($response->successful()) {
                $files = $response->json('files', []);
                return array_map(function($file) {
                    return [
                        'id' => $file['id'],
                        'name' => $file['name'],
                        'size' => $file['size'] ?? 0,
                        'time' => isset($file['modifiedTime']) ? strtotime($file['modifiedTime']) : time(),
                    ];
                }, $files);
            }
            
            Log::error('Google Drive list gagal: ' . $response->body());
        } catch (\Exception $e) {
            Log::error('Google Drive list error: ' . $e->getMessage());
        }

        return [];
    }

    /**
     * Hapus file berdasarkan ID
     */
    public function delete(string $fileId): bool
    {
        $token = $this->getAccessToken();
        if (!$token) {
            return false;
        }

        try {
            $response = Http::withToken($token)->delete('https://www.googleapis.com/drive/v3/files/' . $fileId);
            
            if ($response->successful() || $response->status() === 204) {
                return true;
            }
            
            Log::error('Google Drive delete gagal: ' . $response->body());
        } catch (\Exception $e) {
            Log::error('Google Drive delete error: ' . $e->getMessage());
        }

        return false;
    }

    /**
     * Download file berdasarkan ID
     */
    public function download(string $fileId)
    {
        $token = $this->getAccessToken();
        if (!$token) {
            return null;
        }

        try {
            // Get file metadata to get the name
            $metadataResponse = Http::withToken($token)->get('https://www.googleapis.com/drive/v3/files/' . $fileId, [
                'fields' => 'name',
            ]);
            
            $fileName = 'backup.zip';
            if ($metadataResponse->successful()) {
                $fileName = $metadataResponse->json('name', 'backup.zip');
            }

            // Download file content
            $response = Http::withToken($token)->get('https://www.googleapis.com/drive/v3/files/' . $fileId . '?alt=media');
            
            if ($response->successful()) {
                return [
                    'content' => $response->body(),
                    'name' => $fileName,
                ];
            }
            
            Log::error('Google Drive download gagal: ' . $response->body());
        } catch (\Exception $e) {
            Log::error('Google Drive download error: ' . $e->getMessage());
        }

        return null;
    }
}
