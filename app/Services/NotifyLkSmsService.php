<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\SmsNotification;
use Exception;

class NotifyLkSmsService
{
    private string $userId;
    private string $apiKey;
    private string $senderId;
    private string $baseUrl;

    public function __construct()
    {
        $this->userId = config('sms.drivers.notify_lk.user_id');
        $this->apiKey = config('sms.drivers.notify_lk.api_key');
        $this->senderId = config('sms.drivers.notify_lk.sender_id');
        $this->baseUrl = config('sms.drivers.notify_lk.base_url', 'https://app.notify.lk/api/v1');
    }

    /**
     * Send SMS to a single phone number
     */
    public function sendSms(
        string $phoneNumber, 
        string $message, 
        ?string $contactFirstName = null,
        ?string $contactLastName = null,
        ?string $contactEmail = null,
        ?string $contactAddress = null,
        bool $unicode = false
    ): array {
        try {
            // Format phone number to Sri Lankan format (94XXXXXXXXX)
            $formattedNumber = $this->formatPhoneNumber($phoneNumber);
            
            if (!$formattedNumber) {
                throw new Exception("Invalid phone number format: {$phoneNumber}");
            }

            $params = [
                'user_id' => $this->userId,
                'api_key' => $this->apiKey,
                'sender_id' => $this->senderId,
                'to' => $formattedNumber,
                'message' => $message,
            ];

            // Add optional contact information
            if ($contactFirstName) $params['contact_fname'] = $contactFirstName;
            if ($contactLastName) $params['contact_lname'] = $contactLastName;
            if ($contactEmail) $params['contact_email'] = $contactEmail;
            if ($contactAddress) $params['contact_address'] = $contactAddress;
            if ($unicode) $params['type'] = 'unicode';

            // Send the request
            $response = Http::timeout(30)->get("{$this->baseUrl}/send", $params);

            $responseData = $response->json();

            // Log the SMS attempt
            $this->logSmsAttempt($phoneNumber, $message, $responseData, $response->status());

            if ($response->successful() && isset($responseData['status']) && $responseData['status'] === 'success') {
                Log::info("SMS sent successfully to {$phoneNumber}");
                return [
                    'success' => true,
                    'message' => 'SMS sent successfully',
                    'response' => $responseData
                ];
            } else {
                $errorMessage = $responseData['message'] ?? 'Unknown error occurred';
                Log::error("SMS failed to {$phoneNumber}: {$errorMessage}");
                return [
                    'success' => false,
                    'message' => $errorMessage,
                    'response' => $responseData
                ];
            }

        } catch (Exception $e) {
            Log::error("SMS Service Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'response' => null
            ];
        }
    }

    /**
     * Send bulk SMS to multiple phone numbers
     */
    public function sendBulkSms(array $recipients): array
    {
        $results = [];
        $successCount = 0;
        $failCount = 0;

        foreach ($recipients as $recipient) {
            $result = $this->sendSms(
                $recipient['phone'],
                $recipient['message'],
                $recipient['first_name'] ?? null,
                $recipient['last_name'] ?? null,
                $recipient['email'] ?? null,
                $recipient['address'] ?? null,
                $recipient['unicode'] ?? false
            );

            if ($result['success']) {
                $successCount++;
            } else {
                $failCount++;
            }

            $results[] = array_merge($recipient, $result);

            // Add a small delay between messages to avoid rate limiting
            usleep(500000); // 0.5 seconds
        }

        return [
            'total' => count($recipients),
            'success_count' => $successCount,
            'fail_count' => $failCount,
            'results' => $results
        ];
    }

    /**
     * Check account status and balance
     */
    public function getAccountStatus(): array
    {
        try {
            $response = Http::timeout(15)->get("{$this->baseUrl}/status", [
                'user_id' => $this->userId,
                'api_key' => $this->apiKey,
            ]);

            $responseData = $response->json();

            if ($response->successful() && isset($responseData['status']) && $responseData['status'] === 'success') {
                return [
                    'success' => true,
                    'active' => $responseData['data']['active'] ?? false,
                    'balance' => $responseData['data']['acc_balance'] ?? 0,
                    'response' => $responseData
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to get account status',
                    'response' => $responseData
                ];
            }

        } catch (Exception $e) {
            Log::error("SMS Account Status Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'response' => null
            ];
        }
    }

    /**
     * Format phone number to Sri Lankan format (94XXXXXXXXX)
     */
    private function formatPhoneNumber(string $phoneNumber): ?string
    {
        // Remove all non-numeric characters
        $cleaned = preg_replace('/[^0-9]/', '', $phoneNumber);

        // Handle different formats
        if (strlen($cleaned) === 10 && substr($cleaned, 0, 1) === '0') {
            // Local format: 0712345678 -> 94712345678
            return '94' . substr($cleaned, 1);
        } elseif (strlen($cleaned) === 9) {
            // Without leading 0: 712345678 -> 94712345678
            return '94' . $cleaned;
        } elseif (strlen($cleaned) === 12 && substr($cleaned, 0, 2) === '94') {
            // Already in international format: 94712345678
            return $cleaned;
        } elseif (strlen($cleaned) === 13 && substr($cleaned, 0, 3) === '+94') {
            // With + prefix: +94712345678 -> 94712345678
            return substr($cleaned, 1);
        }

        // Invalid format
        return null;
    }

    /**
     * Log SMS attempt to database
     */
    private function logSmsAttempt(string $phoneNumber, string $message, ?array $response, int $httpStatus): void
    {
        try {
            SmsNotification::create([
                'phone_number' => $phoneNumber,
                'message' => $message,
                'status' => ($response['status'] ?? 'failed') === 'success' ? 'sent' : 'failed',
                'response_data' => $response,
                'http_status' => $httpStatus,
                'sent_at' => now(),
            ]);
        } catch (Exception $e) {
            Log::error("Failed to log SMS attempt: " . $e->getMessage());
        }
    }

    /**
     * Validate configuration
     */
    public function validateConfiguration(): array
    {
        $errors = [];

        if (empty($this->userId)) {
            $errors[] = 'SMS User ID not configured';
        }

        if (empty($this->apiKey)) {
            $errors[] = 'SMS API Key not configured';
        }

        if (empty($this->senderId)) {
            $errors[] = 'SMS Sender ID not configured';
        }

        return $errors;
    }
} 