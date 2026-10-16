import 'package:flutter/material.dart';
import '../../services/auth_service.dart';
import '../../services/api_service.dart';
import '../../core/realtime/realtime_service.dart';
import '../../modules/dashboard/presentation/pages/module_registry.dart';
import '../../modules/dashboard/presentation/widgets/shop_switcher_widget.dart';
import '../../modules/dashboard/presentation/pages/dashboard_page.dart';
import 'package:mobile_gateway/l10n/core_localization.dart';

class Sidebar extends StatelessWidget {
  const Sidebar({super.key});

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final user = AuthService.instance.user;
    final modules = ModuleRegistry.modules.where((m) => m.permission == null || AuthService.instance.hasPermission(m.permission!)).toList();

    final salonKeys = {'booking', 'barber', 'service', 'customer', 'working_hour', 'payment', 'review'};
    final smsKeys = {'message_queue', 'message_log', 'message_template', 'event_setting', 'device_token', 'notification_channel'};
    final saasKeys = {'barber_shop', 'subscription_renewal', 'plan', 'user'};

    final salonModules = modules.where((m) => salonKeys.contains(m.key)).toList();
    final smsModules = modules.where((m) => smsKeys.contains(m.key)).toList();
    final saasModules = modules.where((m) => saasKeys.contains(m.key)).toList();
    final otherModules = modules.where((m) => !salonKeys.contains(m.key) && !smsKeys.contains(m.key) && !saasKeys.contains(m.key)).toList();

    return Drawer(
      backgroundColor: theme.colorScheme.surface,
      child: Column(
        children: [
          UserHeaderCard(
            userData: user,
            baseUrl: ApiService.serverUrlSync,
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
                  label: coreTr(context, 'sidebar.overview'),
                  onTap: () {
                    Navigator.of(context).pushNamedAndRemoveUntil('/dashboard', (route) => false);
                  },
                ),

                // 1. MENAXHIMI I SALLONIT
                if (salonModules.isNotEmpty) ...[
                  _SectionHeader(title: coreTr(context, 'sidebar.salon_management')),
                  ...salonModules.map((m) => _DrawerItem(
                    icon: m.icon,
                    label: m.getTitle(context),
                    onTap: () {
                      Navigator.pop(context);
                      Navigator.push(context, MaterialPageRoute(builder: (_) => m.page));
                    },
                  )),
                ],

                // 2. SMS & NJOFTIMET
                if (smsModules.isNotEmpty) ...[
                  _SectionHeader(title: coreTr(context, 'sidebar.sms_notifications')),
                  ...smsModules.map((m) => _DrawerItem(
                    icon: m.icon,
                    label: m.getTitle(context),
                    onTap: () {
                      Navigator.pop(context);
                      Navigator.push(context, MaterialPageRoute(builder: (_) => m.page));
                    },
                  )),
                ],

                // 3. ADMINISTRIMI SAAS
                if (saasModules.isNotEmpty) ...[
                  _SectionHeader(title: coreTr(context, 'sidebar.saas_administration')),
                  ...saasModules.map((m) => _DrawerItem(
                    icon: m.icon,
                    label: m.getTitle(context),
                    onTap: () {
                      Navigator.pop(context);
                      Navigator.push(context, MaterialPageRoute(builder: (_) => m.page));
                    },
                  )),
                ],

                // 4. TJERA / CILËSIMET
                if (otherModules.isNotEmpty) ...[
                  _SectionHeader(title: coreTr(context, 'sidebar.other')),
                  ...otherModules.map((m) => _DrawerItem(
                    icon: m.icon,
                    label: m.getTitle(context),
                    onTap: () {
                      Navigator.pop(context);
                      Navigator.push(context, MaterialPageRoute(builder: (_) => m.page));
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

class _SectionHeader extends StatelessWidget {
  final String title;
  const _SectionHeader({required this.title});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.fromLTRB(16, 18, 16, 6),
      child: Text(
        title,
        style: const TextStyle(
          fontSize: 10,
          fontWeight: FontWeight.w900,
          color: Colors.grey,
          letterSpacing: 1.2,
        ),
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
    final primaryColor = theme.colorScheme.primary;

    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 2),
      child: ListTile(
        onTap: onTap,
        leading: Container(
          padding: const EdgeInsets.all(8),
          decoration: BoxDecoration(
            color: primaryColor.withOpacity(0.12),
            borderRadius: BorderRadius.circular(10),
          ),
          child: Icon(icon, color: primaryColor, size: 20),
        ),
        title: Text(
          label,
          style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 13.5),
        ),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
      ),
    );
  }
}
