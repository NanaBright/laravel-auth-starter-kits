<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class CustomSmsService
{
    private $gatewayUrl;
    private $username;
    private $password;
    private $senderId;

    public function __construct()
    {
        // These would be your SMS gateway credentials
        $this->gatewayUrl = config('sms.gateway_url', 'https://api.your-sms-gateway.com/send');
        $this->username = config('sms.username');
        $this->password = config('sms.password');
        $this->senderId = config('sms.sender_id', 'YourApp');
    }

    /**
     * Send SMS using custom gateway
     */
    public function sendSms($phoneNumber, $message)
    {
        try {
            $method = config('sms.default_method', 'http_api');
            
            switch ($method) {
                case 'http_api':
                    return $this->sendViaHttpApi($phoneNumber, $message);
                case 'smpp':
                    return $this->sendViaSmpp($phoneNumber, $message);
                case 'email_gateway':
                    return $this->sendViaEmailGateway($phoneNumber, $message);
                default:
                    // For development - just log the SMS
                    return $this->sendViaLogger($phoneNumber, $message);
            }
            
        } catch (Exception $e) {
            Log::error('SMS sending failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Send SMS via HTTP API Gateway
     */
    private function sendViaHttpApi($phoneNumber, $message)
    {
        $response = Http::timeout(30)->post($this->gatewayUrl, [
            'username' => $this->username,
            'password' => $this->password,
            'sender' => $this->senderId,
            'recipient' => $this->formatPhoneNumber($phoneNumber),
            'message' => $message,
            'type' => 'text'
        ]);

        if ($response->successful()) {
            $data = $response->json();
            
            // Check if the gateway returned success
            if (isset($data['status']) && $data['status'] === 'success') {
                return [
                    'success' => true,
                    'message_id' => $data['message_id'] ?? null,
                    'cost' => $data['cost'] ?? null
                ];
            }
        }

        throw new Exception('Gateway returned error: ' . $response->body());
    }

    /**
     * Send SMS via SMPP Protocol (for direct carrier connection)
     */
    private function sendViaSmpp($phoneNumber, $message)
    {
        // This requires an SMPP library like php-smpp
        // composer require onlinecity/php-smpp
        
        /*
        $transport = new SocketTransport(['127.0.0.1'], 2775);
        $transport->setRecvTimeout(60000);
        $smpp = new SmppClient($transport);
        
        $transport->open();
        $smpp->bindTransceiver($this->username, $this->password);
        
        $message_id = $smpp->sendSMS(
            new SmppAddress($this->senderId, SMPP::TON_ALPHANUMERIC),
            new SmppAddress($phoneNumber, SMPP::TON_INTL),
            $message
        );
        
        $smpp->close();
        
        return [
            'success' => true,
            'message_id' => $message_id
        ];
        */
        
        throw new Exception('SMPP method not implemented. Install php-smpp library first.');
    }

    /**
     * Send SMS via Email-to-SMS Gateway (for testing)
     */
    private function sendViaEmailGateway($phoneNumber, $message)
    {
        // Many carriers support email-to-SMS
        $carriers = [
            // US Carriers
            'verizon' => '@vtext.com',
            'att' => '@txt.att.net',
            'tmobile' => '@tmomail.net',
            'sprint' => '@messaging.sprintpcs.com',
            // Add more carriers as needed
        ];

        // For demo purposes, we'll use a default carrier
        $emailAddress = $phoneNumber . '@vtext.com';
        
        try {
            // Send email (requires mail configuration)
            \Mail::raw($message, function($mail) use ($emailAddress) {
                $mail->to($emailAddress)
                     ->subject('Verification Code');
            });

            return [
                'success' => true,
                'method' => 'email_gateway',
                'message_id' => 'email_' . time()
            ];
        } catch (\Exception $e) {
            // Fall back to logging for development
            Log::info("SMS to {$phoneNumber}: {$message}");
            return [
                'success' => true,
                'method' => 'logger_fallback',
                'message_id' => 'log_' . time()
            ];
        }
    }

    /**
     * Send SMS via Logger (for development/testing)
     */
    private function sendViaLogger($phoneNumber, $message)
    {
        Log::info("SMS to {$phoneNumber}: {$message}");
        
        return [
            'success' => true,
            'method' => 'logger',
            'message_id' => 'log_' . time()
        ];
    }

    /**
     * Format phone number for international sending
     */
    private function formatPhoneNumber($phoneNumber)
    {
        // Remove all non-numeric characters
        $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);
        
        // Add country code if not present (assuming US)
        if (strlen($phoneNumber) === 10) {
            $phoneNumber = '1' . $phoneNumber;
        }
        
        // Ensure it starts with +
        if (!str_starts_with($phoneNumber, '+')) {
            $phoneNumber = '+' . $phoneNumber;
        }
        
        return $phoneNumber;
    }

    /**
     * Send OTP SMS
     */
    public function sendOtp($phoneNumber, $otp)
    {
        $message = "Your verification code is: {$otp}. This code will expire in 10 minutes. Do not share this code with anyone.";
        
        return $this->sendSms($phoneNumber, $message);
    }

    /**
     * Validate phone number format
     */
    public function validatePhoneNumber($phoneNumber)
    {
        $cleaned = preg_replace('/[^0-9]/', '', $phoneNumber);
        
        // US phone number validation (10 or 11 digits)
        if (strlen($cleaned) === 10 || (strlen($cleaned) === 11 && str_starts_with($cleaned, '1'))) {
            return true;
        }
        
        return false;
    }
}
