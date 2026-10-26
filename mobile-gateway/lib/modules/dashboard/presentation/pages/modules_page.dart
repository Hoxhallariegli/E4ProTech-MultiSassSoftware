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

    final salonKeys = {'booking', 'barber', 'service', 'customer', 'payment', 'review', 'working_hour', 'bulk_cancellation'};
    final smsKeys = {'message_template', 'message_queue', 'message_log', 'event_setting', 'device_token', 'notification_channel'};
    final saasKeys = {'barber_shop', 'subscription_renewal', 'shop_front_page_setting', 'user'};

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
          padding: const EdgeInsets.fromLTRB(12, 8, 12, 120),
          children: [
            // 1. Compact Search Bar
            PremiumSearchBar(
              controller: _searchController,
              hintText: coreTr(context, 'modules.search_hint'),
              onChanged: (val) => setState(() => _searchQuery = val.trim()),
            ),

            const SizedBox(height: 12),

            // 2. Salon Operations Category
            if (salonModules.isNotEmpty) ...[
              _CategoryHeader(
                title: coreTr(context, 'sidebar.salon_management'),
                icon: Icons.storefront_rounded,
                color: primaryColor,
              ),
              const SizedBox(height: 6),
              _ModuleGrid(modules: salonModules, primaryColor: primaryColor, isDark: isDark),
              const SizedBox(height: 12),
            ],

            // 3. SMS Gateway Category
            if (smsModules.isNotEmpty) ...[
              _CategoryHeader(
                title: coreTr(context, 'sidebar.sms_notifications'),
                icon: Icons.sms_rounded,
                color: primaryColor,
              ),
              const SizedBox(height: 6),
              _ModuleGrid(modules: smsModules, primaryColor: primaryColor, isDark: isDark),
              const SizedBox(height: 12),
            ],

            // 4. SaaS Administration Category
            if (saasModules.isNotEmpty) ...[
              _CategoryHeader(
                title: coreTr(context, 'sidebar.saas_administration'),
                icon: Icons.auto_awesome_mosaic_rounded,
                color: primaryColor,
              ),
              const SizedBox(height: 6),
              _ModuleGrid(modules: saasModules, primaryColor: primaryColor, isDark: isDark),
              const SizedBox(height: 12),
            ],

            // 5. Other Modules
            if (otherModules.isNotEmpty) ...[
              _CategoryHeader(
                title: coreTr(context, 'sidebar.other'),
                icon: Icons.grid_view_rounded,
                color: primaryColor,
              ),
              const SizedBox(height: 6),
              _ModuleGrid(modules: otherModules, primaryColor: primaryColor, isDark: isDark),
            ],
          ],
        ),
      ),
    );
  }
}

class _CategoryHeader extends StatelessWidget {
  final String title;
  final IconData icon;
  final Color color;

  const _CategoryHeader({
    required this.title,
    required this.icon,
    required this.color,
  });

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(left: 4, bottom: 2),
      child: Row(
        children: [
          Container(
            padding: const EdgeInsets.all(4),
            decoration: BoxDecoration(
              color: color.withOpacity(0.12),
              borderRadius: BorderRadius.circular(6),
            ),
            child: Icon(icon, color: color, size: 13),
          ),
          const SizedBox(width: 6),
          Text(
            title,
            style: TextStyle(
              fontSize: 10,
              fontWeight: FontWeight.w900,
              color: color,
              letterSpacing: 0.8,
            ),
          ),
        ],
      ),
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
    double aspectRatio = screenWidth >= 600 ? 3.0 : 2.4;

    return GridView.builder(
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
        crossAxisCount: crossAxisCount,
        crossAxisSpacing: 8,
        mainAxisSpacing: 8,
        childAspectRatio: aspectRatio,
      ),
      itemCount: modules.length,
      itemBuilder: (context, index) {
        final module = modules[index];
        return Material(
          color: Colors.transparent,
          child: InkWell(
            onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => module.page)),
            borderRadius: BorderRadius.circular(16),
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
              decoration: BoxDecoration(
                color: isDark ? const Color(0xFF1E212B) : Colors.white,
                borderRadius: BorderRadius.circular(16),
                border: Border.all(
                  color: isDark ? const Color(0xFF3B4052) : Colors.grey.shade200,
                  width: 1,
                ),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withOpacity(0.02),
                    blurRadius: 6,
                    offset: const Offset(0, 2),
                  ),
                ],
              ),
              child: Row(
                children: [
                  Container(
                    width: 32,
                    height: 32,
                    decoration: BoxDecoration(
                      color: primaryColor.withOpacity(0.12),
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: Icon(module.icon, size: 16, color: primaryColor),
                  ),
                  const SizedBox(width: 8),
                  Expanded(
                    child: Text(
                      module.getTitle(context),
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                      style: TextStyle(
                        fontWeight: FontWeight.w800,
                        fontSize: 11.5,
                        color: isDark ? Colors.white : Colors.black87,
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
