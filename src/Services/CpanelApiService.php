<?php

namespace Leazycms\Web\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CpanelApiService
{
    protected $host;
    protected $username;
    protected $token;
    protected $default_directory;

    public function __construct()
    {
        try {
            $host = config('modules.cpanel_api.host');
            $username = config('modules.cpanel_api.username');
            $token = config('modules.cpanel_api.api_token');
            
            $this->host = $host ? \Illuminate\Support\Facades\Crypt::decryptString($host) : null;
            $this->username = $username ? \Illuminate\Support\Facades\Crypt::decryptString($username) : null;
            $this->token = $token ? \Illuminate\Support\Facades\Crypt::decryptString($token) : null;
        } catch (\Exception $e) {
            $this->host = null;
            $this->username = null;
            $this->token = null;
        }
        $this->default_directory = config('modules.cpanel_api.default_directory');
    }

    public function isActive()
    {
        return !empty($this->host) && !empty($this->username) && !empty($this->token);
    }

    protected function execute($module, $function, $parameters = [])
    {
        if (!$this->host || !$this->username || !$this->token) {
            Log::warning('cPanel API credentials not configured.');
            return false;
        }

        $url = rtrim($this->host, '/') . '/execute/' . $module . '/' . $function;

        try {
            $response = Http::withHeaders([
                'Authorization' => 'cpanel ' . $this->username . ':' . $this->token
            ])->get($url, $parameters);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['status']) && $data['status'] === 1) {
                    return $data['data'] ?? true;
                } else {
                    $error = $data['errors'][0] ?? 'Unknown cPanel API Error';
                    Log::error("cPanel API Error ($module/$function): " . $error);
                    return ['error' => $error];
                }
            } else {
                Log::error('cPanel API HTTP Error: ' . $response->status());
                return ['error' => 'HTTP Error ' . $response->status()];
            }
        } catch (\Exception $e) {
            Log::error('cPanel API Exception: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    protected function executeApi2($module, $function, $parameters = [])
    {
        if (!$this->host || !$this->username || !$this->token) {
            Log::warning('cPanel API credentials not configured.');
            return false;
        }

        $url = rtrim($this->host, '/') . '/json-api/cpanel';
        
        $query = array_merge([
            'cpanel_jsonapi_apiversion' => 2,
            'cpanel_jsonapi_module' => $module,
            'cpanel_jsonapi_func' => $function,
        ], $parameters);

        try {
            $response = Http::withHeaders([
                'Authorization' => 'cpanel ' . $this->username . ':' . $this->token
            ])->get($url, $query);

            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['cpanelresult']['data'][0]['result'])) {
                    $result = $data['cpanelresult']['data'][0]['result'];
                    if ($result == 1) {
                        return true;
                    } else {
                        $error = $data['cpanelresult']['data'][0]['reason'] ?? 'Unknown cPanel API 2 Error';
                        Log::error("cPanel API 2 Error ($module/$function): " . $error);
                        return ['error' => $error];
                    }
                } elseif (isset($data['cpanelresult']['error'])) {
                    $error = $data['cpanelresult']['error'];
                    Log::error("cPanel API 2 Error ($module/$function): " . $error);
                    return ['error' => $error];
                }
                
                return $data['cpanelresult']['data'] ?? true;
            } else {
                Log::error('cPanel API HTTP Error: ' . $response->status());
                return ['error' => 'HTTP Error ' . $response->status()];
            }
        } catch (\Exception $e) {
            Log::error('cPanel API Exception: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    public function checkDomainExists($domain)
    {
        $result = $this->execute('DomainInfo', 'list_domains');
        
        if (isset($result['error']) || !$result) {
            return false; 
        }

        $allDomains = [];
        
        if (isset($result['main_domain'])) $allDomains[] = $result['main_domain'];
        if (isset($result['addon_domains'])) $allDomains = array_merge($allDomains, $result['addon_domains']);
        if (isset($result['parked_domains'])) $allDomains = array_merge($allDomains, $result['parked_domains']);
        if (isset($result['sub_domains'])) $allDomains = array_merge($allDomains, $result['sub_domains']);

        return in_array($domain, $allDomains);
    }

    public function createAliasDomain($domain)
    {
        // Generate subdomain name automatically (e.g. domainbaru.com -> domainbaru)
        $subdomain = explode('.', $domain)[0];

        return $this->executeApi2('AddonDomain', 'addaddondomain', [
            'newdomain' => $domain,
            'subdomain' => $subdomain,
            'dir' => $this->default_directory
        ]);
    }

    public function deleteAliasDomain($domain)
    {
        $subdomain = explode('.', $domain)[0];

        return $this->executeApi2('AddonDomain', 'deladdondomain', [
            'domain' => $domain,
            'subdomain' => $subdomain
        ]);
    }
}
