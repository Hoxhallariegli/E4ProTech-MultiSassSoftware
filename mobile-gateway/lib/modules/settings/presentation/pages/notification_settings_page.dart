import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import '../../../../core/widgets/premium_widgets.dart';
import '../../../../services/auth_service.dart';
import '../../../../core/branding/branding_cubit.dart';
import '../../data/notification_repository.dart';
import '../cubit/notification_cubit.dart';
import '../cubit/notification_state.dart';

class NotificationSettingsPage extends StatelessWidget {
  const NotificationSettingsPage({super.key});

  @override
  Widget build(BuildContext context) {
    return BlocProvider(
      create: (context) => NotificationCubit(NotificationRepository())..loadSettings(),
      child: const NotificationSettingsView(),
    );
  }
}

class NotificationSettingsView extends StatelessWidget {
  const NotificationSettingsView({super.key});

  String _getRequiredPermission(String moduleName) {
    final key = moduleName.toUpperCase().replaceAll('_', '').replaceAll(' ', '');
    switch (key) {
      case 'BARBER': return 'view_barbers';
      case 'BARBERSHOP': return 'view_barber_shops';
      case 'BOOKING': return 'view_bookings';
      case 'CUSTOMER': return 'view_customers';
      case 'SERVICE': return 'view_services';
      case 'PAYMENT': return 'view_payments';
      case 'WORKINGHOUR': return 'view_working_hours';
      case 'REVIEW': return 'view_reviews';
      case 'PLAN': return 'view_plans';
      case 'SUBSCRIPTION': return 'view_subscriptions';
      case 'EVENTSETTING': return 'view_event_settings';
      case 'NOTIFICATIONCHANNEL': return 'view_notification_channels';
      case 'MESSAGETEMPLATE': return 'view_message_templates';
      case 'MESSAGEQUEUE': return 'view_message_queues';
      case 'MESSAGELOG': return 'view_message_logs';
      case 'DEVICETOKEN': return 'view_device_tokens';
      default: return 'view_dashboard';
    }
  }

  bool _hasModulePermission(String moduleName) {
    if (AuthService.instance.user?['is_admin'] == true) return true;
    final perm = _getRequiredPermission(moduleName);
    return AuthService.instance.hasPermission(perm);
  }

  String _getModuleDisplayTitle(BuildContext context, String moduleName) {
    final key = moduleName.toUpperCase().replaceAll('_', '').replaceAll(' ', '');
    switch (key) {
      case 'BARBER': return context.staffLabelPlural.toUpperCase();
      case 'BARBERSHOP': return context.shopLabel.toUpperCase();
      case 'BOOKING': return 'REZERVIMET & TAKIMET';
      case 'CUSTOMER': return 'KLIENTËT';
      case 'SERVICE': return 'SHËRBIMET';
      case 'PAYMENT': return 'PAGESAT';
      case 'WORKINGHOUR': return 'ORARI JAVOR';
      case 'REVIEW': return 'VLERËSIMET';
      case 'PLAN': return 'PLANET';
      case 'SUBSCRIPTION': return 'ABONIMET';
      case 'NOTIFICATIONCHANNEL': return 'KANALET E NJOFTIMEVE';
      case 'EVENTSETTING': return 'CILËSIMET E NGJARJEVE';
      case 'MESSAGETEMPLATE': return 'SHABLLONET E MESAZHEVE';
      case 'MESSAGEQUEUE': return 'RADHA E MESAZHEVE';
      case 'MESSAGELOG': return 'LOGJET E MESAZHEVE';
      case 'DEVICETOKEN': return 'TOKENAT E PAJISJEVE';
      default: return moduleName.toUpperCase();
    }
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);

    return Scaffold(
      backgroundColor: theme.colorScheme.surface,
      appBar: AppBar(
        title: const Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('Njoftimet e Moduleve', style: TextStyle(fontSize: 18, fontWeight: FontWeight.w900)),
            Text('Konfiguro njoftimet Push Firebase për çdo modul', style: TextStyle(fontSize: 11, color: Colors.grey)),
          ],
        ),
      ),
      body: BlocConsumer<NotificationCubit, NotificationState>(
        listener: (context, state) {
          if (state is NotificationError) {
            ScaffoldMessenger.of(context).showSnackBar(
              SnackBar(content: Text('Gabim: ${state.message}'), backgroundColor: Colors.red),
            );
          }
        },
        builder: (context, state) {
          if (state is NotificationLoading) {
            return const Center(child: CircularProgressIndicator.adaptive());
          }

          if (state is NotificationLoaded) {
            final allowedModules = state.modules
                .where((m) => _hasModulePermission((m['name'] ?? '').toString()))
                .toList();

            final allowedModuleNames = allowedModules
                .map((m) => (m['name'] ?? '').toString().toUpperCase().replaceAll('_', '').replaceAll(' ', ''))
                .toSet();

            final allowedEvents = state.events.where((e) {
              final rawEvent = (e['event'] ?? '').toString();
              final prefix = rawEvent.contains('.') ? rawEvent.split('.').first : rawEvent;
              final moduleKey = prefix.toUpperCase().replaceAll('_', '').replaceAll('-', '').replaceAll('s', '');
              return _hasModulePermission(moduleKey);
            }).toList();

            return ListView(
              padding: const EdgeInsets.all(20),
              children: [
                _buildSectionHeader(theme, 'Modulet (Njoftimet Qendrore)', Icons.apps_rounded),
                const SizedBox(height: 12),
                if (allowedModules.isEmpty)
                  _buildEmptyState('Nuk keni leje për modulet e njoftimeve')
                else
                  ...allowedModules.map((m) => _buildModuleTile(context, m)),

                const SizedBox(height: 30),
                _buildSectionHeader(theme, 'Ngjarjet e Detajuara', Icons.bolt_rounded),
                const SizedBox(height: 12),
                if (allowedEvents.isEmpty)
                  _buildEmptyState('Nuk ka ngjarje të lejuara')
                else
                  ...allowedEvents.map((e) => _buildEventTile(context, e)),

                const SizedBox(height: 40),
                Text(
                  'Sqarim: Përditësimet në kohë reale në ekran mbeten aktive. Këta switch-e kordinojnë vetëm njoftimet Push Firebase.',
                  style: TextStyle(fontSize: 12, color: Colors.grey.shade600, fontStyle: FontStyle.italic),
                  textAlign: TextAlign.center,
                ),
              ],
            );
          }

          return const SizedBox();
        },
      ),
    );
  }

  Widget _buildSectionHeader(ThemeData theme, String title, IconData icon) {
    return Row(
      children: [
        Icon(icon, color: theme.colorScheme.primary, size: 20),
        const SizedBox(width: 8),
        Text(title, style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 16)),
      ],
    );
  }

  Widget _buildEmptyState(String message) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 20),
      child: Center(
        child: Text(message, style: const TextStyle(color: Colors.grey, fontSize: 13)),
      ),
    );
  }

  Widget _buildModuleTile(BuildContext context, dynamic module) {
    final rawName = (module['name'] ?? 'Unknown').toString();
    final displayTitle = _getModuleDisplayTitle(context, rawName);
    final isEnabled = module['enabled'] == true;

    return Container(
      margin: const EdgeInsets.only(bottom: 10),
      decoration: BoxDecoration(
        color: Theme.of(context).colorScheme.surfaceContainerHighest.withOpacity(0.3),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: Theme.of(context).colorScheme.outlineVariant.withOpacity(0.5)),
      ),
      child: SwitchListTile.adaptive(
        title: Text(displayTitle, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
        subtitle: Text('Rregullo njoftimet Push për $displayTitle', style: const TextStyle(fontSize: 11)),
        value: isEnabled,
        onChanged: (val) => context.read<NotificationCubit>().toggleModule(rawName),
        contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
      ),
    );
  }

  Widget _buildEventTile(BuildContext context, dynamic event) {
    final eventName = event['event'] ?? 'Unknown';
    final label = event['label'] ?? '';
    final isEnabled = event['firebase_enabled'] == true;

    return Container(
      margin: const EdgeInsets.only(bottom: 8),
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
      decoration: BoxDecoration(
        color: Theme.of(context).colorScheme.surface,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: Theme.of(context).colorScheme.outlineVariant.withOpacity(0.3)),
      ),
      child: Row(
        children: [
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(label, style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 14)),
                const SizedBox(height: 2),
                Text(eventName, style: TextStyle(fontFamily: 'monospace', fontSize: 10, color: Colors.grey.shade600)),
              ],
            ),
          ),
          Transform.scale(
            scale: 0.8,
            child: Switch.adaptive(
              value: isEnabled,
              onChanged: (val) => context.read<NotificationCubit>().toggleEvent(event['id']),
            ),
          ),
        ],
      ),
    );
  }
}
