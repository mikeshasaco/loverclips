<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Purchase;
use App\Models\Scene;
use App\Models\Tip;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;

class PaymentController extends Controller
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * Create a purchase for a post.
     */
    public function purchase(Request $request)
    {
        $validated = $request->validate([
            'post_id' => 'required|exists:posts,id',
        ]);

        $post = Post::findOrFail($validated['post_id']);
        $user = auth()->user();

        // Check if already purchased
        if ($post->isPurchasedBy($user->id)) {
            return response()->json(['message' => 'Already purchased'], 422);
        }

        // Check if post is paid
        if (!$post->is_paid || !$post->price) {
            return response()->json(['message' => 'Post is not available for purchase'], 422);
        }

        // Create Stripe Payment Intent
        try {
            $paymentIntent = PaymentIntent::create([
                'amount' => (int)($post->price * 100), // Convert to cents
                'currency' => 'usd',
                'metadata' => [
                    'user_id' => $user->id,
                    'post_id' => $post->id,
                ],
            ]);

            // Create purchase record (pending)
            $purchase = Purchase::create([
                'user_id' => $user->id,
                'post_id' => $post->id,
                'amount' => $post->price,
                'stripe_payment_intent_id' => $paymentIntent->id,
            ]);

            return response()->json([
                'client_secret' => $paymentIntent->client_secret,
                'purchase_id' => $purchase->id,
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Payment processing failed', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Create a tip for a scene.
     */
    public function tip(Request $request)
    {
        $validated = $request->validate([
            'post_id' => 'required|exists:posts,id',
            'scene_id' => 'required|exists:scenes,id',
            'amount' => 'required|numeric|min:1',
        ]);

        $scene = Scene::with('post')->findOrFail($validated['scene_id']);
        $user = auth()->user();

        // Verify scene belongs to post
        if ($scene->post_id != $validated['post_id']) {
            return response()->json(['message' => 'Scene does not belong to post'], 422);
        }

        // Create Stripe Payment Intent
        try {
            $amount = (float)$validated['amount'];
            $paymentIntent = PaymentIntent::create([
                'amount' => (int)($amount * 100), // Convert to cents
                'currency' => 'usd',
                'metadata' => [
                    'user_id' => $user->id,
                    'post_id' => $validated['post_id'],
                    'scene_id' => $validated['scene_id'],
                    'type' => 'tip',
                ],
            ]);

            return response()->json([
                'client_secret' => $paymentIntent->client_secret,
                'payment_intent_id' => $paymentIntent->id,
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Payment processing failed', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Handle Stripe webhook.
     */
    public function webhook(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $endpointSecret = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
        } catch (SignatureVerificationException $e) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        // Handle the event
        switch ($event->type) {
            case 'payment_intent.succeeded':
                $paymentIntent = $event->data->object;
                $this->handlePaymentSuccess($paymentIntent);
                break;

            case 'payment_intent.payment_failed':
                // Handle failed payment
                break;
        }

        return response()->json(['received' => true]);
    }

    /**
     * Handle successful payment.
     */
    private function handlePaymentSuccess($paymentIntent)
    {
        $metadata = $paymentIntent->metadata;

        if (isset($metadata->type) && $metadata->type === 'tip') {
            // Handle tip
            Tip::create([
                'user_id' => $metadata->user_id,
                'post_id' => $metadata->post_id,
                'scene_id' => $metadata->scene_id,
                'amount' => $paymentIntent->amount / 100, // Convert from cents
            ]);
        } else {
            // Handle purchase
            $purchase = Purchase::where('stripe_payment_intent_id', $paymentIntent->id)->first();
            if ($purchase) {
                // Purchase is already created, just mark as completed if needed
                // You might want to add a status field to track payment status
            }
        }
    }
}
