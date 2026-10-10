<!-- 1. MAIN CORE -->
@can('view_dashboard')
    <x-nav.link route="dashboard" icon="home">{{ __('admin.Dashboard') }}</x-nav.link>
@endcan

@can('view_shop_front_page_settings')
    <x-nav.link route="admin.shop-front-page-settings.index" icon="computer-desktop">{{ __('Faqja Ime (Front Page)') }}</x-nav.link>
@endcan

<!-- 2. SALON MANAGEMENT -->
@if(can('view_bookings') || can('view_barbers') || can('view_services') || can('view_customers') || can('view_working_hours') || can('view_payments') || can('view_reviews'))
    <x-nav.divider>{{ __('Menaxhimi i Sallonit') }}</x-nav.divider>
@endif

@can('view_bookings')
    <x-nav.link route="admin.bookings.index" icon="chart-bar">{{ __('bookings.Bookings') }}</x-nav.link>
@endcan

@can('view_barbers')
    <x-nav.link route="admin.barbers.index" icon="user">{{ __('barbers.Barbers') }}</x-nav.link>
@endcan

@can('view_services')
    <x-nav.link route="admin.services.index" icon="pencil-square">{{ __('services.Services') }}</x-nav.link>
@endcan

@can('view_customers')
    <x-nav.link route="admin.customers.index" icon="users">{{ __('customers.Customers') }}</x-nav.link>
@endcan

@can('view_working_hours')
    <x-nav.link route="admin.working-hours.index" icon="clock">{{ __('working-hours.WorkingHours') }}</x-nav.link>
@endcan

@can('view_payments')
    <x-nav.link route="admin.payments.index" icon="archive-box">{{ __('payments.Payments') }}</x-nav.link>
@endcan

@can('view_reviews')
    <x-nav.link route="admin.reviews.index" icon="pencil-square">{{ __('reviews.Reviews') }}</x-nav.link>
@endcan

<!-- 3. SMS GATEWAY & NOTIFICATIONS -->
@if(can('view_message_queues') || can('view_message_logs') || can('view_message_templates') || can('view_event_settings') || can('view_device_tokens'))
    <x-nav.divider>{{ __('SMS & Njoftimet') }}</x-nav.divider>
@endif

@can('view_message_queues')
    <x-nav.link route="admin.message-queues.index" icon="bell">{{ __('message-queues.MessageQueues') }}</x-nav.link>
@endcan

@can('view_message_logs')
    <x-nav.link route="admin.message-logs.index" icon="document">{{ __('message-logs.MessageLogs') }}</x-nav.link>
@endcan

@can('view_message_templates')
    <x-nav.link route="admin.message-templates.index" icon="document">{{ __('message-templates.MessageTemplates') }}</x-nav.link>
@endcan

@can('view_event_settings')
    <x-nav.link route="admin.event-settings.index" icon="bolt">{{ __('event-settings.EventSettings') }}</x-nav.link>
@endcan

@can('view_device_tokens')
    <x-nav.link route="admin.device-tokens.index" icon="bolt">{{ __('device-tokens.DeviceTokens') }}</x-nav.link>
@endcan

@can('view_notification_channels')
    <x-nav.link route="admin.notification-channels.index" icon="bell">{{ __('notification-channels.NotificationChannels') }}</x-nav.link>
@endcan

<!-- 4. SAAS ADMINISTRATION -->
@if(can('view_barber_shops') || can('view_subscriptions') || can('view_plans') || can('view_users'))
    <x-nav.divider>{{ __('Administrimi SaaS') }}</x-nav.divider>
@endif

@can('view_barber_shops')
    <x-nav.link route="admin.barber-shops.index" icon="building-office">{{ __('barber-shops.BarberShops') }}</x-nav.link>
@endcan

@can('view_subscriptions')
    <x-nav.link route="admin.subscriptions.index" icon="clipboard-document">{{ __('subscriptions.Subscriptions') }}</x-nav.link>
    <x-nav.link route="admin.subscription-renewals.index" icon="arrow-path">Renovimi i Abonimit</x-nav.link>
@endcan

@can('view_plans')
    <x-nav.link route="admin.plans.index" icon="chart-bar">{{ __('plans.Plans') }}</x-nav.link>
@endcan

@can('view_users')
    <x-nav.link route="admin.users.index" icon="users">{{ __('admin.Users') }}</x-nav.link>
@endcan

<!-- 5. SYSTEM SETTINGS -->
@if(can('view_system_settings') || can('view_roles') || can('view_audit_trails'))
    <x-nav.divider>{{ __('admin.Settings') }}</x-nav.divider>
@endif

@can('view_roles')
    <x-nav.link route="admin.settings.roles.index" icon="archive-box">{{ __('admin.Roles') }}</x-nav.link>
@endcan

@can('view_system_settings')
    <x-nav.link route="admin.settings.ai-assistant" icon="cpu-chip">{{ __('AI Assistant') }}</x-nav.link>
@endcan

@can('view_system_settings')
    <x-nav.link route="admin.settings.languages.index" icon="language">{{ __('admin.Languages') }}</x-nav.link>
@endcan

@can('view_system_settings')
    <x-nav.link route="admin.settings.notifications" icon="bell">{{ __('admin.Notifications') }}</x-nav.link>
@endcan

@can('view_system_settings')
    <x-nav.link route="admin.settings" icon="wrench-screwdriver">{{ __('admin.System Settings') }}</x-nav.link>
@endcan

@can('view_audit_trails')
    <x-nav.link route="admin.settings.audit-trails.index" icon="identification">{{ __('admin.Audit Trails') }}</x-nav.link>
@endcan
