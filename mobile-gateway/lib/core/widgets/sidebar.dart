import 'package:flutter/material.dart';
import '../../services/auth_service.dart';
import '../../services/api_service.dart';
import '../../core/realtime/realtime_service.dart';
import '../../modules/dashboard/presentation/pages/module_registry.dart';
import '../../modules/dashboard/presentation/widgets/shop_switcher_widget.dart';
import '../../modules/dashboard/presentation/pages/dashboard_page.dart';

class Sidebar extends StatelessWidget {
  const Sidebar({super.key});

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final user = AuthService.instance.user;
    final modules = ModuleRegistry.modules.where((m) => m.permission == null || AuthService.instance.hasPermission(m.permission!)).toList();

    return Drawer(
      backgroundColor: theme.colorScheme.surface,
      child: Column(
        children: [
          UserHeaderCard(
            userData: user,
            baseUrl: '', // Base URL is handled inside the widget now
            onSwitchShop: () {
              showModalBottomSheet(
                context: context,
                isScrollControlled: true,
                backgroundColor: Colors.transparent,
                builder: (_) => ShopSwitcherWidget(
                  onSwitched: () async {
                    await AuthService.instance.sync();
                    RealtimeService.instance.stop().then((_) => RealtimeService.instance.start());
                  },
                ),
              );
            },
          ),
          const SizedBox(height: 12),
          Expanded(
            child: ListView(
              padding: const EdgeInsets.symmetric(horizontal: 12),
              children: [
                _DrawerItem(
                  icon: Icons.dashboard_customize_rounded,
                  label: 'Overview Dashboard',
                  onTap: () => Navigator.pushReplacementNamed(context, '/dashboard'),
                ),

                if (modules.isNotEmpty) ...[
                  const Padding(
                    padding: EdgeInsets.fromLTRB(16, 16, 16, 8),
                    child: Text('DYNAMIC MODULES', style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: Colors.grey, letterSpacing: 1.2)),
                  ),
                  ...modules.map((module) => _DrawerItem(
                    icon: module.icon,
                    label: module.name,
                    onTap: () {
                      Navigator.pop(context);
                      Navigator.push(context, MaterialPageRoute(builder: (_) => module.page));
                    },
                  )),
                ],
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
  final VoidCallback onTap;

  const _DrawerItem({required this.icon, required this.label, required this.onTap});

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return ListTile(
      onTap: onTap,
      leading: Icon(icon, color: Colors.grey),
      title: Text(label, style: const TextStyle(fontWeight: FontWeight.w500)),
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
    );
  }
}
