import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../../../../core/widgets/premium_widgets.dart';
import '../../../../core/theme/app_theme.dart';
import '../../../../services/api_service.dart';
import '../../../../core/realtime/realtime_service.dart';
import 'dart:convert';
import 'package:pusher_reverb_flutter/pusher_reverb_flutter.dart';

import 'module_registry.dart';

class DashboardPage extends StatefulWidget {
  const DashboardPage({super.key});

  @override
  State<DashboardPage> createState() => _DashboardPageState();
}

class _DashboardPageState extends State<DashboardPage> {
  int _selectedDrawerIndex = 0;
  String _reverbStatus = 'OFFLINE';
  Map<String, dynamic>? _userData;
  String _baseUrl = '';

  @override
  void initState() {
    super.initState();
    _checkReverb();
    _loadUserData();
  }

  Future<void> _loadUserData() async {
    final prefs = await SharedPreferences.getInstance();
    final data = prefs.getString('user_data');
    final url = await ApiService.serverUrl;
    if (mounted) {
      setState(() {
        _userData = data != null ? jsonDecode(data) : null;
        _baseUrl = url;
      });
    }
  }

  Future<void> _checkReverb() async {
    await RealtimeService.instance.start();
    final client = RealtimeService.instance.client;

    if (client == null) {
      if (mounted) setState(() => _reverbStatus = 'OFFLINE');
      return;
    }

    if (mounted) setState(() => _reverbStatus = client.connectionState.name.toUpperCase());

    client.onConnectionStateChange.listen((state) {
      if (mounted) setState(() => _reverbStatus = state.name.toUpperCase());
    });
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
            Text(
              'LaraFlutter Control Panel',
              style: TextStyle(fontSize: 18, fontWeight: FontWeight.w900),
            ),
            Text(
              'Pro Automation Starter Kit Active',
              style: TextStyle(fontSize: 11, color: Colors.grey, fontWeight: FontWeight.w500),
            ),
          ],
        ),
        actions: [
          IconButton(
            icon: Icon(
              _reverbStatus == 'CONNECTED'
                ? Icons.notifications_active_outlined
                : Icons.notifications_off_outlined,
              color: _reverbStatus == 'CONNECTED' ? Colors.amber : Colors.grey,
            ),
            onPressed: () {
              String msg = 'Realtime service is offline.';
              if (_reverbStatus == 'CONNECTED') msg = 'Realtime streaming network active.';
              if (_reverbStatus == 'CONNECTING') msg = 'Connecting to realtime network...';
              if (_reverbStatus == 'DISCONNECTED') msg = 'Realtime service is offline.';

              ScaffoldMessenger.of(context).hideCurrentSnackBar();
              ScaffoldMessenger.of(context).showSnackBar(
                SnackBar(
                  content: Text(msg),
                  behavior: SnackBarBehavior.floating,
                  backgroundColor: _reverbStatus == 'CONNECTED' ? Colors.green.shade800 : Colors.red.shade800,
                ),
              );
            },
          ),
          const SizedBox(width: 8),
        ],
      ),
      drawer: Drawer(
        backgroundColor: theme.colorScheme.surface,
        child: Column(
          children: [
            UserHeaderCard(userData: _userData, baseUrl: _baseUrl),
            const SizedBox(height: 12),
            Expanded(
              child: ListView(
                padding: const EdgeInsets.symmetric(horizontal: 12),
                children: [
                  _DrawerItem(
                    icon: Icons.dashboard_customize_rounded,
                    label: 'Overview Dashboard',
                    selected: _selectedDrawerIndex == 0,
                    onTap: () => setState(() => _selectedDrawerIndex = 0),
                  ),

                  if (ModuleRegistry.modules.isNotEmpty) ...[
                    const Padding(
                      padding: EdgeInsets.fromLTRB(16, 16, 16, 8),
                      child: Text('DYNAMIC MODULES', style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: Colors.grey, letterSpacing: 1.2)),
                    ),
                    ...ModuleRegistry.modules.map((module) => _DrawerItem(
                      icon: module.icon,
                      label: module.name,
                      selected: false,
                      onTap: () {
                        Navigator.push(context, MaterialPageRoute(builder: (_) => module.page));
                      },
                    )),
                  ],

                  const Padding(
                    padding: EdgeInsets.fromLTRB(16, 16, 16, 8),
                    child: Text('SYSTEM', style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: Colors.grey, letterSpacing: 1.2)),
                  ),
                  _DrawerItem(
                    icon: Icons.settings_suggest_outlined,
                    label: 'System Settings',
                    selected: _selectedDrawerIndex == 3,
                    onTap: () => setState(() => _selectedDrawerIndex = 3),
                  ),
                ],
              ),
            ),
            Padding(
              padding: const EdgeInsets.all(20),
              child: Text(
                'v1.0.0 (God Version ready)',
                style: TextStyle(fontSize: 11, color: theme.colorScheme.onSurfaceVariant.withOpacity(0.5)),
              ),
            )
          ],
        ),
      ),
      body: RefreshIndicator(
        onRefresh: () async => await RealtimeService.instance.start(),
        child: ListView(
          padding: const EdgeInsets.all(20),
          children: [
            const WelcomeBanner(),
            const SizedBox(height: 24),
            const Text(
              'Automation Instructions',
              style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 12),
            PremiumCard(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Icon(Icons.auto_awesome, color: theme.colorScheme.primary),
                      const SizedBox(width: 8),
                      const Text(
                        'Ready for Reusable Scaffolding',
                        style: TextStyle(fontWeight: FontWeight.w800, fontSize: 14),
                      ),
                    ],
                  ),
                  const SizedBox(height: 8),
                  const Text(
                    'Use "php artisan new:view {ModuleName} --api" on the Laravel backend. The system will automatically build full-stack CRUD capabilities and append clean architectural pages directly into this project workspace instantly.',
                    style: TextStyle(fontSize: 12.5, color: Colors.grey, height: 1.4),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class UserHeaderCard extends StatelessWidget {
  final Map<String, dynamic>? userData;
  final String baseUrl;
  const UserHeaderCard({super.key, this.userData, required this.baseUrl});

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final name = userData?['name'] ?? 'Administrator';
    final email = userData?['email'] ?? 'admin@laraflutter.auto';
    final image = userData?['image'];

    return Container(
      width: double.infinity,
      padding: const EdgeInsets.fromLTRB(20, 60, 20, 24),
      decoration: BoxDecoration(
        gradient: LinearGradient(
          colors: [theme.colorScheme.primary, theme.colorScheme.secondary],
        ),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          CircleAvatar(
            radius: 32,
            backgroundColor: Colors.white24,
            backgroundImage: (image != null && image.toString().isNotEmpty)
                ? NetworkImage(image.toString().startsWith('http') ? image.toString() : '$baseUrl/$image')
                : null,
            child: (image == null || image.toString().isEmpty)
                ? const Icon(Icons.admin_panel_settings, color: Colors.white, size: 30)
                : null,
          ),
          const SizedBox(height: 14),
          Text(
            name,
            style: const TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.w900),
          ),
          Text(
            email,
            style: const TextStyle(color: Colors.white70, fontSize: 12),
          ),
        ],
      ),
    );
  }
}

class _DrawerItem extends StatelessWidget {
  final IconData icon;
  final String label;
  final bool selected;
  final VoidCallback onTap;

  const _DrawerItem({
    required this.icon,
    required this.label,
    required this.selected,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Container(
      margin: const EdgeInsets.only(bottom: 4),
      child: ListTile(
        onTap: () {
          Navigator.pop(context);
          onTap();
        },
        leading: Icon(icon, color: selected ? theme.colorScheme.primary : Colors.grey),
        title: Text(
          label,
          style: TextStyle(
            fontWeight: selected ? FontWeight.bold : FontWeight.w500,
            color: selected ? theme.colorScheme.primary : theme.colorScheme.onSurface,
          ),
        ),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        selected: selected,
        selectedTileColor: theme.colorScheme.primaryContainer.withOpacity(0.3),
      ),
    );
  }
}

class WelcomeBanner extends StatelessWidget {
  const WelcomeBanner({super.key});

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        gradient: LinearGradient(
          colors: [theme.colorScheme.primaryContainer, theme.colorScheme.secondaryContainer],
        ),
        borderRadius: BorderRadius.circular(24),
      ),
      child: Row(
        children: [
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text(
                  'Environment Ready! 🚀',
                  style: TextStyle(fontSize: 18, fontWeight: FontWeight.w900),
                ),
                const SizedBox(height: 4),
                Text(
                  'Your premium full-stack ecosystem is completely set up. Let\'s make spectacular applications.',
                  style: TextStyle(fontSize: 12, color: theme.colorScheme.onSurfaceVariant),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class MetricCard extends StatelessWidget {
  final String title;
  final String value;
  final IconData icon;
  final Color color;

  const MetricCard({
    super.key,
    required this.title,
    required this.value,
    required this.icon,
    required this.color,
  });

  @override
  Widget build(BuildContext context) {
    return PremiumCard(
      padding: const EdgeInsets.all(14),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Row(
            children: [
              Container(
                padding: const EdgeInsets.all(6),
                decoration: BoxDecoration(
                  color: color.withOpacity(0.12),
                  shape: BoxShape.circle,
                ),
                child: Icon(icon, color: color, size: 20),
              ),
              const Spacer(),
              const Icon(Icons.arrow_forward_ios_rounded, size: 10, color: Colors.grey),
            ],
          ),
          Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                value,
                style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w900),
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
              ),
              const SizedBox(height: 2),
              Text(
                title,
                style: const TextStyle(fontSize: 10, color: Colors.grey, fontWeight: FontWeight.w600),
              ),
            ],
          )
        ],
      ),
    );
  }
}
