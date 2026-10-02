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
  bool _loading = false;

  @override
  void initState() {
    super.initState();
    _handleRefresh();
    AuthService.instance.addListener(_handleAuthChange);
  }

  @override
  void dispose() {
    AuthService.instance.removeListener(_handleAuthChange);
    super.dispose();
  }

  void _handleAuthChange() {
    if (mounted) setState(() {});
  }

  Future<void> _handleRefresh() async {
    if (!mounted) return;
    setState(() => _loading = true);
    await AuthService.instance.sync();
    if (mounted) setState(() => _loading = false);
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final isDark = theme.brightness == Brightness.dark;
    final screenWidth = MediaQuery.of(context).size.width;

    // Responsive columns and aspect ratios for Mobile, Tablet, and Desktop
    int crossAxisCount = 2;
    double aspectRatio = 1.1;

    if (screenWidth >= 1100) {
      crossAxisCount = 5;
      aspectRatio = 1.25;
    } else if (screenWidth >= 800) {
      crossAxisCount = 4;
      aspectRatio = 1.2;
    } else if (screenWidth >= 600) {
      crossAxisCount = 3;
      aspectRatio = 1.15;
    }

    final allModules = ModuleRegistry.modules;
    final modules = allModules.where((m) => m.permission == null || AuthService.instance.hasPermission(m.permission!)).toList();

    Widget content;

    if (modules.isEmpty) {
      content = SingleChildScrollView(
        physics: const AlwaysScrollableScrollPhysics(),
        child: SizedBox(
          height: MediaQuery.of(context).size.height * 0.7,
          child: Center(
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Icon(Icons.layers_outlined, size: 70, color: theme.colorScheme.primary.withOpacity(0.3)),
                const SizedBox(height: 16),
                Text(
                  coreTr(context, 'modules.no_access'),
                  style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                ),
                const SizedBox(height: 8),
                Text(
                  coreTr(context, 'modules.no_access_desc'),
                  style: const TextStyle(color: Colors.grey),
                ),
                const SizedBox(height: 16),
                TextButton.icon(
                  onPressed: _handleRefresh,
                  icon: const Icon(Icons.refresh),
                  label: Text(coreTr(context, 'modules.refresh_permissions')),
                ),
              ],
            ),
          ),
        ),
      );
    } else {
      content = GridView.builder(
        padding: const EdgeInsets.all(16),
        gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
          crossAxisCount: crossAxisCount,
          crossAxisSpacing: 14,
          mainAxisSpacing: 14,
          childAspectRatio: aspectRatio,
        ),
        itemCount: modules.length,
        itemBuilder: (context, index) {
          final module = modules[index];
          return PremiumCard(
            onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => module.page)),
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 14),
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Container(
                  width: 50,
                  height: 50,
                  decoration: BoxDecoration(
                    color: theme.colorScheme.primary.withOpacity(0.12),
                    borderRadius: BorderRadius.circular(16),
                  ),
                  child: Icon(module.icon, size: 26, color: theme.colorScheme.primary),
                ),
                const SizedBox(height: 10),
                Expanded(
                  child: Center(
                    child: Text(
                      module.getTitle(context),
                      textAlign: TextAlign.center,
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                      style: TextStyle(
                        fontWeight: FontWeight.w800,
                        fontSize: 13.5,
                        color: isDark ? Colors.white : theme.colorScheme.onSurface,
                      ),
                    ),
                  ),
                ),
              ],
            ),
          );
        },
      );
    }

    return Scaffold(
      backgroundColor: theme.colorScheme.surface,
      body: RefreshIndicator(
        onRefresh: _handleRefresh,
        child: content,
      ),
    );
  }
}
