import 'package:flutter/material.dart';
import '../../services/api_service.dart';
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
      body: Column(
        children: [
          // 1. Red Offline Banner
          ValueListenableBuilder<bool>(
            valueListenable: ApiService.isOffline,
            builder: (context, offline, child) {
              if (!offline) return const SizedBox.shrink();
              return Container(
                width: double.infinity,
                color: Colors.red.shade800,
                padding: const EdgeInsets.symmetric(vertical: 6, horizontal: 16),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    const Icon(Icons.wifi_off_rounded, color: Colors.white, size: 14),
                    const SizedBox(width: 8),
                    const Expanded(
                      child: Text(
                        'Nuk ka lidhje me serverin (Aplikacioni është Offline)',
                        style: TextStyle(color: Colors.white, fontSize: 11.5, fontWeight: FontWeight.bold),
                        textAlign: TextAlign.center,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ),
                    InkWell(
                      onTap: () {
                        ApiService.checkServerHealth();
                      },
                      child: const Icon(Icons.refresh_rounded, color: Colors.white, size: 16),
                    ),
                  ],
                ),
              );
            },
          ),

          // 2. Green Online Restored Banner (Shows for 4 seconds when connection returns)
          ValueListenableBuilder<bool>(
            valueListenable: ApiService.isOnlineRestored,
            builder: (context, onlineRestored, child) {
              if (!onlineRestored) return const SizedBox.shrink();
              return Container(
                width: double.infinity,
                color: Colors.green.shade800,
                padding: const EdgeInsets.symmetric(vertical: 6, horizontal: 16),
                child: const Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Icon(Icons.wifi_rounded, color: Colors.white, size: 14),
                    SizedBox(width: 8),
                    Expanded(
                      child: Text(
                        'Lidhja me serverin u rikthye (Online) 📶',
                        style: TextStyle(color: Colors.white, fontSize: 11.5, fontWeight: FontWeight.bold),
                        textAlign: TextAlign.center,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ),
                  ],
                ),
              );
            },
          ),

          Expanded(child: body),
        ],
      ),
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
