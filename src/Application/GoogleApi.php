<?php
namespace CSIROCMS\Application;

use Google_Client;
use Google_Service_Drive;
use Symfony\Component\Filesystem\Exception\IOException;

class GoogleApi
{
    private $name = 'Google Drive API';
    private $credentialsPath = 'config/client_id.json';
    private $clientSecretPath = 'config/client_secret.json';
    private $scopes = [];
    private $service = null;

    /**
     * Returns an authorized API client.
     * @return Google_Client the authorized client object
     */
    public function getClient()
    {
        $client = new Google_Client();
        $client->setApplicationName($this->name);
        $client->setScopes($this->scopes);
		if (file_exists($this->clientSecretPath)) {
			$client->setAuthConfig($this->clientSecretPath);
		} else {
			throw new IOException("The `config/client_secret.json` file is missing.\nCheck the README for instructions.");
		}
        $client->setAccessType('offline');
		$client->setApprovalPrompt('force');
        $client->setIncludeGrantedScopes(true);
        // Load previously authorized credentials from a file.
        if (file_exists($this->credentialsPath)) {
            $accessToken = json_decode(file_get_contents($this->credentialsPath), true);
        } else {
            // Request authorization from the user.
            $authUrl = $client->createAuthUrl();
            printf("Open the following link in your browser:\n%s\n", $authUrl);
            print 'Enter verification code: ';
            $authCode = trim(fgets(STDIN));

            // Exchange authorization code for an access token.
            $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
            // Store the credentials to disk.
            if (!file_exists(dirname($this->credentialsPath))) {
                mkdir(dirname($this->credentialsPath), 0700, true);
            }
            file_put_contents($this->credentialsPath, json_encode($accessToken));
            printf("Credentials saved to %s\n", $this->credentialsPath);
        }
        $client->setAccessToken($accessToken);

        // Refresh the token if it's expired.
        if ($client->isAccessTokenExpired()) {
            $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            file_put_contents($this->credentialsPath, json_encode($client->getAccessToken()));
        }
        return $client;
    }

    /**
     * Expands the home directory alias '~' to the full path.
     * @param string $path the path to expand.
     * @return string the expanded path.
     */
    public function expandHomeDirectory($path)
    {
        $homeDirectory = getenv('HOME');
        if (empty($homeDirectory)) {
            $homeDirectory = getenv('HOMEDRIVE') . getenv('HOMEPATH');
        }
        return str_replace('~', realpath($homeDirectory), $path);
    }

    public function listFiles($service, $query)
    {
        $optParams = array(
            'fields' => 'files(id, name)',
            'q' => $query
        );
        return $service->files->listFiles($optParams);
    }

    public function exportFile($service, $fileId, $mimeType)
    {
        $response = $service->files->export($fileId, $mimeType, array(
            'alt' => 'media'));
        return $response->getBody()->getContents();
    }

    public function getService() {
        return $this->service;
    }

    public function configure() {
        $this->scopes = implode(' ', [Google_Service_Drive::DRIVE]);
        $client = $this->getClient();
        $this->service = new Google_Service_Drive($client);
    }

    public function __construct() {

    }
}
