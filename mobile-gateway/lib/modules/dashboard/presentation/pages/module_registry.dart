import 'package:flutter/material.dart';
import 'package:mobile_gateway/l10n/core_localization.dart';
import 'package:mobile_gateway/core/branding/branding_cubit.dart';
import '../../barber_shop/presentation/pages/barber_shop_list_page.dart';
import '../../plan/presentation/pages/plan_list_page.dart';
import '../../subscription/presentation/pages/subscription_list_page.dart';
import '../../barber/presentation/pages/barber_list_page.dart';
import '../../service/presentation/pages/service_list_page.dart';
import '../../customer/presentation/pages/customer_list_page.dart';
import '../../notification_channel/presentation/pages/notification_channel_list_page.dart';
import '../../event_setting/presentation/pages/event_setting_list_page.dart';
import '../../message_template/presentation/pages/message_template_list_page.dart';
import '../../booking/presentation/pages/booking_list_page.dart';
import '../../payment/presentation/pages/payment_list_page.dart';
import '../../message_queue/presentation/pages/message_queue_list_page.dart';
import '../../message_log/presentation/pages/message_log_list_page.dart';
import '../../device_token/presentation/pages/device_token_list_page.dart';
import '../../review/presentation/pages/review_list_page.dart';
import '../../working_hour/presentation/pages/working_hour_list_page.dart';
// [REGISTRY_IMPORTS]

class ModuleEntry {
  final String key;
  final String name;
  final IconData icon;
  final Widget page;
  final String? permission;

  const ModuleEntry({
    required this.key,
    required this.name,
    required this.icon,
    required this.page,
    this.permission,
  });

  String getTitle(BuildContext context) {
    if (key == 'barber') return context.staffLabelPlural;
    if (key == 'barber_shop') return context.shopLabel;
    final translated = coreTr(context, 'module.$key');
    return translated.startsWith('module.') ? name : translated;
  }
}

class ModuleRegistry {
  static final List<ModuleEntry> modules = [
    ModuleEntry(key: 'barber_shop', name: 'Dyqanet', icon: Icons.storefront_rounded, page: const BarberShopListPage(), permission: 'view_barber_shops'),
    ModuleEntry(key: 'plan', name: 'Planet', icon: Icons.stars_rounded, page: const PlanListPage(), permission: 'view_plans'),
    ModuleEntry(key: 'subscription', name: 'Abonimet', icon: Icons.card_membership_rounded, page: const SubscriptionListPage(), permission: 'view_subscriptions'),
    ModuleEntry(key: 'barber', name: 'Punonjësit', icon: Icons.person_pin_rounded, page: const BarberListPage(), permission: 'view_barbers'),
    ModuleEntry(key: 'service', name: 'Shërbimet', icon: Icons.content_cut_rounded, page: const ServiceListPage(), permission: 'view_services'),
    ModuleEntry(key: 'customer', name: 'Klientët', icon: Icons.people_alt_rounded, page: const CustomerListPage(), permission: 'view_customers'),
    ModuleEntry(key: 'notification_channel', name: 'Kanalet e Njoftimeve', icon: Icons.notifications_active_rounded, page: const NotificationChannelListPage(), permission: 'view_notification_channels'),
    ModuleEntry(key: 'event_setting', name: 'Cilësimet e Ngjarjeve', icon: Icons.bolt_rounded, page: const EventSettingListPage(), permission: 'view_event_settings'),
    ModuleEntry(key: 'message_template', name: 'Shabllonet e Mesazheve', icon: Icons.description_rounded, page: const MessageTemplateListPage(), permission: 'view_message_templates'),
    ModuleEntry(key: 'booking', name: 'Rezervimet', icon: Icons.event_note_rounded, page: const BookingListPage(), permission: 'view_bookings'),
    ModuleEntry(key: 'payment', name: 'Pagesat', icon: Icons.payments_rounded, page: const PaymentListPage(), permission: 'view_payments'),
    ModuleEntry(key: 'message_queue', name: 'Radha e Mesazheve', icon: Icons.forward_to_inbox_rounded, page: const MessageQueueListPage(), permission: 'view_message_queues'),
    ModuleEntry(key: 'message_log', name: 'Logjet e Mesazheve', icon: Icons.mark_email_read_rounded, page: const MessageLogListPage(), permission: 'view_message_logs'),
    ModuleEntry(key: 'device_token', name: 'Tokenat e Pajisjeve', icon: Icons.phonelink_ring_rounded, page: const DeviceTokenListPage(), permission: 'view_device_tokens'),
    ModuleEntry(key: 'review', name: 'Vlerësimet', icon: Icons.star_rounded, page: const ReviewListPage(), permission: 'view_reviews'),
    ModuleEntry(key: 'working_hour', name: 'Orari Javor', icon: Icons.schedule_rounded, page: const WorkingHourListPage(), permission: 'view_working_hours'),
    // [REGISTRY_ENTRIES]
  ];
}
