<?php

/**
 * CloudFlare API
 *
 *
 * @author AzzA <azza@broadcasthe.net>
 * @copyright omgwtfhax inc. 2013
 * @version 1.1
 */
class cloudflare_api
{
    //The URL of the API
    private static $URL = 'https://api.cloudflare.com/v4/';

    //Service mode values.
    private static $MODE_SERVICE = array('A', 'AAAA', 'CNAME', 'TXT', 'SRV', 'LOC', 'MX', 'NS', 'SPF');

    //Prio values.
    private static $PRIO = array('MX', 'SRV');

    //Timeout for the API requests in seconds
    const TIMEOUT = 5;

    // //Interval values for Stats
    // const INTERVAL_365_DAYS = 10;
    // const INTERVAL_30_DAYS = 20;
    // const INTERVAL_7_DAYS = 30;
    // const INTERVAL_DAY = 40;
    // const INTERVAL_24_HOURS = 100;
    // const INTERVAL_12_HOURS = 110;
    // const INTERVAL_6_HOURS = 120;

    //Stores the api key
    private $token_key;
    private $host_key;

    //Stores the email login
    private $email;

    /**
     * Make a new instance of the API client
     */
    public function __construct()
    {
        $num_args = func_num_args();

        if ($num_args >= 2) {
            $parameters = func_get_args();
            if ($num_args === 2) {
                $this->email     = $parameters[0];
                $this->token_key = $parameters[1];
            } else if ($num_args === 3) {
                $this->email     = $parameters[0];
                $this->token_key = $parameters[1];
                $this->host_key  = $parameters[2];
            }
        }
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function setToken($token_key)
    {
        $this->token_key = $token_key;
    }


    /**
     * Zone
     * A Zone is a domain name along with its subdomains and other identities
     */

    /**
     * Create a zone (permission needed: #zone:edit)
     * @param  string   $domain       The domain name
     * @param  boolean  $jump_start   Automatically attempt to fetch existing DNS records
     * @param  stdClass $organization Organization that this zone will belong to
     */
    public function create_zone($name, $jump_start = true, $organization = new stdClass())
    {
        $data = array(
            'name'         => $name,
            'jump_start'   => $jump_start,
            'organization' => $organization
        );
        return $this->http_post('zones', $data);
    }

    /**
     * List zones permission needed: #zone:read
     * List, search, sort, and filter your zones
     * @param  string  $name      A domain name
     * @param  string  $status    Status of the zone (active, pending, initializing, moved, deleted)
     * @param  integer $page      Page number of paginated results
     * @param  integer $per_page  Number of zones per page
     * @param  string  $order     Field to order zones by (name, status, email)
     * @param  string  $direction Direction to order zones (asc, desc)
     * @param  string  $match     Whether to match all search requirements or at least one (any) (any, all)
     */
    public function list_zones($name = '', $status = 'active', $page = 1, $per_page = 20, $order = 'status', $direction = 'desc', $match = 'all')
    {
        $data = array(
            'name'      => $name,
            'status'    => $status,
            'page'      => $page,
            'per_page'  => $per_page,
            'order'     => $order,
            'direction' => $direction,
            'match'     => $match
        );
        return $this->http_get('zones', $data);
    }

    /**
     * Zone details (permission needed: #zone:read)
     * @param  string $identifier API item identifier tag
     */
    public function zone_details($identifier)
    {
        return $this->http_get('zones/' . $identifier);
    }

    /**
     * Pause all CloudFlare features (permission needed: #zone:edit)
     * This will pause all features and settings for the zone. DNS will still resolve
     * @param  string $identifier API item identifier tag
     */
    public function pause_zone($identifier)
    {
        return $this->http_put('zones/' . $identifier . '/pause');
    }

    /**
     * Re-enable all CloudFlare features (permission needed: #zone:edit)
     * This will restore all features and settings for the zone
     * @param  string $identifier API item identifier tag
     */
    public function unpause_zone($identifier)
    {
        return $this->http_put('zones/' . $identifier . '/unpause');
    }

    /**
     * Delete a zone (permission needed: #zone:edit)
     * @param  string $identifier API item identifier tag
     */
    public function delete_zone($identifier)
    {
        return $this->http_delete('zones/' . $identifier);
    }

    /**
     * Purge all files (permission needed: #zone:edit)
     * Remove ALL files from CloudFlare's cache
     * @param  string  $identifier API item identifier tag
     * @param  boolean A flag that indicates all resources in CloudFlare's cache should be removed.
     *                 Note: This may have dramatic affects on your origin server load after
     *                 performing this action. (true)
     */
    public function purge($identifier, $purge_everything = true)
    {
        $data = array(
            'purge_everything' => $purge_everything
        );
        return $this->http_put('zones/' . $identifier . '/purge_cache', $data);
    }

    /**
     * Purge individual files (permission needed: #zone:edit)
     * Remove one or more files from CloudFlare's cache
     * @param  string $identifier API item identifier tag
     * @param  array  $files      An array of URLs that should be removed from cache
     */
    public function purge_files($identifier, array $files)
    {
        $data = array(
            'files' => $files
        );
        return $this->http_delete('zones/' . $identifier . '/purge_cache', $data);
    }

    /**
     * Zone Plan
     */

    /**
     * Available plans (permission needed: #billing:read)
     * List all plans the zone can subscribe to.
     * @param  string $zone_identifier
     */
    public function available_plans($zone_identifier)
    {
        return $this->http_get('zones/' . $identifier . '/plans');
    }

    /**
     * Available plans (permission needed: #billing:read)
     * @param  string $zone_identifier
     * @param  string $identifier      API item identifier tag
     */
    public function plan_details($zone_identifier, $identifier)
    {
        return $this->http_get('zones/' . $zone_identifier . '/plans/' . $identifier);
    }

    /**
     * Change plan (permission needed: #billing:edit)
     * Change the plan level for the zone. This will cancel any previous subscriptions and subscribe the zone to the new plan.
     * @param  string $zone_identifier
     * @param  string $identifier      API item identifier tag
     */
    public function change_plan($zone_identifier, $identifier)
    {
        return $this->http_put('zones/' . $zone_identifier . '/plans/' . $identifier . '/subscribe');
    }

    /**
     * DNS Record
     */

    /**
     * Create DNS record (permission needed: #dns_records:edit)
     * @param  string  $zone_identifier
     * @param  string  $type    DNS record type (A, AAAA, CNAME, TXT, SRV, LOC, MX, NS, SPF)
     * @param  string  $name    DNS record name
     * @param  string  $content DNS record content
     * @param  integer $ttl     Time to live for DNS record. Value of 1 is 'automatic'
     */
    public function create_dns_record($zone_identifier, $type, $name = '', $content = '', $ttl = 120)
    {
        $data = array(
            'type'    => $type,
            'name'    => $name,
            'content' => $content,
            'ttl'     => $ttl
        );

        return $this->http_post('/zones/' . $zone_identifier . '/dns_records', $data);
    }

    /**
     * List DNS Records (permission needed: #dns_records:read)
     * List, search, sort, and filter a zones' DNS records.
     * @param  string  $zone_identifier
     * @param  string  $type                      DNS record type (A, AAAA, CNAME, TXT, SRV, LOC, MX, NS, SPF)
     * @param  string  $name                      DNS record name
     * @param  string  $content                   DNS record content
     * @param  string  $vanity_name_server_record Flag for records that were created for the vanity name server feature (true, false)
     * @param  integer $page                      Page number of paginated results
     * @param  integer $per_page                  Number of DNS records per page
     * @param  string  $order                     Field to order records by (type, name, content, ttl, proxied)
     * @param  string  $direction                 Direction to order domains (asc, desc)
     * @param  string  $match                     Whether to match all search requirements or at least one (any) (any, all)
     */
    public function list_dns_records($zone_identifier, $type, $name = '', $content = '', $vanity_name_server_record = 'true', $page = 1, $per_page = 20, $order = 'type', $direction = 'desc', $match = 'all')
    {
        $data = array(
            'type'                      => $type,
            'name'                      => $name,
            'content'                   => $content,
            'vanity_name_server_record' => $vanity_name_server_record,
            'page'                      => $page,
            'per_page'                  => $per_page,
            'order'                     => $order,
            'direction'                 => $direction,
            'match'                     => $match
        );

        return $this->http_get('/zones/' . $zone_identifier . '/dns_records', $data);
    }

    /**
     * DNS record details (permission needed: #dns_records:read)
     * @param  string $zone_identifier
     * @param  string $identifier      API item identifier tag
     */
    public function dns_record_details($zone_identifier, $identifier)
    {
        return $this->http_get('zones/' . $zone_identifier . '/dns_records/' . $identifier);
    }

    /**
     * Update DNS record (permission needed: #dns_records:edit)
     * @param  string $zone_identifier
     * @param  string $identifier      API item identifier tag
     */
    public function dns_update_record_details($zone_identifier, $identifier)
    {
        return $this->http_put('zones/' . $zone_identifier . '/dns_records/' . $identifier);
    }

    /**
     * Update DNS record (permission needed: #dns_records:edit)
     * @param  string $zone_identifier
     * @param  string $identifier      API item identifier tag
     */
    public function dns_delete_record($zone_identifier, $identifier)
    {
        return $this->http_delete('zones/' . $zone_identifier . '/dns_records/' . $identifier);
    }


    /**
     * GLOBAL API CALL methods
     */
    private function http_get($path, $data = array())
    {
        return $this->http_request($path, $data, 'get');
    }

    private function http_post($path, $data = array())
    {
        return $this->http_request($path, $data, 'post');
    }

    private function http_put($path, $data = array())
    {
        return $this->http_request($path, $data, 'put');
    }

    private function http_delete($path, $data = array())
    {
        return $this->http_request($path, $data, 'delete');
    }

    private function http_request($path, $data = array(), $method = 'get') {

        $url = self::$URL . $path;
        $headers = array("X-Auth-Email: {$this->email}", "X-Auth-Key: {$this->token_key}");

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_VERBOSE, 0);
        curl_setopt($ch, CURLOPT_FORBID_REUSE, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, self::TIMEOUT);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        if( $method === 'post' ) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_POST, true);
        } else if ( $method === 'put' ) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data, '', '&'));
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
            //curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-HTTP-Method-Override: PUT'));
        } else if ( $method === 'delete' ) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data, '', '&'));
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        } else if ($method === 'patch') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH");
            //$headers[] = "Content-type: application/json";
        } else {
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data, '', '&'));
            $url .= '?' . http_build_query($data);
        }

        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_URL, $url);

        $http_result = curl_exec($ch);
        $error       = curl_error($ch);
        $http_code   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($http_code != 200) {
            return array(
                'error' => $error
            );
        } else {
            return json_decode($http_result);
        }
    }
}
