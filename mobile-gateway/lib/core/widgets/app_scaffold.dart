import 'package:flutter/material.dart';
import '../../l10n/core_localization.dart';
import 'sidebar.dart';
import 'premium_header.dart';

class AppScaffold extends StatelessWidget {
  final Widget body;
  final Widget? title;
  final List<Widget>? actions;
  final Widget? floatingActionButton;
  final int? currentNavIndex;
  final Widget? leading;
  final bool? showBackButton;

  const AppScaffold({
    super.key,
    required this.body,
    this.title,
    this.actions,
    this.floatingActionButton,
    this.currentNavIndex,
    this.leading,
    this.showBackButton,
  });

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);

    final bool shouldShowBack = showBackButton ?? (currentNavIndex == 1 || Navigator.of(context).canPop());

    Widget? leadingWidget = leading;
    if (leadingWidget == null && shouldShowBack) {
      leadingWidget = IconButton(
        icon: const Icon(Icons.arrow_back_rounded),
        tooltip: 'Mbrapa',
        onPressed: () {
          if (Navigator.of(context).canPop()) {
            Navigator.of(context).pop();
          } else {
            Navigator.of(context).pushNamedAndRemoveUntil(
              '/dashboard',
              (route) => false,
              arguments: 1,
            );
          }
        },
      );
    }

    PreferredSizeWidget appBarWidget;
    if (title is Text) {
      appBarWidget = PremiumHeader(
        title: (title as Text).data ?? '',
        actions: actions,
      );
    } else {
      appBarWidget = AppBar(
        leading: leadingWidget,
        titleSpacing: (title is Column || leadingWidget != null) ? 0 : null,
        title: title,
        actions: actions,
      );
    }

    return Scaffold(
      backgroundColor: theme.colorScheme.surface,
      drawer: const Sidebar(),
      appBar: appBarWidget,
      body: body,
      floatingActionButton: floatingActionButton,
      bottomNavigationBar: currentNavIndex != null
          ? BottomNavigationBar(
              currentIndex: currentNavIndex ?? 0,
              onTap: (index) {
                Navigator.of(context).pushNamedAndRemoveUntil(
                  '/dashboard',
                  (route) => false,
                  arguments: index,
                );
              },
              backgroundColor: theme.colorScheme.surface,
              selectedItemColor: theme.colorScheme.primary,
              unselectedItemColor: Colors.grey,
              selectedLabelStyle: const TextStyle(fontWeight: FontWeight.bold, fontSize: 12),
              unselectedLabelStyle: const TextStyle(fontWeight: FontWeight.w500, fontSize: 12),
              type: BottomNavigationBarType.fixed,
              items: [
                BottomNavigationBarItem(
                  icon: const Icon(Icons.dashboard_customize_rounded),
                  label: coreTr(context, 'nav.dashboard'),
                ),
                BottomNavigationBarItem(
                  icon: const Icon(Icons.layers_outlined),
                  label: coreTr(context, 'nav.modules'),
                ),
                BottomNavigationBarItem(
                  icon: const Icon(Icons.settings_suggest_outlined),
                  label: coreTr(context, 'nav.settings'),
                ),
              ],
            )
          : null,
    );
  }
}
