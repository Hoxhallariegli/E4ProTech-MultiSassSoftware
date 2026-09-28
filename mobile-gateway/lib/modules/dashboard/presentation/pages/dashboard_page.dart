import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:mobile_gateway/modules/dashboard/booking/presentation/cubit/booking_cubit.dart';
import 'package:mobile_gateway/modules/dashboard/booking/data/booking_repository.dart';
import 'package:mobile_gateway/modules/dashboard/booking/presentation/pages/booking_calendar_page.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../../../../core/widgets/premium_widgets.dart';
import '../../../../core/theme/app_theme.dart';
import '../../../../services/api_service.dart';
import '../../../../services/auth_service.dart';
import '../../../../core/realtime/realtime_service.dart';
import 'dart:convert';
import 'package:pusher_reverb_flutter/pusher_reverb_flutter.dart';

import '../widgets/shop_switcher_widget.dart';
import 'module_registry.dart';

class DashboardPage extends StatefulWidget {
  final Key? calendarKey;
  const DashboardPage({super.key, this.calendarKey});

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
    _syncData();
    AuthService.instance.addListener(_handleAuthChange);
  }

  @override
  void dispose() {
    AuthService.instance.removeListener(_handleAuthChange);
    super.dispose();
  }

  void _handleAuthChange() {
    if (mounted) _loadUserData();
  }

  Future<void> _syncData() async {
    await AuthService.instance.sync();
    if (mounted) _loadUserData();
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
    return BlocProvider(
      create: (_) => BookingCubit(BookingRepository()),
      child: BookingCalendarPage(key: widget.calendarKey),
    );
  }
}

class UserHeaderCard extends StatelessWidget {
  final Map<String, dynamic>? userData;
  final String baseUrl;
  final VoidCallback? onSwitchShop;

  const UserHeaderCard({super.key, this.userData, required this.baseUrl, this.onSwitchShop});

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final name = userData?['name'] ?? 'Administrator';
    final email = userData?['email'] ?? 'admin@laraflutter.auto';
    final image = userData?['image'];
    final shopName = userData?['business']?['name'] ?? 'No Shop Selected';

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
          Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
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
              if (onSwitchShop != null)
                IconButton(
                  onPressed: onSwitchShop,
                  icon: const Icon(Icons.swap_horiz_rounded, color: Colors.white),
                  tooltip: 'Switch Shop',
                ),
            ],
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
          const SizedBox(height: 8),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
            decoration: BoxDecoration(
              color: Colors.white.withOpacity(0.2),
              borderRadius: BorderRadius.circular(12),
            ),
            child: Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                const Icon(Icons.storefront_rounded, color: Colors.white, size: 14),
                const SizedBox(width: 6),
                Text(
                  shopName,
                  style: const TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.bold),
                ),
              ],
            ),
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
