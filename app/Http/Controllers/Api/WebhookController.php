<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessIncomingWhatsAppMessage;
use App\Services\WhatsAppWebhookService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

/**
 * @OA\Tag(
 *     name="Webhooks",
 *     description="WhatsApp Business API webhook endpoints"
 * )
 */
class WebhookController extends Controller
{
    public function __construct(
        private WhatsAppWebhookService $webhookService
    ) {}

    /**
     * @OA\Get(
     *     path="/api/webhooks/whatsapp/verify",
     *     summary="Verify WhatsApp webhook",
     *     tags={"Webhooks"},
     *     @OA\Parameter(
     *         name="hub.mode",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="hub.verify_token",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="hub.challenge",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Webhook verified",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Invalid verify token"
     *     )
     * )
     */
    public function verifyWhatsApp(Request $request): Response
    {
        try {
            $mode = $request->query('hub.mode');
            $token = $request->query('hub.verify_token');
            $challenge = $request->query('hub.challenge');

            if ($mode === 'subscribe' && $this->webhookService->verifyToken($token)) {
                Log::info('WhatsApp webhook verified successfully');
                return response($challenge, 200);
            }

            Log::warning('WhatsApp webhook verification failed', [
                'mode' => $mode,
                'token' => $token,
            ]);

            return response('Forbidden', 403);
        } catch (\Exception $e) {
            Log::error('WhatsApp webhook verification error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response('Internal Server Error', 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/webhooks/whatsapp",
     *     summary="Handle WhatsApp webhook events",
     *     tags={"Webhooks"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="object", type="string", example="whatsapp_business_account"),
     *             @OA\Property(
     *                 property="entry",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="string"),
     *                     @OA\Property(
     *                         property="changes",
     *                         type="array",
     *                         @OA\Items(
     *                             @OA\Property(property="field", type="string", example="messages"),
     *                             @OA\Property(property="value", type="object")
     *                         )
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Webhook processed successfully"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request - Invalid payload"
     *     )
     * )
     */
    public function handleWhatsApp(Request $request): Response
    {
        try {
            $payload = $request->all();

            Log::info('WhatsApp webhook received', [
                'payload' => $payload,
                'headers' => $request->headers->all()
            ]);

            // Validate webhook signature
            if (!$this->webhookService->validateSignature($request)) {
                Log::warning('WhatsApp webhook signature validation failed');
                return response('Unauthorized', 401);
            }

            // Process webhook payload
            $this->webhookService->processWebhook($payload);

            return response('OK', 200);
        } catch (\Exception $e) {
            Log::error('WhatsApp webhook processing error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'payload' => $request->all()
            ]);

            return response('Internal Server Error', 500);
        }
    }
}
