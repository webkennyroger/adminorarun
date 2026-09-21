<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    /**
     * List all active plans.
     */
    public function index()
    {
        $plans = SubscriptionPlan::active()->get()->map(function ($plan) {
            return [
                'id' => $plan->id,
                'name' => $plan->name,
                'slug' => $plan->slug,
                'price' => $plan->price,
                'formatted_price' => $plan->formatted_price,
                'billing_period' => $plan->billing_period,
                'features' => $plan->features,
                'stripe_plan_id' => $plan->stripe_plan_id,
            ];
        });

        return response()->json($plans);
    }

    /**
     * Get current user subscription status.
     */
    public function status(Request $request)
    {
        $user = $request->user();

        if (! $user->subscribed('default')) {
            return response()->json([
                'subscribed' => false,
                'plan' => null,
            ]);
        }

        $subscription = $user->subscription('default'); // Default subscription name
        $stripeSubscription = $subscription->asStripeSubscription();

        // Find the internal plan info if possible mainly for name display
        $planId = $stripeSubscription->items->data[0]->price->id;
        $localPlan = SubscriptionPlan::where('stripe_plan_id', $planId)->first();

        return response()->json([
            'subscribed' => true,
            'status' => $stripeSubscription->status, // active, trialing, past_due, etc.
            'plan' => [
                'name' => $localPlan ? $localPlan->name : 'Unknown Plan',
                'stripe_id' => $planId,
            ],
            'ends_at' => $subscription->ends_at,
            'on_grace_period' => $subscription->onGracePeriod(),
            'renews_at' => $stripeSubscription->current_period_end
                ? Carbon::createFromTimestamp($stripeSubscription->current_period_end)->toIso8601String()
                : null,
        ]);
    }

    /**
     * Create a new subscription intent using Stripe Payment Element/Mobile SDK.
     * This creates the subscription as incomplete and returns the client secret for the frontend to confirm.
     */
    public function subscribe(Request $request)
    {
        $request->validate([
            'plan_id' => 'required', // We expect the local database ID or slug, or stripe ID. Let's use local ID for simplicity.
        ]);

        $user = $request->user();
        $plan = SubscriptionPlan::findOrFail($request->plan_id);

        if ($user->subscribed('default')) {
            // Swap logic could be here, but for now simple error or handled
            return response()->json(['message' => 'User already subscribed'], 400);
        }

        try {
            // Create the subscription. Cashier will create it in 'incomplete' state
            // and return the logic needed to confirm it.
            // For mobile, we often just need the payment intent or setup intent.
            // Cashier's `newSubscription` followed by `create` usually charges immediately if method is present.
            // For mobile "PaymentSheet", we want to pass the latest_invoice.payment_intent.client_secret

            $subscription = $user->newSubscription('default', $plan->stripe_plan_id)
                ->create(null, [], [
                    'payment_behavior' => 'default_incomplete',
                    'payment_settings' => ['save_default_payment_method' => 'on_subscription'],
                    'expand' => ['latest_invoice.payment_intent'],
                ]);

            $invoice = $subscription->latestInvoice;
            $paymentIntent = $invoice->payment_intent;

            return response()->json([
                'subscriptionId' => $subscription->id,
                'clientSecret' => $paymentIntent->client_secret,
                'ephemeralKey' => '', // You might need to generate an ephemeral key for the customer if strictly following Stripe Mobile docs
                'customer' => $user->stripe_id,
            ]);

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
