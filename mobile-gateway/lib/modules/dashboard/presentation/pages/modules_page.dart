import 'package:flutter/material.dart';
import 'package:mobile_gateway/core/widgets/premium_widgets.dart';
import 'package:mobile_gateway/services/auth_service.dart';
import 'package:mobile_gateway/modules/dashboard/presentation/pages/module_registry.dart';
import 'package:mobile_gateway/l10n/core_localization.dart';

class ModulesPage extends StatefulWidget {
  const ModulesPage({super.key});

  @override
  State<ModulesPage> createState() => _ModulesPageState();
}

class _ModulesPageState extends State<ModulesPage> {
  final TextEditingController _searchController = TextEditingController();
  String _searchQuery = '';

  @override
  void initState() {
    super.initState();
    AuthService.instance.addListener(_handleAuthChange);
  }

  @override
  void dispose() {
    _searchController.dispose();
    AuthService.instance.removeListener(_handleAuthChange);
    super.dispose();
  }

  void _handleAuthChange() {
    if (mounted) setState(() {});
  }

  Future<void> _handleRefresh() async {
    await AuthService.instance.sync();
    if (mounted) setState(() {});
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final isDark = theme.brightness == Brightness.dark;
    final primaryColor = theme.colorScheme.primary;

    final allModules = ModuleRegistry.modules;
    final userModules = allModules.where((m) => m.permission == null || AuthService.instance.hasPermission(m.permission!)).toList();

    // Filter by search query
    final filteredModules = _searchQuery.isEmpty
        ? userModules
        : userModules.where((m) => m.getTitle(context).toLowerCase().contains(_searchQuery.toLowerCase())).toList();

    final salonKeys = {'booking', 'barber', 'service', 'customer', 'working_hour', 'payment', 'review'};
    final smsKeys = {'message_queue', 'message_log', 'message_template', 'event_setting', 'device_token', 'notification_channel'};
    final saasKeys = {'shop_front_page_setting', 'barber_shop', 'subscription_renewal', 'plan', 'user'};

    final salonModules = filteredModules.where((m) => salonKeys.contains(m.key)).toList();
    final smsModules = filteredModules.where((m) => smsKeys.contains(m.key)).toList();
    final saasModules = filteredModules.where((m) => saasKeys.contains(m.key)).toList();
    final otherModules = filteredModules.where((m) => !salonKeys.contains(m.key) && !smsKeys.contains(m.key) && !saasKeys.contains(m.key)).toList();

    if (userModules.isEmpty) {
      return Scaffold(
        backgroundColor: theme.colorScheme.surface,
        body: Center(
          child: Padding(
            padding: const EdgeInsets.all(24),
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Icon(Icons.lock_clock_rounded, size: 64, color: primaryColor.withOpacity(0.4)),
                const SizedBox(height: 16),
                Text(
                  coreTr(context, 'modules.no_access'),
                  style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w900),
                ),
                const SizedBox(height: 6),
                Text(
                  coreTr(context, 'modules.no_access_desc'),
                  textAlign: TextAlign.center,
                  style: const TextStyle(color: Colors.grey, fontSize: 13),
                ),
                const SizedBox(height: 16),
                FilledButton.icon(
                  onPressed: _handleRefresh,
                  icon: const Icon(Icons.refresh_rounded),
                  label: Text(coreTr(context, 'modules.refresh_permissions')),
                ),
              ],
            ),
          ),
        ),
      );
    }

    return Scaffold(
      backgroundColor: theme.colorScheme.surface,
      body: RefreshIndicator(
        onRefresh: _handleRefresh,
        child: ListView(
          padding: const EdgeInsets.fromLTRB(16, 12, 16, 120),
          children: [
            // 1. Modern Search Bar
            PremiumSearchBar(
              controller: _searchController,
              hintText: 'Kërko modulin e dëshiruar...',
              onChanged: (val) => setState(() => _searchQuery = val.trim()),
            ),

            const SizedBox(height: 20),

            // 2. Salon Operations Category
            if (salonModules.isNotEmpty) ...[
              _CategoryHeader(
                title: 'MENAXHIMI I SALLONIT',
                subtitle: 'Operacionet e përditshme, takimet & stafi',
                icon: Icons.storefront_rounded,
                color: primaryColor,
              ),
              const SizedBox(height: 10),
              _ModuleGrid(modules: salonModules, primaryColor: primaryColor, isDark: isDark),
              const SizedBox(height: 22),
            ],

            // 3. SMS Gateway Category
            if (smsModules.isNotEmpty) ...[
              _CategoryHeader(
                title: 'SMS GATEWAY & NJOFTIMET',
                subtitle: 'Radha e mesazheve, logjet & shabllonet',
                icon: Icons.sms_rounded,
                color: const Color(0xFF10B981),
              ),
              const SizedBox(height: 10),
              _ModuleGrid(modules: smsModules, primaryColor: const Color(0xFF10B981), isDark: isDark),
              const SizedBox(height: 22),
            ],

            // 4. SaaS Administration Category
            if (saasModules.isNotEmpty) ...[
              _CategoryHeader(
                title: 'ADMINISTRIMI SAAS',
                subtitle: 'Abonimet, planet, faqja publike & përdoruesit',
                icon: Icons.auto_awesome_mosaic_rounded,
                color: const Color(0xFF8B5CF6),
              ),
              const SizedBox(height: 10),
              _ModuleGrid(modules: saasModules, primaryColor: const Color(0xFF8B5CF6), isDark: isDark),
              const SizedBox(height: 22),
            ],

            // 5. Other Modules
            if (otherModules.isNotEmpty) ...[
              _CategoryHeader(
                title: 'TË TJERA',
                subtitle: 'Modulet shtesë të sistemit',
                icon: Icons.grid_view_rounded,
                color: Colors.amber.shade800,
              ),
              const SizedBox(height: 10),
              _ModuleGrid(modules: otherModules, primaryColor: Colors.amber.shade800, isDark: isDark),
            ],
          ],
        ),
      ),
    );
  }
}

class _CategoryHeader extends StatelessWidget {
  final String title;
  final String subtitle;
  final IconData icon;
  final Color color;

  const _CategoryHeader({
    required this.title,
    required this.subtitle,
    required this.icon,
    required this.color,
  });

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        Container(
          padding: const EdgeInsets.all(7),
          decoration: BoxDecoration(
            color: color.withOpacity(0.14),
            borderRadius: BorderRadius.circular(10),
          ),
          child: Icon(icon, color: color, size: 18),
        ),
        const SizedBox(width: 10),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                title,
                style: TextStyle(
                  fontSize: 11,
                  fontWeight: FontWeight.w900,
                  color: color,
                  letterSpacing: 0.8,
                ),
              ),
              Text(
                subtitle,
                style: const TextStyle(fontSize: 10, color: Colors.grey, fontWeight: FontWeight.w500),
              ),
            ],
          ),
        ),
      ],
    );
  }
}

class _ModuleGrid extends StatelessWidget {
  final List<ModuleEntry> modules;
  final Color primaryColor;
  final bool isDark;

  const _ModuleGrid({
    required this.modules,
    required this.primaryColor,
    required this.isDark,
  });

  @override
  Widget build(BuildContext context) {
    final screenWidth = MediaQuery.of(context).size.width;
    int crossAxisCount = screenWidth >= 900 ? 4 : (screenWidth >= 600 ? 3 : 2);

    return GridView.builder(
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
        crossAxisCount: crossAxisCount,
        crossAxisSpacing: 10,
        mainAxisSpacing: 10,
        childAspectRatio: 1.35,
      ),
      itemCount: modules.length,
      itemBuilder: (context, index) {
        final module = modules[index];
        return Material(
          color: Colors.transparent,
          child: InkWell(
            onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => module.page)),
            borderRadius: BorderRadius.circular(22),
            child: Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: isDark ? const Color(0xFF1E212B) : Colors.white,
                borderRadius: BorderRadius.circular(22),
                border: Border.all(
                  color: isDark ? const Color(0xFF3B4052) : Colors.grey.shade200,
                  width: 1,
                ),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withOpacity(0.03),
                    blurRadius: 10,
                    offset: const Offset(0, 4),
                  ),
                ],
              ),
              child: Row(
                children: [
                  Container(
                    width: 44,
                    height: 44,
                    decoration: BoxDecoration(
                      gradient: LinearGradient(
                        begin: Alignment.topLeft,
                        end: Alignment.bottomRight,
                        colors: [
                          primaryColor.withOpacity(0.18),
                          primaryColor.withOpacity(0.06),
                        ],
                      ),
                      borderRadius: BorderRadius.circular(15),
                    ),
                    child: Icon(module.icon, size: 22, color: primaryColor),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: Text(
                      module.getTitle(context),
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                      style: const TextStyle(
                        fontWeight: FontWeight.w800,
                        fontSize: 12.5,
                        height: 1.2,
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ),
        );
      },
    );
  }
}
