@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Alerts" />

    <div class="space-y-5 sm:space-y-6">
        {{-- Success Alert --}}
        <x-common.component-card title="Success Alert">
            <div class="space-y-4">
                <x-ui.alert variant="success" title="Success Message">
                    Be cautious when performing this action.
                    <a href="/" class="inline-block mt-2 text-sm font-medium underline">Learn more</a>
                </x-ui.alert>

                <x-ui.alert variant="success" title="Success Message">
                    Be cautious when performing this action.
                </x-ui.alert>
            </div>
        </x-common.component-card>

        {{-- Warning Alert --}}
        <x-common.component-card title="Warning Alert">
            <div class="space-y-4">
                <x-ui.alert variant="warning" title="Warning Message">
                    Be cautious when performing this action.
                    <a href="/" class="inline-block mt-2 text-sm font-medium underline">Learn more</a>
                </x-ui.alert>

                <x-ui.alert variant="warning" title="Warning Message">
                    Be cautious when performing this action.
                </x-ui.alert>
            </div>
        </x-common.component-card>

        {{-- Danger Alert --}}
        <x-common.component-card title="Danger Alert">
            <div class="space-y-4">
                <x-ui.alert variant="danger" title="Error Message">
                    Be cautious when performing this action.
                    <a href="/" class="inline-block mt-2 text-sm font-medium underline">Learn more</a>
                </x-ui.alert>

                <x-ui.alert variant="danger" title="Error Message">
                    Be cautious when performing this action.
                </x-ui.alert>
            </div>
        </x-common.component-card>

        {{-- Info Alert --}}
        <x-common.component-card title="Info Alert">
            <div class="space-y-4">
                <x-ui.alert variant="info" title="Info Message">
                    Be cautious when performing this action.
                    <a href="/" class="inline-block mt-2 text-sm font-medium underline">Learn more</a>
                </x-ui.alert>

                <x-ui.alert variant="info" title="Info Message">
                    Be cautious when performing this action.
                </x-ui.alert>
            </div>
        </x-common.component-card>

        {{-- Additional Examples --}}
        <x-common.component-card title="Alert Variations">
            <div class="space-y-4">
                {{-- Dismissible --}}
                <x-ui.alert variant="success" title="Custom Content Alert" dismissible>
                    <p>
                        This alert uses <strong>custom slot content</strong>
                        instead of the message prop, and can be dismissed.
                    </p>
                    <ul class="mt-2 list-disc list-inside">
                        <li>You can add any HTML content</li>
                        <li>Including lists and formatting</li>
                        <li>Perfect for complex messages</li>
                    </ul>
                </x-ui.alert>

                {{-- Minimal Alert --}}
                <x-ui.alert variant="info" title="Quick Info">
                    Sometimes you just need a simple message.
                </x-ui.alert>

                {{-- Alert with Long Message --}}
                <x-ui.alert variant="warning" title="Important Notice">
                    This is a longer message that provides more detailed information about the warning. You should read this carefully before proceeding with your action.
                    <a href="/docs" class="inline-block mt-2 text-sm font-medium underline">View documentation</a>
                </x-ui.alert>
            </div>
        </x-common.component-card>

        {{-- Interactive Demo --}}
        <x-common.component-card title="Real-World Examples">
            <div class="space-y-4">
                {{-- Payment Success --}}
                <x-ui.alert variant="success" title="Payment Successful" dismissible>
                    <p class="mb-2">
                        Your payment of <strong>$99.00</strong> has been processed successfully.
                    </p>
                    <div>
                        <p><strong>Order ID:</strong> #TAILADMIN-0014</p>
                        <p><strong>Transaction ID:</strong> TXN-1234567890</p>
                    </div>
                    <a href="/orders" class="inline-block mt-3 text-sm font-medium underline">
                        View Order Details
                    </a>
                </x-ui.alert>

                {{-- Account Warning --}}
                <x-ui.alert variant="warning" title="Your trial is ending soon">
                    Your 14-day trial will expire in 3 days. Upgrade now to continue using all features.
                    <a href="/billing" class="inline-block mt-2 text-sm font-medium underline">Upgrade now</a>
                </x-ui.alert>

                {{-- Validation Error --}}
                <x-ui.alert variant="danger" title="Form Validation Failed" dismissible>
                    <ul class="list-disc list-inside space-y-1">
                        <li>Email field is required</li>
                        <li>Password must be at least 8 characters</li>
                        <li>Please accept the terms and conditions</li>
                    </ul>
                </x-ui.alert>

                {{-- System Info --}}
                <x-ui.alert variant="info" title="Scheduled Maintenance">
                    Our system will undergo maintenance on November 15, 2025 from 2:00 AM to 4:00 AM EST. Some features may be unavailable during this time.
                    <a href="/status" class="inline-block mt-2 text-sm font-medium underline">Check status page</a>
                </x-ui.alert>
            </div>
        </x-common.component-card>
    </div>
@endsection
