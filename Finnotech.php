<?php
class Finnotech
{
    private $token = '';
    private $appName = '';
    private $appPassword = '';

    function __construct($token, $appName, $appPassword)
    {
        $this->token = $token;
        $this->appName = $appName;
        $this->appPassword = $appPassword;
    }

    public function checkIBAN($iban)
    {
        $trackID = uniqid();

        if (
            !str_starts_with($iban, "IR") &&
            !str_starts_with($iban, "ir")
        ) {
            $iban = "IR" . $iban;
        }

        $address = "https://apibeta.finnotech.ir/oak/v2/clients/kamexweb/ibanInquiry?trackId=$trackID&iban=$iban";
        return $this->callUrl($address);
    }

    public function callUrl($address)
    {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $address,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => [
                "authorization: Bearer " . $this->token
            ],
        ]);

        $response = curl_exec($curl);

        return $response;
    }

    public function getIBANName($iban)
    {
        $result = $this->checkIBAN($iban);

        $result = json_decode($result, true);
        if (array_key_exists('status', $result) && ($result['status'] == 'DONE' || $result['status'] == true)) {
            return [
                'status' => true,
                'result' => $result['result']
            ];
        }
        return [
            'status' => false,
            'result' => $result
        ];
    }

    public  function getCardName($cardID)
    {
        $result = $this->checkCard($cardID);
        $result = json_decode($result, true);
        if (array_key_exists('status', $result) && ($result['status'] == 'DONE' || $result['status'] == true)) {
            return [
                'status' => true,
                'result' => $result['result']
            ];
        }
        return [
            'status' => false,
            'result' => $result
        ];
    }

    public  function checkCard($cardID)
    {
        $trackID = uniqid();
        $cardID = str_replace("-", '', $cardID);
        $cardID = str_replace(" ", '', $cardID);
        $cardID = str_replace("/", '', $cardID);
        $address = "https://apibeta.finnotech.ir/mpg/v2/clients/kamexweb/cards/{$cardID}?trackId=$trackID";
        return $this->callUrl($address);
    }

    public  function callUrl2($address)
    {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $address,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => [
                "authorization: Basic " . base64_encode("{$this->appName}:{$this->appPassword}")
            ],
        ]);

        $response = curl_exec($curl);

        return $response;
    }

    public  function callUrl3($address, $data)
    {
        $jsonData = json_encode($data);
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $address,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_POST => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_POSTFIELDS => $jsonData,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                "authorization: Basic " . base64_encode("{$this->appName}:{$this->appPassword}"),
                'Content-Length: ' . strlen($jsonData)
            ],
        ]);

        $response = curl_exec($curl);

        return $response;
    }

    public  function callUrl4($address, $token)
    {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $address,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "authorization: Bearer " . $token
            ),
        ]);

        $response = curl_exec($curl);

        return $response;
    }


    public function sendSmsCode($mobile)
    {
        $address = "https://apibeta.finnotech.ir/dev/v2/oauth2/authorize?client_id=kamexweb&response_type=code&redirect_uri=https://dev.kamex.ir/finnotech/callback&scope=facility:sms-nid-verification:get&mobile=$mobile&&auth_type=SMS";
        $result = $this->callUrl2($address);
        $result = json_decode($result, true);
        if (
            array_key_exists('status', $result) &&
            $result['status'] == 'DONE'
        ) {
            return $result['result']['trackId'];
        }
        return null;
    }

    public function verifySms($mobile, $code, $nid, $trackID)
    {
        $address = "https://apibeta.finnotech.ir/dev/v2/oauth2/verify/sms";
        $result = $this->callUrl3($address, [
            "mobile" => $mobile,
            "otp" => $code,
            "trackId" => $trackID,
            "nid" => $nid
        ]);
        $result = json_decode($result, true);
        if (
            array_key_exists('status', $result) &&
            $result['status'] == 'DONE'
        ) {
            return $result['result']['code'];
        }
        return null;
    }

    public function getSmsToken($finnotechToken)
    {
        $address = "https://apibeta.finnotech.ir/dev/v2/oauth2/token";
        $data = [
            "grant_type" => "authorization_code",
            "code" => $finnotechToken,
            "auth_type" => "SMS",
            "redirect_uri" => "https://dev.kamex.ir/finnotech/callback"
        ];
        $result = $this->callUrl3($address, $data);

        $result = json_decode($result, true);

        if ($result['status'] != 'DONE') {
            return null;
        }

        return $result['result']['value'];
    }

    public function verifyUser($userData, $token)
    {
        try {
            foreach ($userData as $key => $value) {
                if ($key != "national_code") {
                    $userData[$key] = urlencode($value);
                }
            }
            $trackID = uniqid('NID-');
            $address = "https://apibeta.finnotech.ir/facility/v2/clients/kamexweb/users/{$userData['national_code']}/sms/nidVerification?trackId={$trackID}&birthDate={$userData['birthdate']}&firstName={$userData['firstname']}&lastName={$userData['surname']}";
            $result = $this->callUrl4($address, $token);

            if ($result == null) {
                return null;
            }
            $result = json_decode($result, true);

            if (!array_key_exists('status', $result) || $result['status'] != 'DONE') {
                return [
                    'status' => false,
                    'details' => $result
                ];
            }
            if (
                $result['result']['firstNameSimilarity'] == 100 &&
                $result['result']['lastNameSimilarity'] == 100
            ) {
                return [
                    'status' => true,
                    'details' => $result
                ];
            }
            return [
                'status' => false,
                'details' => $result
            ];
        } catch (\Exception $exception) {
            return [
                'status' => false,
                'details' => $exception->getMessage()
            ];
        }
        return [
            'status' => false,
            'details' => 'EMPTY'
        ];
    }
}
