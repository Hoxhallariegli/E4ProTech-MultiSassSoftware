import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import '../../../../core/widgets/premium_widgets.dart';
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

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);

    return Scaffold(
      backgroundColor: theme.colorScheme.surface,
      appBar: AppBar(
        title: const Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('Notification Settings', style: TextStyle(fontSize: 18, fontWeight: FontWeight.w900)),
            Text('Configure Firebase Push Notifications', style: TextStyle(fontSize: 11, color: Colors.grey)),
          ],
        ),
      ),
      body: BlocConsumer<NotificationCubit, NotificationState>(
        listener: (context, state) {
          if (state is NotificationError) {
            ScaffoldMessenger.of(context).showSnackBar(
              SnackBar(content: Text('Error: ${state.message}'), backgroundColor: Colors.red),
            );
          }
        },
        builder: (context, state) {
          if (state is NotificationLoading) {
            return const Center(child: CircularProgressIndicator.adaptive());
          }

          if (state is NotificationLoaded) {
            return ListView(
              padding: const EdgeInsets.all(20),
              children: [
                _buildSectionHeader(theme, 'Modules (Global Switch)', Icons.apps_rounded),
                const SizedBox(height: 12),
                if (state.modules.isEmpty)
                  _buildEmptyState('No modules found')
                else
                  ...state.modules.map((m) => _buildModuleTile(context, m)),

                const SizedBox(height: 30),
                _buildSectionHeader(theme, 'Granular Action Events', Icons.bolt_rounded),
                const SizedBox(height: 12),
                if (state.events.isEmpty)
                  _buildEmptyState('No events registered yet')
                else
                  ...state.events.map((e) => _buildEventTile(context, e)),

                const SizedBox(height: 40),
                Text(
                  'Note: Reverb realtime UI updates remain active. These switches only control Firebase Push Notifications.',
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
    final name = module['name'] ?? 'Unknown';
    final isEnabled = module['enabled'] == true;

    return Container(
      margin: const EdgeInsets.only(bottom: 10),
      decoration: BoxDecoration(
        color: Theme.of(context).colorScheme.surfaceContainerHighest.withOpacity(0.3),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: Theme.of(context).colorScheme.outlineVariant.withOpacity(0.5)),
      ),
      child: SwitchListTile.adaptive(
        title: Text(name.toUpperCase(), style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
        subtitle: const Text('Toggle all notifications for this module', style: TextStyle(fontSize: 11)),
        value: isEnabled,
        onChanged: (val) => context.read<NotificationCubit>().toggleModule(name),
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
